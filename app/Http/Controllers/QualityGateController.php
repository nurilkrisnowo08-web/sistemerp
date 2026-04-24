<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    /**
     * Menampilkan antrean inspeksi untuk Stamping dan Welding
     */
    public function index() {
        // Antrean Stamping (Status: WAITING_QC)
        $produksiQueue = DB::table('produksi_batches')->where('status', 'WAITING_QC')->get();
        
        // Antrean Welding (Status: COMPLETED tapi belum diverifikasi QC/qc_at masih NULL)
        $weldingQueue = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->whereNull('qc_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

    /**
     * Memproses verifikasi QC, update stok FG, dan finalisasi batch
     */
    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // 1. Identifikasi & Mapping Kolom (Mencegah Error SQL Unknown Column)
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Produksi tidak ditemukan.");
                
                $origin = 'STAMPING'; 
                $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; 
                $qty_awal = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
                
                // ✨ Mapping Kolom Tabel Stamping (produksi_batches)
                $colOk = 'qty_hasil_ok';
                $colNg = 'qty_hasil_ng';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                // Validasi agar QC tidak input melebihi kiriman operator
                if ($request->qty_ok_final > $batch->qty_ok) {
                    throw new \Exception("Gagal! Input Qty OK melebihi kiriman welding (" . $batch->qty_ok . ").");
                }
                
                $origin = 'WELDING'; 
                $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; 
                $qty_awal = $batch->qty_ok;
                $table = 'welding_batches';

                // ✨ Mapping Kolom Tabel Welding (welding_batches)
                $colOk = 'qty_ok';
                $colNg = 'qty_ng';
            }

            // 2. Definisi Nama Inspektur dan Pembersihan Part No
            $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // 3. Validasi Master Finished Goods
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) {
                throw new \Exception("Gagal! Part No [$partNo] tidak terdaftar di Finished Goods.");
            }

            // 4. Simpan History Inspeksi QC
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

            // 5. Update Stok di Finished Goods (actual_stock & act_stock)
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $request->qty_ok_final,
                'act_stock'    => ($fg->act_stock ?? 0) + $request->qty_ok_final,
                'updated_at'   => now()
            ]);

            // 6. Log Mutasi FG (Gudang Jadi)
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 7. Finalisasi Status Batch & Update Data (MENGGUNAKAN MAPPING KOLOM)
            // Ini akan mengupdate produksi_batches atau welding_batches sesuai mapping di atas
            DB::table($table)->where('id', $id)->update([
                $colOk       => $request->qty_ok_final, 
                $colNg       => $request->qty_ng_final, 
                'keterangan' => $request->ng_reason ?? '',
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            // ✨ 8. UPDATE ROBOT DASHBOARD (Sinkronisasi ke Quality Hub)
            // Hanya update jika ada selisih atau penyesuaian dari QC
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
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        $today = date('Y-m-d');

        // Note: Gunakan updateOrInsert agar dashboard selalu akurat
        DB::table('production_actuals')->updateOrInsert(
            [
                'part_no'    => $partNo,
                'line_code'  => $lineCode,
                'created_at' => $today
            ],
            [
                'qty_ok'     => DB::raw("qty_ok + 0"), // Kita asumsikan dashboard sudah diupdate saat produksi
                'qty_ng'     => DB::raw("qty_ng + 0"), 
                'shift'      => 'N/A',
                'updated_at' => now()
            ]
        );
    }

    /**
     * Menghapus batch antrean jika terdapat kesalahan input data
     */
    public function destroy($type, $id)
    {
        try {
            if ($type == 'stamping') {
                DB::table('produksi_batches')->where('id', $id)->delete();
            } else {
                DB::table('welding_batches')->where('id', $id)->delete();
            }
            return back()->with('success', 'Batch antrean berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat hasil inspeksi QC
     */
    public function history()
    {
        $historyData = DB::table('quality_inspections')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Quality.history', compact('historyData'));
    }
}