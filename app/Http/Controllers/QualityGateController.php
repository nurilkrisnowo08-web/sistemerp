<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {
   public function index() {
    $stampingQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
    $weldingQueue = DB::table('welding_batches')->where('status', 'WAITING_QC')->get();

    // Sesuaiin sama nama folder lu: Quality (Q gede) rill!
    return view('Quality.index', compact('stampingQueue', 'weldingQueue'));
}

   public function approve(Request $request, $type, $id) {
    DB::beginTransaction();
    try {
        // ... (logic ambil data batch tetep sama rill) ...

        // ✨ SEKARANG TEMBAK TABEL YANG BENER RILL!
        DB::table('quality_inspections')->insert([
            'batch_no'      => $batchNo,
            'origin'        => $origin,
            'part_no'       => $partNo,
            'qty_from_prod' => $qty_awal,
            'qty_ok'        => $request->qty_ok_final,
            'qty_ng'        => $request->qty_ng_final,
            'ng_reason'     => $request->ng_reason,
            'inspector'     => Auth::user()->name ?? 'QC_OFFICER',
            'status'        => 'APPROVED',
            'created_at'    => now(), 
            'updated_at'    => now()
        ]);

        // ... (logic update stok FG & status batch tetep sama rill) ...

        DB::commit();
        return back()->with('success', 'Barang Lulus QC rill!');
    } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); }
}
}