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

        $activeWelding = DB::table('welding_batches')
            ->leftJoin('finished_goods', function($join) {
                $join->on(DB::raw("REPLACE(welding_batches.part_no, ' ', '')"), '=', DB::raw("REPLACE(finished_goods.part_no, ' ', '')"));
            })
            ->select(
                'welding_batches.*', 
                'finished_goods.customer', 
                'finished_goods.part_name', 
                'welding_batches.status as batch_status'
            )
            ->whereIn('welding_batches.status', ['PENDING', 'PROSES'])
            ->get();

        $availableCustomers = $inventoryWelding->pluck('customer')->unique()->filter();

        return view('welding.welding_index', compact('date', 'activeWelding', 'availableCustomers', 'inventoryWelding'));
    }

    /**
     * 2. DEPLOY WELDING (Tombol TAKE)
     */
    public function deployWelding(Request $request)
    {
        $qty_ambil = (int)$request->qty_ambil;
        $part_no = $request->part_no;
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
     */
    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch tidak ditemukan.');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $keterangan = $request->keterangan; 

        DB::beginTransaction();
        try {
            // Update status menjadi COMPLETED
            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'      => $qty_ok, 
                'qty_ng'      => $qty_ng,
                'keterangan'  => $keterangan,
                'status'      => 'COMPLETED', 
                'qc_at'       => null, 
                'updated_at'  => now()
            ]);

            // ✨ PANGGIL ROBOT SINKRONISASI DISINI ✨
            // Agar data hasil las muncul di Dashboard Quality Hub secara otomatis
            $this->syncWeldingToActual($id);

            DB::commit();
            return back()->with('success', 'Proses Las Selesai. Data sudah masuk ke laporan Quality Hub.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage()); 
        }
    }

    /**
     * ✨ 4.1 FUNGSI BARU: ROBOT SINKRONISASI WELDING
     * Menjamin data las Bapak terbaca di Dashboard Actual & NG
     */
    private function syncWeldingToActual($weldingId)
    {
        $batch = DB::table('welding_batches')->where('id', $weldingId)->first();
        if (!$batch) return;

        $dateOnly = date('Y-m-d', strtotime($batch->created_at));

        // Kita cari atau buat baris di production_actuals
        $actual = DB::table('production_actuals')
            ->where('part_no', $batch->part_no)
            ->where('line_code', 'WELDING AREA') // Penanda bahwa ini hasil dari welding
            ->where('shift', 'N/A')
            ->whereDate('created_at', $dateOnly)
            ->first();

        if ($actual) {
            DB::table('production_actuals')->where('id', $actual->id)->update([
                'qty_ok' => $actual->qty_ok + $batch->qty_ok,
                'qty_ng' => $actual->qty_ng + $batch->qty_ng,
                'updated_at' => now()
            ]);
            $actualId = $actual->id;
        } else {
            $actualId = DB::table('production_actuals')->insertGetId([
                'part_no'    => $batch->part_no,
                'line_code'  => 'WELDING AREA',
                'shift'      => 'N/A',
                'qty_ok'     => $batch->qty_ok,
                'qty_ng'     => $batch->qty_ng,
                'created_at' => $batch->created_at,
                'updated_at' => now()
            ]);
        }

        // Catat rincian NG ke log jika ada
        if ($batch->qty_ng > 0) {
            DB::table('production_ng_logs')->insert([
                'actual_id'  => $actualId,
                'ng_type'    => 'NG Welding (Process)',
                'qty'        => $batch->qty_ng,
                'created_at' => $batch->created_at
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
            ->where('status', 'COMPLETED')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('welding.welding_history_weldig', compact('historyData'));
    }
}