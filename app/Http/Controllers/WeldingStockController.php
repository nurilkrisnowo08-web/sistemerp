<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeldingStockController extends Controller
{
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

        // ✨ FIX JOIN: Pake REPLACE biar part_no yang ada spasi tetep sinkron rill!
        $activeWelding = DB::table('welding_batches')
            ->leftJoin('finished_goods', function($join) {
                $join->on(DB::raw("REPLACE(welding_batches.part_no, ' ', '')"), '=', DB::raw("REPLACE(finished_goods.part_no, ' ', '')"));
            })
            ->select('welding_batches.*', 'finished_goods.customer', 'finished_goods.part_name', DB::raw("`status ENUM` as batch_status"))
            ->whereIn('status ENUM', ['PENDING', 'PROSES'])
            ->get();

        $availableCustomers = $inventoryWelding->pluck('customer')->unique();

        return view('welding.welding_index', compact('date', 'activeWelding', 'availableCustomers', 'inventoryWelding'));
    }

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
                throw new \Exception("Stok tidak cukup rill! Tersedia: " . ($fg->welding_stock ?? 0));
            }

            // Potong stok di finished_goods.welding_stock rill
            DB::table('finished_goods')
                ->where('id', $fg->id)
                ->decrement('welding_stock', $qty_ambil, ['updated_at' => now()]);

            DB::table('welding_batches')->insert([
                'no_produksi_stamping' => 'WLD-' . date('Ymd-His'), 
                'part_no'              => $part_no,
                'qty_masuk'            => $qty_ambil,
                'status ENUM'          => 'PENDING',
                'created_at'           => now(),
                'updated_at'           => now()
            ]);

            DB::commit();
            return back()->with('success', "Berhasil deploy $qty_ambil Pcs rill!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function startWelding($id)
    {
        DB::table('welding_batches')->where('id', $id)->update([
            'status ENUM' => 'PROSES',
            'updated_at' => now()
        ]);
        return back()->with('success', 'Proses Las Dimulai rill!');
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
            DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [$cleanPart])
                ->increment('actual_stock', $qty_ok, ['updated_at' => now()]);

            DB::table('production_logs')->insert([
                'part_no'      => $batch->part_no,
                'qty'          => $qty_ok,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok'      => $qty_ok, 
                'qty_ng'      => $qty_ng,
                'status ENUM' => 'COMPLETED', 
                'updated_at'  => now()
            ]);

            DB::commit();
            return back()->with('success', 'Selesai rill! Barang masuk FG.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }

    public function history(Request $request)
    {
        // ... (Fungsi history tetep rill, gak gue ubah)
    }

    /**
     * ✨ FIX CANCEL DEPLOY: Balikin ke tempat yang bener rill!
     */
    public function cancelDeploy($id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('welding_batches')->where('id', $id)->first();

            if (!$batch) {
                return back()->with('error', 'Data batch tidak ditemukan rill!');
            }

            // 1. ✨ SINKRONISASI: Balikin ke finished_goods.welding_stock (BUKAN welding_wip) rill!
            DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [str_replace(' ', '', trim($batch->part_no))])
                ->increment('welding_stock', $batch->qty_masuk);

            // 2. Hapus data antrean
            DB::table('welding_batches')->where('id', $id)->delete();

            // 3. Catat Log
            DB::table('production_logs')->insert([
                'part_no' => $batch->part_no,
                'qty' => $batch->qty_masuk,
                'process_type' => 'CANCEL_DEPLOY',
                'operator' => auth()->user()->name ?? 'System',
                'created_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Batch berhasil dibatalkan dan stok balik ke Rak rill!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal batal: ' . $e->getMessage());
        }
    }
}