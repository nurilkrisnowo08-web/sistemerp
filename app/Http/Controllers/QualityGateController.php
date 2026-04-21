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
            // 1. Identifikasi asal data batch
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Produksi tidak ditemukan.");
                
                $origin = 'STAMPING'; 
                $batchNo = $batch->no_produksi; 
                $partNo = $batch->material_code; 
                $qty_awal = $batch->qty_hasil_ok;
                $table = 'produksi_batches';
            } else {
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                // PROTEKSI KEAMANAN: Validasi agar input tidak melebihi kiriman welding
                if ($request->qty_ok_final > $batch->qty_ok) {
                    throw new \Exception("Gagal! Input Qty OK (" . $request->qty_ok_final . ") melebihi jumlah yang dikirim dari area Welding (" . $batch->qty_ok . ").");
                }
                
                $origin = 'WELDING'; 
                $batchNo = $batch->no_produksi_stamping; 
                $partNo = $batch->part_no; 
                $qty_awal = $batch->qty_ok;
                $table = 'welding_batches';
            }

            // 2. Definisi Nama Inspektur dan Pembersihan Part No
            $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // 3. Validasi keberadaan Part di Tabel Master Finished Goods
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) {
                throw new \Exception("Gagal! Part No [$partNo] tidak terdaftar di Tabel Master Finished Goods.");
            }

            // 4. Simpan Laporan Inspeksi ke Tabel quality_inspections (History Pemeriksaan)
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

            // 5. Update Stok di Finished Goods (Update dua kolom: actual_stock & act_stock)
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $request->qty_ok_final,
                'act_stock'    => ($fg->act_stock ?? 0) + $request->qty_ok_final,
                'updated_at'   => now()
            ]);

            // 6. Pencatatan Mutasi ke Log Produksi (Tipe FG)
            DB::table('production_logs')->insert([
                'part_no'      => $partNo,
                'qty'          => $request->qty_ok_final,
                'process_type' => 'FG',
                'created_at'   => now()
            ]);

            // 7. Finalisasi Status Batch & Timpa Angka Operator dengan Angka Verifikasi QC
            // Ini memastikan History Welding sinkron dengan hasil pengecekan fisik QC
            DB::table($table)->where('id', $id)->update([
                'qty_ok'     => $request->qty_ok_final, // Timpa data ok
                'qty_ng'     => $request->qty_ng_final, // Timpa data ng
                'keterangan' => $request->ng_reason ?? '', // Timpa catatan operator dengan catatan QC
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus Verifikasi QC.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
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
            return back()->with('success', 'Batch antrean berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan penghapusan data: ' . $e->getMessage());
        }
    }
    /**
     * Menampilkan riwayat hasil inspeksi QC (Audit Trail)
     */
    public function history()
    {
        // Mengambil data inspeksi terbaru
        $historyData = DB::table('quality_inspections')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Quality.history', compact('historyData'));
    }
}