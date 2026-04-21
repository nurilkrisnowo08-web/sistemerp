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
        // ... (logika ambil data batch tetep sama) ...

        // ✨ FIX: Ambil nama dari input Form, kalau kosong baru pake Auth User rill!
        $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
        
        $cleanPart = str_replace([' ', '-'], '', trim($partNo));

        // 2. Cari Part di Finished Goods
        $fg = DB::table('finished_goods')
            ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
            ->first();

        if (!$fg) throw new \Exception("Part No [$partNo] gak ada di Master FG rill!");

        // 3. Simpan Laporan QC
        DB::table('quality_inspections')->insert([
            'batch_no'      => $batchNo,
            'origin'        => $origin,
            'part_no'       => $partNo,
            'qty_from_prod' => $qty_awal,
            'qty_ok'        => $request->qty_ok_final,
            'qty_ng'        => $request->qty_ng_final,
            'ng_reason'     => $request->ng_reason ?? '',
            'inspector'     => $inspectorName, // Nama inspector masuk sini rill!
            'status'        => 'APPROVED',
            'created_at'    => now(), 'updated_at' => now()
        ]);

        // ... (logika update stok & update batch tetep sama) ...

        DB::commit();
        return back()->with('success', "Part $partNo berhasil di-verify oleh $inspectorName rill!");

    } catch (\Exception $e) { 
        DB::rollBack(); 
        return back()->with('error', $e->getMessage()); 
    }
}
}