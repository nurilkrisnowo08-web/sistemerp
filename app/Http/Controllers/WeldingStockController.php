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
     * 4. FINISH: Status berubah ke COMPLETED untuk History, namun tetap verifikasi di QC
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
            // Update status menjadi COMPLETED (Agar muncul di History)
            // qc_at dibiarkan NULL sebagai penanda bahwa QC belum melakukan verifikasi akhir (update stok)
            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'      => $qty_ok, 
                'qty_ng'      => $qty_ng,
                'keterangan'  => $keterangan,
                'status'      => 'COMPLETED', 
                'qc_at'       => null, 
                'updated_at'  => now()
            ]);

            DB::commit();
            return back()->with('success', 'Proses Las Selesai (Status: COMPLETED). Menunggu verifikasi di Quality Gate untuk sinkronisasi stok FG.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage()); 
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

    // ✨ GANTI NAMA VARIABEL MENJADI $historyData AGAR SINKRON DENGAN VIEW
    $historyData = $query->get()->map(function($item) use ($startDate, $endDate) {
        $cleanPart = str_replace([' ', '-'], '', trim($item->part_no));

        // 1. Total Masuk ke area Welding (dari Stamping) selama periode terpilih
        $in_period = DB::table('production_logs')
            ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
            ->where('process_type', 'WELDING')
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->sum('qty');

        // 2. Total Keluar dari area Welding (selesai las) selama periode terpilih
        $out_period = DB::table('welding_batches')
            ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
            ->where('status', 'COMPLETED')
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->sum('qty_ok');

        // 3. Menghitung mutasi masa depan (setelah endDate) untuk tarik mundur saldo
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

        // ✨ LOGIKA KALKULASI SALDO
        $item->total_in = $in_period;
        $item->total_out = $out_period;
        // Saldo Akhir periode = Saldo Live saat ini - (Masuk setelah periode) + (Keluar setelah periode)
        $item->stock_akhir = ($item->welding_stock ?? 0) - $future_in + $future_out;
        // Saldo Awal periode = Saldo Akhir - Masuk periode + Keluar periode
        $item->stock_awal = $item->stock_akhir - $in_period + $out_period;

        return $item;
    })->filter(function($i) {
        // Hanya tampilkan part yang ada aktivitasnya atau masih ada stoknya
        return ($i->stock_awal > 0 || $i->total_in > 0 || $i->total_out > 0 || $i->stock_akhir > 0);
    });

    // ✨ PASTIKAN DI COMPACT MENGGUNAKAN 'historyData'
    return view('welding.welding_history', compact('historyData', 'clients', 'customerFilter', 'startDate', 'endDate'));
}
    /**
     * 6. RIWAYAT PRODUKSI WELDING (Level Audit)
     */
    public function historyWelding()
    {
        // Menampilkan semua yang statusnya COMPLETED (baik yang sudah verifikasi QC maupun belum)
        $historyData = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('welding.welding_history_weldig', compact('historyData'));
    }
}