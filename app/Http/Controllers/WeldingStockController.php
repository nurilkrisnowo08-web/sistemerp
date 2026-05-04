<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
    /**
     * 1. TERMINAL HUB LIVE
     * Logika: Menghitung Ledger Stok (Opening Fixed) & Menampilkan Kartu Aktif
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        $inventoryWelding = DB::table('finished_goods')
            ->select('part_no', 'part_name', 'customer', 'welding_stock as live_stock')
            ->where(function($q) {
                $q->where('welding_stock', '>', 0)
                  ->orWhereExists(function ($query) {
                      $query->select(DB::raw(1))
                            ->from('parts')
                            ->whereRaw("REPLACE(parts.part_no, ' ', '') = REPLACE(finished_goods.part_no, ' ', '')")
                            ->where('next_process', 'WELDING');
                  });
            })
            ->get()
            ->map(function($item) use ($date) {
                $cleanPart = str_replace([' ', '-'], '', trim($item->part_no));

                // IN: Dari Stamping (Cek Log Produksi)
                $in_stamping = DB::table('production_logs')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('process_type', 'WELDING')
                    ->whereDate('created_at', $date)
                    ->sum('qty') ?? 0;

                // IN: Dari Return QC & Return Sisa Material (Cek Riwayat Inspeksi)
                $in_return = DB::table('quality_inspections')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('origin', 'WELDING')
                    ->whereDate('created_at', $date)
                    ->sum('qty_ret') ?? 0;

                // OUT: Dari Deploy (Cek Welding Batches)
                $out_welding = DB::table('welding_batches')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->whereDate('created_at', $date)
                    ->sum('qty_masuk') ?? 0;

                // ✨ RUMUS OPENING FIXED RILL (ANTI-LONCAT)
                // Opening = Stok Sekarang - (Semua yang Masuk Hari Ini) + (Semua yang Keluar Hari Ini)
                $item->init = $item->live_stock - ($in_stamping + $in_return) + $out_welding;
                
                $item->in_s = $in_stamping;
                $item->in_r = $in_return; 
                $item->out = $out_welding;
                $item->run = ($item->live_stock > 0) ? round($item->live_stock / 50, 1) : 0;

                return $item;
            });

        $weldingLines = DB::table('line_welding')->get();
        $listNG = DB::table('master_ngs')->whereIn('category', ['WELDING', 'GENERAL'])->orderBy('ng_name', 'asc')->get();

        // 🚀 ACTIVE PROCESS & REWORK CARDS
        $activeWelding = DB::table('welding_batches')
            ->leftJoin('finished_goods', function($join) {
                $join->on(DB::raw("REPLACE(welding_batches.part_no, ' ', '')"), '=', DB::raw("REPLACE(finished_goods.part_no, ' ', '')"));
            })
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->select(
                'welding_batches.*', 
                'finished_goods.customer', 
                'finished_goods.part_name', 
                'line_welding.nama_line as nama_mesin',
                'line_welding.kode_line'
            )
            ->where(function($q) {
                $q->whereIn('welding_batches.status', ['PENDING', 'PROSES'])
                  ->orWhere('welding_batches.qty_return', '>', 0); 
            })
            ->get();

        $availableCustomers = $inventoryWelding->pluck('customer')->unique()->filter();

        return view('welding.welding_index', compact('date', 'activeWelding', 'availableCustomers', 'inventoryWelding', 'weldingLines', 'listNG'));
    }

    /**
     * 2. DEPLOY WELDING (Pengambilan barang dari rak ke mesin)
     */
    public function deployWelding(Request $request)
    {
        $qty_ambil = (int)$request->qty_ambil;
        $part_no = $request->part_no;
        $line_id = $request->line_id;
        $cleanPart = str_replace(' ', '', trim($part_no));

        DB::beginTransaction();
        try {
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->first();

            if (!$fg || $fg->welding_stock < $qty_ambil) {
                throw new \Exception("Stok tidak mencukupi rill! Tersedia: " . ($fg->welding_stock ?? 0));
            }

            DB::table('finished_goods')
                ->where('id', $fg->id)
                ->decrement('welding_stock', $qty_ambil, ['updated_at' => now()]);

            DB::table('welding_batches')->insert([
                'no_produksi_stamping' => 'WLD-' . date('Ymd-His'), 
                'part_no'              => $part_no,
                'line_id'              => $line_id,
                'qty_masuk'            => $qty_ambil,
                'status'               => 'PENDING',
                'created_at'           => now(),
                'updated_at'           => now()
            ]);

            DB::commit();
            return back()->with('success', "Berhasil deploy $qty_ambil Pcs ke area Welding.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 3. START OPERATION
     */
    public function startWelding($id)
    {
        DB::table('welding_batches')->where('id', $id)->update([
            'status' => 'PROSES',
            'updated_at' => now()
        ]);
        return back()->with('success', 'Proses pengelasan telah dimulai.');
    }

    /**
     * 4. FINISH WELDING
     * Logika: Akumulasi OK/NG, Reset Rework, dan Sync ke QC
     */
    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch tidak ditemukan.');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $qty_sisa_ke_rak = (int)$request->qty_return; // Input dari box biru "Return_To_WIP_Rack" rill

        $target = ($batch->qty_return > 0) ? $batch->qty_return : $batch->qty_masuk;

        if (($qty_ok + $qty_ng + $qty_sisa_ke_rak) != $target) {
            return back()->with('error', "Total input ($qty_ok + $qty_ng + $qty_sisa_ke_rak) tidak sesuai target ($target)!");
        }

        DB::beginTransaction();
        try {
            // AKUMULASI RILL (Pake +)
            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'     => $batch->qty_ok + $qty_ok, 
                'qty_ng'     => $batch->qty_ng + $qty_ng,
                'qty_return' => 0, 
                'status'     => 'COMPLETED', 
                'keterangan' => $request->keterangan,
                'updated_at' => now()
            ]);

            if ($qty_sisa_ke_rak > 0) {
                // 1. Tambah stok fisik di rak WIP
                DB::table('finished_goods')
                    ->where('part_no', $batch->part_no)
                    ->increment('welding_stock', $qty_sisa_ke_rak);

                // 2. ✨ WAJIB: Tulis Log ke quality_inspections agar Dashboard terbaca rill!
                DB::table('quality_inspections')->insert([
                    'batch_no'      => $batch->no_produksi_stamping,
                    'origin'        => 'WELDING',
                    'part_no'       => $batch->part_no,
                    'qty_from_prod' => $target,
                    'qty_ok'        => 0,
                    'qty_ng'        => 0,
                    'qty_ret'       => $qty_sisa_ke_rak, // Ini kunci agar masuk kolom biru (IN RET QC)
                    'inspector'     => 'OPERATOR_FINISH',
                    'status'        => 'RETURN_TO_RACK',
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
            }

            $actualId = $this->syncToQualityGate($id, $qty_ok, $qty_ng);

            if ($request->has('ng_detail_type') && $actualId) {
                foreach ($request->ng_detail_type as $key => $type) {
                    $qDetail = (int)$request->ng_detail_qty[$key];
                    if ($qDetail > 0) {
                        DB::table('welding_ng_logs')->insert([
                            'actual_id'   => $actualId,
                            'no_produksi' => $batch->no_produksi_stamping,
                            'ng_type'     => $type,
                            'qty'         => $qDetail,
                            'created_at'  => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Batch Berhasil Diselesaikan.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal rill: ' . $e->getMessage()); 
        }
    }

    /**
     * 4.1 SINKRONISASI KE QC DASHBOARD
     */
    private function syncToQualityGate($weldingId, $qtyOk, $qtyNg)
    {
        $batch = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.id', $weldingId)
            ->select('welding_batches.*', 'line_welding.kode_line')
            ->first();

        if (!$batch) return null;

        $dateOnly = date('Y-m-d');
        $lineName = $batch->kode_line ?? 'WELDING AREA';

        $actual = DB::table('welding_actuals')
            ->whereRaw("REPLACE(part_no, ' ', '') = REPLACE(?, ' ', '')", [$batch->part_no])
            ->where('line_code', $lineName)
            ->whereDate('created_at', $dateOnly)
            ->first();

        if ($actual) {
            DB::table('welding_actuals')->where('id', $actual->id)->update([
                'qty_ok' => $actual->qty_ok + $qtyOk,
                'qty_ng' => $actual->qty_ng + $qtyNg,
                'updated_at' => now()
            ]);
            return $actual->id;
        } else {
            return DB::table('welding_actuals')->insertGetId([
                'part_no'    => $batch->part_no,
                'line_code'  => $lineName,
                'shift'      => 'N/A', 
                'qty_ok'     => $qtyOk,
                'qty_ng'     => $qtyNg,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * 5. HISTORY MUTASI STOK (Terminal Monitoring)
     */
    public function history(Request $request)
    {
        $customerFilter = $request->customer;
        $startDate = $request->start_date ?? date('Y-m-d');
        $endDate = $request->end_date ?? date('Y-m-d');
        $clients = DB::table('customers')->get();

        $query = DB::table('finished_goods')
            ->select('part_no', 'part_name', 'customer', 'welding_stock');

        if ($customerFilter && $customerFilter != 'ALL') {
            $query->where('customer', trim($customerFilter));
        }

        $historyData = $query->get()->map(function($item) use ($startDate, $endDate) {
            $cleanPart = str_replace([' ', '-'], '', trim($item->part_no));

            $in_stamp = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty') ?? 0;

            $in_ret = DB::table('quality_inspections')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('origin', 'WELDING')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty_ret') ?? 0;

            $out_period = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty_masuk') ?? 0;

            $item->in_s = $in_stamp;
            $item->in_r = $in_ret;
            $item->total_in = $in_stamp + $in_ret;
            $item->total_out = $out_period;
            
            $future_in = DB::table('production_logs')->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')->whereDate('created_at', '>', $endDate)->sum('qty') ?? 0;
            $future_out = DB::table('welding_batches')->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->whereDate('created_at', '>', $endDate)->sum('qty_masuk') ?? 0;

            $item->stock_akhir = ($item->welding_stock ?? 0) + $future_out - $future_in;
            $item->stock_awal = $item->stock_akhir - $item->total_in + $item->total_out;

            return $item;
        })->filter(function($i) {
            return ($i->stock_awal != 0 || $i->total_in > 0 || $i->total_out > 0 || $i->stock_akhir != 0);
        });

        return view('welding.welding_history', compact('historyData', 'clients', 'customerFilter', 'startDate', 'endDate'));
    }

    /**
     * 6. HISTORY BATCH WELDING (Rincian OK/NG per Batch)
     */
    public function historyWelding(Request $request)
    {
        $startDate = $request->start_date ?? date('Y-m-d');
        $endDate = $request->end_date ?? date('Y-m-d');

        $historyData = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('status', 'COMPLETED')
            ->whereBetween(DB::raw('DATE(welding_batches.updated_at)'), [$startDate, $endDate])
            ->select('welding_batches.*', 'line_welding.kode_line', 'line_welding.nama_line')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($h) {
                $h->ng_details = DB::table('welding_ng_logs')
                                    ->where('no_produksi', $h->no_produksi_stamping)
                                    ->get();
                return $h;
            });

        return view('welding.welding_history_weldig', compact('historyData', 'startDate', 'endDate'));
    }
}