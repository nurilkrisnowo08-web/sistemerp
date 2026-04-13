<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
    /**
     * 1. TERMINAL HUB LIVE
     * Menampilkan antrean aktif dan daftar stok yang tersedia.
     */
   public function index(Request $request)
{
    // Gunakan tanggal hari ini (Y-m-d)
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
            // Kita bersihkan part_no dari spasi dan karakter aneh buat perbandingan rill
            $cleanPart = str_replace([' ', '-'], '', trim($item->part_no));

            // 1. ✨ HITUNG IN (Dari Stamping)
            // Gue pake LIKE biar lebih aman nemu datanya
            $item->in_s = DB::table('production_logs')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->whereDate('created_at', $date)
                ->sum('qty') ?? 0;

            // 2. ✨ HITUNG OUT (Dari Tombol TAKE/Deploy)
            $item->out = DB::table('welding_batches')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->whereDate('created_at', $date)
                ->sum('qty_masuk') ?? 0;

            // 3. Saldo Awal (Init) = Stok Sekarang - Masuk + Keluar
            $item->init = $item->live_stock - $item->in_s + $item->out;
            
            // 4. Hitung Sisa Pallet (Run)
            $item->run = ($item->live_stock > 0) ? round($item->live_stock / 50, 1) : 0;

            return $item;
        });

    $activeWelding = DB::table('welding_batches')
        ->join('finished_goods', 'welding_batches.part_no', '=', 'finished_goods.part_no')
        ->select('welding_batches.*', 'finished_goods.customer', 'finished_goods.part_name', DB::raw("`status ENUM` as batch_status"))
        ->whereIn('status ENUM', ['PENDING', 'PROSES'])
        ->get();

    $availableCustomers = $inventoryWelding->pluck('customer')->unique();

    return view('welding.welding_index', compact('date', 'activeWelding', 'availableCustomers', 'inventoryWelding'));
}

    /**
     * ✨ FIX DEPLOY: Tombol TAKE / PENGAMBILAN
     * Ini yang bakal nyatet angka di kolom "OUT" rill!
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
                throw new \Exception("Stok di Gudang Welding tidak cukup! Tersedia: " . ($fg->welding_stock ?? 0));
            }

            // 1. Potong saldo utama di gudang WIP
            DB::table('finished_goods')
                ->where('id', $fg->id)
                ->decrement('welding_stock', $qty_ambil, ['updated_at' => now()]);

            // 2. Buat batch baru (Ini yang dibaca sebagai "OUT" hari ini di Dashboard)
            DB::table('welding_batches')->insert([
                'no_produksi_stamping' => 'WLD-' . date('Ymd-His'), 
                'part_no'              => $part_no,
                'qty_masuk'            => $qty_ambil,
                'status ENUM'          => 'PENDING',
                'created_at'           => now(),
                'updated_at'           => now()
            ]);

            DB::commit();
            return back()->with('success', "Berhasil deploy $qty_ambil Pcs ke proses Las rill!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Gagal Deploy: " . $e->getMessage());
        }
    }

    public function startWelding($id)
    {
        $target = DB::table('welding_batches')->where('id', $id)->first();
        if ($target) {
            DB::table('welding_batches')->where('id', $id)->update([
                'status ENUM' => 'PROSES',
                'updated_at' => now()
            ]);
            return back()->with('success', 'Proses Las Dimulai!');
        }
        return back()->with('error', 'Batch tidak ditemukan!');
    }

    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch hilang!');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $cleanPart = str_replace(' ', '', trim($batch->part_no));

        DB::beginTransaction();
        try {
            // 1. Pindahkan hasil OK ke Actual Stock (Finished Goods)
            $affected = DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->increment('actual_stock', $qty_ok, ['updated_at' => now()]);
            
            if ($affected === 0) {
                throw new \Exception("Part No [ $cleanPart ] tidak terdaftar di database Finished Goods!");
            }

            // 2. Log Produksi untuk laporan harian
            DB::table('production_logs')->insert([
                'part_no'    => $batch->part_no,
                'qty'        => $qty_ok,
                'created_at' => now()
            ]);

            // 3. Selesaikan status Batch
            DB::table('welding_batches')
                ->where('id', $id)
                ->update([
                    'qty_ok'      => $qty_ok,
                    'qty_ng'      => $qty_ng,
                    'status ENUM' => 'COMPLETED',
                    'updated_at'  => now()
                ]);

            DB::commit();
            return back()->with('success', 'Selesai rill! Barang resmi masuk ke Finished Goods.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal: ' . $e->getMessage()); 
        }
    }

    public function history(Request $request)
{
    $customerFilter = $request->customer;
    $startDate = $request->start_date ?? date('Y-m-d');
    $endDate = $request->end_date ?? date('Y-m-d');

    $query = DB::table('welding_batches')
        ->join('finished_goods', 'welding_batches.part_no', '=', 'finished_goods.part_no')
        ->select(
            'welding_batches.*', 
            'finished_goods.customer', 
            'finished_goods.part_name'
        )
        ->where('welding_batches.status ENUM', 'COMPLETED');

    // Filter per Customer
    if ($customerFilter && $customerFilter != 'ALL') {
        $query->where('finished_goods.customer', trim($customerFilter));
    }

    // Filter per Tanggal
    if ($request->start_date) {
        $query->whereDate('welding_batches.updated_at', '>=', $startDate);
    }
    if ($request->end_date) {
        $query->whereDate('welding_batches.updated_at', '<=', $endDate);
    }

    $history = $query->orderBy('welding_batches.updated_at', 'desc')->get();
    $clients = DB::table('customers')->get();

    return view('welding.welding_history', compact('history', 'clients', 'customerFilter', 'startDate', 'endDate'));
}
}