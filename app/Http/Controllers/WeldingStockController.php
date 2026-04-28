<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
    /**
     * 1. TERMINAL HUB LIVE
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

                $in_stamping = DB::table('production_logs')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('process_type', 'WELDING')
                    ->whereDate('created_at', $date)
                    ->sum('qty') ?? 0;

                $out_welding = DB::table('welding_batches')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->whereDate('created_at', $date)
                    ->sum('qty_masuk') ?? 0;

                $item->init = $item->live_stock - $in_stamping + $out_welding;
                $item->in_s = $in_stamping;
                $item->out = $out_welding;
                $item->run = ($item->live_stock > 0) ? round($item->live_stock / 50, 1) : 0;

                return $item;
            });

        $weldingLines = DB::table('line_welding')->get();
        $listNG = DB::table('master_ngs')->whereIn('category', ['WELDING', 'GENERAL'])->orderBy('ng_name', 'asc')->get();

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
                'line_welding.kode_line',
                'welding_batches.status as batch_status'
            )
            ->whereIn('welding_batches.status', ['PENDING', 'PROSES'])
            ->get();

        $availableCustomers = $inventoryWelding->pluck('customer')->unique()->filter();

        return view('welding.welding_index', compact('date', 'activeWelding', 'availableCustomers', 'inventoryWelding', 'weldingLines', 'listNG'));
    }

    /**
     * 2. DEPLOY WELDING (TomBOL TAKE)
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
                throw new \Exception("Stok tidak mencukupi! Tersedia: " . ($fg->welding_stock ?? 0));
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
     * 4. FINISH WELDING (DENGAN KEAMANAN DATA)
     */
    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch tidak ditemukan.');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $total_input = $qty_ok + $qty_ng;

        if ($total_input != $batch->qty_masuk) {
            return back()->with('error', "🚨 GAGAL: Total input ($total_input) tidak sama dengan Target ($batch->qty_masuk)!");
        }

        DB::beginTransaction();
        try {
            // 1. Simpan hasil akhir batch
            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok' => $qty_ok, 
                'qty_ng' => $qty_ng,
                'status' => 'COMPLETED', 
                'keterangan' => $request->keterangan,
                'qc_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Simpan Rincian Penyakit NG (Tetap dicatat di Log internal Welding)
            if ($request->has('ng_detail_type')) {
                foreach ($request->ng_detail_type as $key => $type) {
                    $qDetail = (int)$request->ng_detail_qty[$key];
                    if ($qDetail > 0) {
                        DB::table('production_ng_logs')->insert([
                            'no_produksi' => $batch->no_produksi_stamping,
                            'ng_type'     => $type,
                            'qty'         => $qDetail,
                            'created_at'  => now()
                        ]);
                    }
                }
            }

            // 3. ✨ OTOMATIS KIRIM KE QUALITY GATE (Hanya OK Goods)
            $this->syncWeldingToActual($id);

            DB::commit();
            return back()->with('success', 'Batch Selesai. Data OK dikirim ke Quality Gate.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal Simpan: ' . $e->getMessage()); 
        }
    }

    /**
     * ✨ 4.1 ROBOT SINKRONISASI KE QUALITY GATE (FILTER OK ONLY)
     */
    private function syncWeldingToActual($weldingId)
    {
        $batch = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.id', $weldingId)
            ->select('welding_batches.*', 'line_welding.kode_line')
            ->first();

        if (!$batch) return;

        $dateOnly = date('Y-m-d', strtotime($batch->created_at));
        
        // Agar muncul di kolom kanan Quality Gate, pastikan line_code mengandung kata 'WELDING'
        $lineName = $batch->kode_line ?? 'WELDING AREA';

        // 🎯 TARGET: production_actuals (Tabel yang dibaca oleh layar Quality Gate)
        $actual = DB::table('production_actuals')
            ->where('part_no', $batch->part_no)
            ->where('line_code', $lineName)
            ->whereDate('created_at', $dateOnly)
            ->first();

        if ($actual) {
            DB::table('production_actuals')->where('id', $actual->id)->update([
                'qty_ok' => $actual->qty_ok + $batch->qty_ok, // Tambah Total OK saja
                // 'qty_ng' TIDAK DITAMBAH sesuai permintaan Bapak (NG tidak ikut)
                'updated_at' => now()
            ]);
        } else {
            DB::table('production_actuals')->insert([
                'part_no'    => $batch->part_no,
                'line_code'  => $lineName,
                'shift'      => 'N/A',
                'qty_ok'     => $batch->qty_ok, // Hanya Total OK saja
                'qty_ng'     => 0,              // NG diset 0 agar QC cek dari awal
                'created_at' => $batch->created_at,
                'updated_at' => now()
            ]);
        }
    }

    /**
     * 5. HISTORY MUTASI STOK
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

            $in_period = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty');

            $out_period = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('status', 'COMPLETED')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty_ok');

            $future_in = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')
                ->whereDate('created_at', '>', $endDate)
                ->sum('qty');

            $future_out = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('status', 'COMPLETED')
                ->whereDate('created_at', '>', $endDate)
                ->sum('qty_ok');

            $item->total_in = $in_period;
            $item->total_out = $out_period;
            $item->stock_akhir = ($item->welding_stock ?? 0) - $future_in + $future_out;
            $item->stock_awal = $item->stock_akhir - $in_period + $out_period;

            return $item;
        })->filter(function($i) {
            return ($i->stock_awal > 0 || $i->total_in > 0 || $i->total_out > 0 || $i->stock_akhir > 0);
        });

        return view('welding.welding_history', compact('historyData', 'clients', 'customerFilter', 'startDate', 'endDate'));
    }

    /**
     * 6. RIWAYAT PRODUKSI WELDING
     */
    public function historyWelding()
    {
        $historyData = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('status', 'COMPLETED')
            ->select('welding_batches.*', 'line_welding.kode_line', 'line_welding.nama_line')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('welding.welding_history_weldig', compact('historyData'));
    }
}