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
            // 1. Ambil data batch sesuai asalnya rill!
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Produksi tidak ditemukan rill!");
                
                $origin = 'STAMPING'; 
                $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; 
                $qty_awal = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan rill!");
                
                $origin = 'WELDING'; 
                $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; 
                $qty_awal = $batch->qty_ok;
                $table = 'welding_batches';
            }

            // 2. Definisi Inspector & Pembersihan Part No rill
            $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // 3. Cari Part di Finished Goods rill
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) {
                throw new \Exception("Gagal rill! Part No [$partNo] tidak terdaftar di Tabel finished_goods.");
            }

            // 4. Simpan Laporan QC ke quality_inspections rill
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $request->qty_ok_final,
                'qty_ng'        => $request->qty_ng_final,
                'ng_reason'     => $request->ng_reason ?? '',
                'inspector'     => $inspectorName,
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // 5. Update Stok FG (DUA KOLOM SEKALIGUS RILL!)
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $request->qty_ok_final,
                'act_stock'    => ($fg->act_stock ?? 0) + $request->qty_ok_final,
                'updated_at'   => now()
            ]);

            // 6. Catat Log Produksi rill
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 7. Selesaikan Batch rill
            DB::table($table)->where('id', $id)->update([
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus QC rill!");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }
    // Tambahin ini di QualityGateController lu rill!
public function destroy($type, $id)
{
    try {
        if ($type == 'stamping') {
            DB::table('produksi_batches')->where('id', $id)->delete();
        } else {
            DB::table('welding_batches')->where('id', $id)->delete();
        }
        return back()->with('success', 'Batch antrean berhasil dihapus total rill!');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal hapus rill: ' . $e->getMessage());
    }
}
}