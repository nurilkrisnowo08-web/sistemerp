<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        $produksiQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        $weldingQueue = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->whereNull('qc_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // 1. Identifikasi & Mapping Kolom (Agar tidak Unknown Column)
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Produksi tidak ditemukan.");
                
                $origin = 'STAMPING'; 
                $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; 
                $qty_awal = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
                
                // ✨ Mapping Kolom Stamping
                $colOk = 'qty_hasil_ok';
                $colNg = 'qty_hasil_ng';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                if ($request->qty_ok_final > $batch->qty_ok) {
                    throw new \Exception("Gagal! Input melebihi kiriman welding (" . $batch->qty_ok . ").");
                }
                
                $origin = 'WELDING'; 
                $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; 
                $qty_awal = $batch->qty_ok;
                $table = 'welding_batches';

                // ✨ Mapping Kolom Welding
                $colOk = 'qty_ok';
                $colNg = 'qty_ng';
            }

            $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) {
                throw new \Exception("Gagal! Part No [$partNo] tidak terdaftar di Finished Goods.");
            }

            // 4. Simpan History Inspeksi
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

            // 5. Update Stok FG (actual_stock & act_stock)
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $request->qty_ok_final,
                'act_stock'    => ($fg->act_stock ?? 0) + $request->qty_ok_final,
                'updated_at'   => now()
            ]);

            // 6. Log Mutasi FG
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 7. Finalisasi Status Batch (MENGGUNAKAN MAPPING KOLOM)
            DB::table($table)->where('id', $id)->update([
                $colOk       => $request->qty_ok_final, 
                $colNg       => $request->qty_ng_final, 
                'keterangan' => $request->ng_reason ?? '',
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            // ✨ 8. UPDATE ROBOT DASHBOARD (Agar Quality Hub ikut Terupdate)
            $this->updateDashboardActual($partNo, $request->qty_ok_final, $request->qty_ng_final, $origin);

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus Verifikasi QC.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    /**
     * ✨ FUNGSI SINKRONISASI DASHBOARD QUALITY HUB
     */
    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin)
    {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA'; // Sesuaikan mapping line Bapak
        $today = date('Y-m-d');

        // Cari atau Update di production_actuals
        DB::table('production_actuals')->updateOrInsert(
            [
                'part_no'    => $partNo,
                'line_code'  => $lineCode,
                'created_at' => $today
            ],
            [
                'qty_ok'     => DB::raw("qty_ok + $qtyOk"),
                'qty_ng'     => DB::raw("qty_ng + $qtyNg"),
                'shift'      => 'N/A', // Shift bisa disesuaikan jika perlu
                'updated_at' => now()
            ]
        );
    }

    public function destroy($type, $id) { /* Tetap sama seperti punya Bapak */ }
    public function history() { /* Tetap sama seperti punya Bapak */ }
}