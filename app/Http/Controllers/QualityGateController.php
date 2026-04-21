<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // Ambil antrean yang statusnya WAITING_QC rill
        $stampingQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        $weldingQueue = DB::table('welding_batches')->where('status', 'WAITING_QC')->get();

        return view('Quality.index', compact('stampingQueue', 'weldingQueue'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // 1. Identifikasi asal barang rill
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                $origin = 'STAMPING'; 
                $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; 
                $qty_from_prod = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                $origin = 'WELDING'; 
                $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; 
                $qty_from_prod = $batch->qty_ok;
                $table = 'welding_batches';
            }

            $inspectorName = Auth::user()->name ?? 'QC_OFFICER';

            // 2. Simpan Laporan ke quality_inspections (Sesuai image_82dda1.png rill!)
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

            // 3. Update Stok FG (Finished Goods) rill!
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));
            DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->increment('actual_stock', $request->qty_ok_final, ['updated_at' => now()]);

            // 4. Catat ke production_logs sebagai barang FG rill
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 5. Update tabel asal (Selesaikan status & isi jejak QC rill!)
            DB::table($table)->where('id', $id)->update([
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Barang berhasil lulus QC dan masuk Gudang FG rill!');

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', 'Gagal rill! Pesan: ' . $e->getMessage()); 
        }
    }
}