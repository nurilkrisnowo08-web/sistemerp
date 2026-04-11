<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
    /**
     * 1. TERMINAL HUB LIVE
     * Menampilkan antrean aktif dan daftar stok yang tersedia untuk diambil.
     */
    public function index(Request $request)
{
    $date = $request->date ?? date('Y-m-d');

    // ✨ LOGIKA LEDGER WELDING (Planning View)
    $inventoryWelding = DB::table('finished_goods')
        ->select(
            'part_no',
            'part_name',
            'customer',
            'welding_stock as live_stock' // Ini saldo saat ini di HeidiSQL
        )
        // Kita cuma ambil part yang punya rute Welding atau yang ada stoknya
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
            // 1. Hitung Masuk dari Stamping (IN-S) hari ini
            // (Asumsi: ProduksiController nyatet ke production_logs saat finish stamping)
            $in_stamping = DB::table('production_logs')
                ->where('part_no', $item->part_no)
                ->whereDate('created_at', $date)
                ->sum('qty');

            // 2. Hitung Keluar ke Proses Las (OUT) hari ini
            // (Barang yang dideploy/diambil dari gudang welding ke meja las)
            $out_welding = DB::table('welding_batches')
                ->where('part_no', $item->part_no)
                ->whereDate('created_at', $date)
                ->sum('qty_masuk');

            // 3. Hitung Saldo Awal (INIT)
            // Rumus: Live Stock (sekarang) - Masuk + Keluar
            $item->init = $item->live_stock - $in_stamping + $out_welding;
            $item->in_s = $in_stamping;
            $item->out = $out_welding;
            
            // 4. Hitung Sisa Jalan (RUN) 
            // Misal standar per box/pallet adalah 50 pcs
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
     * ✨ FUNGSI BARU: AMBIL BARANG DARI GUDANG WELDING (DEPLOY)
     * Mengurangi welding_stock dan membuat baris baru di welding_batches.
     */
    public function deployWelding(Request $request)
    {
        $qty_ambil = (int)$request->qty_ambil;
        $part_no = $request->part_no;
        $cleanPart = str_replace(' ', '', trim($part_no));

        DB::beginTransaction();
        try {
            // 1. Cek stok di gudang WIP (welding_stock)
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') COLLATE utf8mb4_unicode_ci = ?", [$cleanPart])
                ->first();

            if (!$fg || $fg->welding_stock < $qty_ambil) {
                throw new \Exception("Stok di Gudang Welding tidak cukup! Tersedia: " . ($fg->welding_stock ?? 0));
            }

            // 2. POTONG STOK WELDING (Karena barang dibawa ke meja kerja)
            DB::table('finished_goods')
                ->where('id', $fg->id)
                ->decrement('welding_stock', $qty_ambil, ['updated_at' => now()]);

            // 3. BIKIN ANTREAN KERJA (BATCH BARU)
            DB::table('welding_batches')->insert([
                'no_produksi_stamping' => 'WLD-' . date('Ymd-His'), 
                'part_no'              => $part_no,
                'qty_masuk'            => $qty_ambil,
                'status ENUM'          => 'PENDING',
                'created_at'           => now(),
                'updated_at'           => now()
            ]);

            DB::commit();
            return back()->with('success', "Berhasil deploy $qty_ambil Pcs dari stok Welding.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Gagal Deploy: " . $e->getMessage());
        }
    }

    /**
     * 2. START OPERATION
     */
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

    /**
     * 3. FINISH & TRANSFER TO ACTUAL STOCK (FG)
     */
    public function finishWelding(Request $request, $id)
    {
        $batch = DB::table('welding_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Data batch hilang!');

        $qty_ok = (int)$request->qty_ok;
        $qty_ng = (int)$request->qty_ng;
        $cleanPart = str_replace(' ', '', trim($batch->part_no));

        DB::beginTransaction();
        try {
            // 1. TAMBAH KE STOK ACTUAL (FG)
            $affected = DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') COLLATE utf8mb4_unicode_ci = ?", [$cleanPart])
                ->increment('actual_stock', $qty_ok, ['updated_at' => now()]);
            
            if ($affected === 0) {
                throw new \Exception("Part No [ $cleanPart ] tidak terdaftar di database Finished Goods!");
            }

            // 2. CATAT LOG PRODUKSI (Kolektif)
            // Note: Hilangkan qty_ng jika kolom tidak ada di tabel production_logs Anda
            DB::table('production_logs')->insert([
                'part_no'    => $batch->part_no,
                'qty'        => $qty_ok,
                'created_at' => now()
            ]);

            // 3. TUTUP BATCH WELDING
            DB::table('welding_batches')
                ->where('id', $id)
                ->update([
                    'qty_ok'      => $qty_ok,
                    'qty_ng'      => $qty_ng,
                    'status ENUM' => 'COMPLETED',
                    'updated_at'  => now()
                ]);

            DB::commit();
            return back()->with('success', 'Selesai! Barang resmi masuk ke Finished Goods.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal: ' . $e->getMessage()); 
        }
    }
}