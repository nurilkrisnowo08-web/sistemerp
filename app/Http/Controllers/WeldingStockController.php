<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
    /**
     * 1. TERMINAL HUB LIVE (Fixed: Reading Return from Batches Table)
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

                // 1. IN (Stamping): Hasil produksi Stamping (Masuk ke Rak WIP)
                $in_stamping = DB::table('production_logs')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('process_type', 'WELDING')
                    ->whereDate('created_at', $date)
                    ->sum('qty') ?? 0;

                // 2. ✨ IN (Return): Diambil LANGSUNG dari tabel welding_batches (Bukan production_logs)
                $in_return = DB::table('welding_batches')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('status', 'COMPLETED')
                    ->whereDate('updated_at', $date) // Pakai updated_at karena return diproses saat FINISH
                    ->sum('qty_return') ?? 0;

                // 3. OUT (Welding): Berapa banyak yang dideploy/diambil ke mesin
                $out_welding = DB::table('welding_batches')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->whereDate('created_at', $date)
                    ->sum('qty_masuk') ?? 0;

                // RUMUS AKURAT: Live - (Total Masuk) + Total Keluar = Opening
                $item->init = $item->live_stock - ($in_stamping + $in_return) + $out_welding;
                $item->in_s = $in_stamping;
                $item->in_r = $in_return; // ✨ Sekarang muncul angkanya di kolom IN (RETURN)
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
     * 2. DEPLOY WELDING (Tetap Sama)
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
     * 3. START OPERATION (Tetap Sama)
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
     * 4. FINISH WELDING (Fixed: Accurate Return Handling)
     */
    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch tidak ditemukan.');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $qty_ret = (int)$request->qty_return; 

        // Validasi: Qty Ambil = OK + NG + RETURN
        if (($qty_ok + $qty_ng + $qty_ret) != $batch->qty_masuk) {
            return back()->with('error', "Total input tidak sesuai dengan target deployment!");
        }

        DB::beginTransaction();
        try {
            // 1. Simpan ke tabel batch (Update hasil OK, NG, dan RETURN)
            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'     => $qty_ok, 
                'qty_ng'     => $qty_ng,
                'qty_return' => $qty_ret, 
                'status'     => 'COMPLETED', 
                'keterangan' => $request->keterangan,
                'updated_at' => now()
            ]);

            // 2. ✨ KEMBALIKAN STOK: Tambahkan kembali sisa material ke rak WIP
            if ($qty_ret > 0) {
                DB::table('finished_goods')
                    ->where('part_no', $batch->part_no) // Gunakan part_no rill dari batch
                    ->increment('welding_stock', $qty_ret);
            }

            // 3. Sinkron ke Dashboard Intelligence
            $actualId = $this->syncToQualityGate($id);

            // 4. Simpan Log NG
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
            return back()->with('success', 'Batch Selesai! Sisa material dikembalikan ke rak WIP.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal: ' . $e->getMessage()); 
        }
    }

    /**
     * 4.1 ROBOT SINKRONISASI (Tetap Sama)
     */
    private function syncToQualityGate($weldingId)
    {
        $batch = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.id', $weldingId)
            ->select('welding_batches.*', 'line_welding.kode_line')
            ->first();

        if (!$batch) return null;

        $dateOnly = date('Y-m-d', strtotime($batch->updated_at));
        $lineName = $batch->kode_line ?? 'WELDING AREA';

        $actual = DB::table('welding_actuals')
            ->whereRaw("REPLACE(part_no, ' ', '') = REPLACE(?, ' ', '')", [$batch->part_no])
            ->where('line_code', $lineName)
            ->whereDate('created_at', $dateOnly)
            ->first();

        if ($actual) {
            DB::table('welding_actuals')->where('id', $actual->id)->update([
                'qty_ok' => $actual->qty_ok + $batch->qty_ok,
                'qty_ng' => $actual->qty_ng + $batch->qty_ng,
                'updated_at' => now()
            ]);
            return $actual->id;
        } else {
            return DB::table('welding_actuals')->insertGetId([
                'part_no'    => $batch->part_no,
                'line_code'  => $lineName,
                'shift'      => 'N/A', 
                'qty_ok'     => $batch->qty_ok,
                'qty_ng'     => $batch->qty_ng,
                'created_at' => $batch->updated_at,
                'updated_at' => now()
            ]);
        }
    }

    /**
     * 5. HISTORY MUTASI STOK (Fixed: Logic Return Accuracy)
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

            // Total In (Stamp + Return)
            $in_stamp = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty') ?? 0;

            $in_ret = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('status', 'COMPLETED')
                ->whereBetween(DB::raw('DATE(updated_at)'), [$startDate, $endDate])
                ->sum('qty_return') ?? 0;

            // Total Keluar (Berdasarkan deployed qty)
            $out_period = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty_masuk') ?? 0;

            // Backtracking Masa Depan
            $future_in_stamp = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('process_type', 'WELDING')
                ->whereDate('created_at', '>', $endDate)
                ->sum('qty') ?? 0;

            $future_in_ret = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->where('status', 'COMPLETED')
                ->whereDate('updated_at', '>', $endDate)
                ->sum('qty_return') ?? 0;

            $future_out = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->whereDate('created_at', '>', $endDate)
                ->sum('qty_masuk') ?? 0;

            $item->total_in = $in_stamp + $in_ret;
            $item->total_out = $out_period;
            $item->stock_akhir = ($item->welding_stock ?? 0) + $future_out - ($future_in_stamp + $future_in_ret);
            $item->stock_awal = $item->stock_akhir - ($in_stamp + $in_ret) + $out_period;

            return $item;
        })->filter(function($i) {
            return ($i->stock_awal != 0 || $i->total_in > 0 || $i->total_out > 0 || $i->stock_akhir != 0);
        });

        return view('welding.welding_history', compact('historyData', 'clients', 'customerFilter', 'startDate', 'endDate'));
    }

    /**
     * 6. RIWAYAT PRODUKSI WELDING (Tetap Sama)
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