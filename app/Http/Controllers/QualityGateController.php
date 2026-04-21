<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // 1. Ambil antrean Produksi (Stamping) - Variabel disesuaikan buat UI rill!
        $produksiQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        
        // 2. Ambil antrean Welding
        $weldingQueue = DB::table('welding_batches')->where('status', 'WAITING_QC')->get();

        // Pastiin variabel produksiQueue dikirim ke view Quality.index rill
        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

   public function approve(Request $request, $type, $id) {
    DB::beginTransaction();
    try {
        if ($type == 'stamping') {
            $batch = DB::table('produksi_batches')->where('id', $id)->first();
            $origin = 'STAMPING'; $batchNo = $batch->no_produksi; $partNo = $batch->material_code; $qty_from_prod = $batch->qty_hasil_ok;
            $table = 'produksi_batches';
        } else {
            $batch = DB::table('welding_batches')->where('id', $id)->first();
            $origin = 'WELDING'; $batchNo = $batch->no_produksi_stamping; $partNo = $batch->part_no; $qty_from_prod = $batch->qty_ok;
            $table = 'welding_batches';
        }

        $inspectorName = Auth::user()->name ?? 'QC_OFFICER';
        $cleanPart = str_replace([' ', '-'], '', trim($partNo));

        // ✨ STEP 1: CEK DULU ADA GAK PART-NYA DI GUDANG rill!
        $fg_exist = DB::table('finished_goods')
            ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
            ->first();

        if (!$fg_exist) {
            // Kalau gak ketemu, sistem bakal stop dan kasih tau lu rill!
            throw new \Exception("Gagal rill! Part No [$partNo] tidak terdaftar di Tabel finished_goods. Daftarin dulu di Master Part!");
        }

        // 1. Simpan Laporan QC rill
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
            'created_at'    => now(), 'updated_at' => now()
        ]);

        // 2. Update Stok FG rill
        DB::table('finished_goods')
            ->where('id', $fg_exist->id) // Pake ID biar pasti masuk rill!
            ->increment('actual_stock', $request->qty_ok_final, ['updated_at' => now()]);

        // 3. Catat Log rill
        DB::table('production_logs')->insert([
            'part_no' => $partNo, 'qty' => $request->qty_ok_final, 'process_type' => 'FG', 'created_at' => now()
        ]);

        // 4. Selesaikan Batch rill
        DB::table($table)->where('id', $id)->update([
            'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspectorName, 'updated_at' => now()
        ]);

        DB::commit();
        return back()->with('success', 'Suksess rill! Barang masuk gudang.');

    } catch (\Exception $e) { 
        DB::rollBack(); 
        return back()->with('error', $e->getMessage()); 
    }
}
}