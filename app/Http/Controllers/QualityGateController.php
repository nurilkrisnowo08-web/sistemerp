<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {
    public function index() {
        // Ambil data yang nunggu QC dari Stamping rill
        $stampingQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        // Ambil data yang nunggu QC dari Welding rill
        $weldingQueue = DB::table('welding_batches')->where('status ENUM', 'WAITING_QC')->get();

        return view('Quality.index', compact('stampingQueue', 'weldingQueue'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                $origin = 'STAMPING'; $batchNo = $batch->no_produksi; $partNo = $batch->material_code; $qty = $batch->qty_hasil_ok;
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                $origin = 'WELDING'; $batchNo = $batch->no_produksi_stamping; $partNo = $batch->part_no; $qty = $batch->qty_ok;
            }

            // 1. Simpan ke tabel quality_inspections yang barusan lu bikin rill!
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty,
                'qty_ok'        => $request->qty_ok_final,
                'qty_ng'        => $request->qty_ng_final,
                'ng_reason'     => $request->ng_reason,
                'inspector'     => Auth::user()->name ?? 'QC_OFFICER',
                'status'        => 'APPROVED',
                'created_at'    => now(), 'updated_at' => now()
            ]);

            // 2. ✨ UPDATE STOK FG: Di sini stok resmi nambah rill!
            DB::table('finished_goods')
                ->whereRaw("REPLACE(part_no, ' ', '') = ?", [str_replace([' ', '-'], '', trim($partNo))])
                ->increment('actual_stock', $request->qty_ok_final);

            // 3. Selesaikan status di tabel asal
            $table = ($type == 'stamping') ? 'produksi_batches' : 'welding_batches';
            $statusCol = ($type == 'stamping') ? 'status' : 'status ENUM';
            DB::table($table)->where('id', $id)->update([$statusCol => 'COMPLETED', 'updated_at' => now()]);

            DB::commit();
            return back()->with('success', 'Barang Lulus QC & Masuk Finished Goods rill!');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }
}