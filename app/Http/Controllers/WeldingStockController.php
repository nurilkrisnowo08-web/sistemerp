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

                // 1. ✨ FIX IN: Ambil data murni kiriman Stamping ke Welding rill!
                // Tanpa filter 'WELDING', semua produksi (termasuk yang langsung ke FG) bakal masuk ke sini.
                $in_stamping = DB::table('production_logs')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->where('process_type', 'WELDING') // <--- INI BIAR GAK BOCOR RILL!
                    ->whereDate('created_at', $date)
                    ->sum('qty') ?? 0;

                // 2. HITUNG OUT: Pengambilan ke meja las hari ini
                $out_welding = DB::table('welding_batches')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->whereDate('created_at', $date)
                    ->sum('qty_masuk') ?? 0;

                // 3. Hitung Saldo Awal (INIT)
                $item->init = $item->live_stock - $in_stamping + $out_welding;
                $item->in_s = $in_stamping;
                $item->out = $out_welding;
                
                // 4. Hitung Sisa Jalan (RUN)
                $item->run = ($item->live_stock > 0) ? round($item->live_stock / 50, 1) : 0;

                return $item;
            });

        // ✨ FIX: Sesuaikan status ENUM pakai backtick karena ada spasi di DB lu rill
        $activeWelding = DB::table('welding_batches')
            ->join('finished_goods', 'welding_batches.part_no', '=', 'finished_goods.part_no')
            ->select('welding_batches.*', 'finished_goods.customer', 'finished_goods.part_name', DB::raw("`status ENUM` as batch_status"))
            ->whereIn('status ENUM', ['PENDING', 'PROSES'])
            ->get();

        $availableCustomers = $inventoryWelding->pluck('customer')->unique();

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
                throw new \Exception("Stok di Gudang Welding tidak cukup! Tersedia: " . ($fg->welding_stock ?? 0));
            }

            DB::table('finished_goods')
                ->where('id', $fg->id)
                ->decrement('welding_stock', $qty_ambil, ['updated_at' => now()]);

            DB::table('welding_batches')->insert([
                'no_produksi_stamping' => 'WLD-' . date('Ymd-His'), 
                'part_no'              => $part_no,
                'qty_masuk'            => $qty_ambil,
                'status ENUM'          => 'PENDING', // Sesuai DB lu rill
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

    /**
     * 3. START OPERATION
     */
    public function startWelding($id)
    {
        $target = DB::table('welding_batches')->where('id', $id)->first();
        if ($target) {
            DB::table('welding_batches')->where('id', $id)->update([
                'status ENUM' => 'PROSES',
                'updated_at' => now()
            ]);
            return back()->with('success', 'Proses Las Dimulai rill!');
        }
        return back()->with('error', 'Batch tidak ditemukan!');
    }

    /**
     * 4. FINISH & TRANSFER (KE FG)
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
            $affected = DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->increment('actual_stock', $qty_ok, ['updated_at' => now()]);
            
            if ($affected === 0) { throw new \Exception("Part No tidak terdaftar di database FG!"); }

            // ✨ FIX LOG: Kasih label 'FG' biar log ini GAK DIHITUNG sebagai "IN" di dashboard Welding rill!
            DB::table('production_logs')->insert([
                'part_no'      => $batch->part_no,
                'qty'          => $qty_ok,
                'process_type' => 'FG', // Menandakan barang masuk ke Finished Goods
                'created_at'   => now()
            ]);

            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'      => $qty_ok, 
                'qty_ng'      => $qty_ng,
                'status ENUM' => 'COMPLETED', 
                'updated_at'  => now()
            ]);

            DB::commit();
            return back()->with('success', 'Selesai rill! Barang resmi masuk ke Finished Goods.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }
}