<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // Ambil antrean sesuai variabel UI lu rill!
        $produksiQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        $weldingQueue = DB::table('welding_batches')->where('status', 'WAITING_QC')->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // 1. Ambil data batch asal rill
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                $origin = 'STAMPING'; $batchNo = $batch->no_produksi; $partNo = $batch->material_code; 
                $qty_from_prod = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                $origin = 'WELDING'; $batchNo = $batch->no_produksi_stamping; $partNo = $batch->part_no; 
                $qty_from_prod = $batch->qty_ok;
                $table = 'welding_batches';
            }

            if (!$batch) throw new \Exception("Batch tidak ditemukan rill!");

            $inspectorName = Auth::user()->name ?? 'QC_OFFICER';
            // Bersihkan part no dari spasi dan strip rill
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // 2. Cari Part di Finished Goods (Tembak pake ID biar akurat rill!)
            $fg_exist = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg_exist) {
                throw new \Exception("Gagal rill! Part No [$partNo] nggak ada di tabel finished_goods. Daftarin dulu di Master Part!");
            }

            // 3. Simpan Laporan QC ke quality_inspections rill
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_from_prod,
                'qty_ok'        => $request->qty_ok_final,
                'qty_ng'        => $request->qty_ng_final,
                'ng_reason'     => $request->ng_reason,
                'inspector'     => $inspectorName,
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // 4. ✨ UPDATE STOK FG (Gue tembak dua kolom biar aman rill!)
            // Kita update 'actual_stock' DAN 'act_stock' sesuai gambar image_8fa2ec.png
            DB::table('finished_goods')
                ->where('id', $fg_exist->id)
                ->update([
                    'actual_stock' => $fg_exist->actual_stock + $request->qty_ok_final,
                    'act_stock'    => ($fg_exist->act_stock ?? 0) + $request->qty_ok_final, // Kolom No 14 rill!
                    'updated_at'   => now()
                ]);

            // 5. Catat Log Produksi rill
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 6. Selesaikan Batch di tabel asal rill
            DB::table($table)->where('id', $id)->update([
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Suksess rill! Stok FG sudah diupdate.');

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }
}