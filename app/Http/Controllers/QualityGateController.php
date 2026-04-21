<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        $produksiQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        $weldingQueue = DB::table('welding_batches')->where('status', 'WAITING_QC')->get();
        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // 1. Ambil data batch rill
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                $origin = 'STAMPING'; $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; $qty_awal = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                $origin = 'WELDING'; $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; $qty_awal = $batch->qty_ok;
                $table = 'welding_batches';
            }

            if (!$batch) throw new \Exception("Batch ID $id tidak ditemukan rill!");

            // ✨ FIX: Pake ?-> biar gak crash kalau belum login rill
            $inspectorName = Auth::user()?->name ?? 'QC_OFFICER';
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // 2. Cari Part di Finished Goods (Wajib ada rill!)
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) {
                throw new \Exception("Gagal rill! Part [$partNo] tidak ada di Master Finished Goods. Daftarin dulu barunya bisa nambah stok!");
            }

            // 3. Simpan Laporan QC
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $request->qty_ok_final,
                'qty_ng'        => $request->qty_ng_final,
                'ng_reason'     => $request->ng_reason,
                'inspector'     => $inspectorName,
                'status'        => 'APPROVED',
                'created_at'    => now(), 'updated_at' => now()
            ]);

            // 4. Update Stok FG (Tembak semua kolom biar sinkron rill!)
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $request->qty_ok_final,
                'act_stock'    => ($fg->act_stock ?? 0) + $request->qty_ok_final, // Kolom 14 rill
                'updated_at'   => now()
            ]);

            // 5. Catat Log Produksi
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 6. Selesaikan Batch (Biar hilang dari antrean rill!)
            DB::table($table)->where('id', $id)->update([
                'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspectorName, 'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', "Part $partNo sukses masuk gudang rill!");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }
}