<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    /**
     * Menampilkan antrean inspeksi (Disesuaikan agar tidak double)
     */
    public function index() {
        // ✨ FIX DOUBLE: Kelompokkan berdasarkan no_produksi
        $produksiQueue = DB::table('produksi_batches')
            ->where('status', 'WAITING_QC')
            ->select(
                'no_produksi',
                'material_code',
                'keterangan',
                'created_at',
                DB::raw('SUM(qty_hasil_ok) as qty_hasil_ok'), // Total OK dari semua line
                DB::raw('SUM(qty_hasil_ng) as qty_hasil_ng'), // Info NG Produksi (disimpan di background)
                DB::raw('MIN(id) as id') // Ambil satu ID sebagai referensi route
            )
            ->groupBy('no_produksi', 'material_code', 'keterangan', 'created_at')
            ->get();
        
        $weldingQueue = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->whereNull('qc_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue'));
    }

    /**
     * Memproses verifikasi QC (Logika Akumulasi NG)
     */
    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            if ($type == 'stamping') {
                $batchReference = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$batchReference) throw new \Exception("Batch tidak ditemukan.");

                $batchNo = $batchReference->no_produksi;
                $partNo  = $batchReference->material_code;
                $table   = 'produksi_batches';

                // Hitung total NG awal dari produksi (semua line di batch ini)
                $prod_ng_total = DB::table('produksi_batches')->where('no_produksi', $batchNo)->sum('qty_hasil_ng');
                $qty_awal = DB::table('produksi_batches')->where('no_produksi', $batchNo)->sum('qty_hasil_ok');

                // Logika: OK Final adalah inputan QC, NG Final adalah (NG Produksi + Temuan Baru QC)
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$prod_ng_total + (int)$request->qty_ng_final;

                $colOk = 'qty_hasil_ok';
                $colNg = 'qty_hasil_ng';
                
                // Update Clause: Update semua baris yang memiliki nomor batch sama
                $updateWhere = ['no_produksi' => $batchNo];
                $origin = 'STAMPING';
            } else {
                // Logika Welding (Single Line)
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                $batchNo = $batch->no_produksi_stamping;
                $partNo  = $batch->part_no;
                $qty_awal = $batch->qty_ok;

                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$batch->qty_ng + (int)$request->qty_ng_final;

                $table = 'welding_batches';
                $colOk = 'qty_ok';
                $colNg = 'qty_ng';
                $updateWhere = ['id' => $id];
                $origin = 'WELDING';
            }

            $inspectorName = $request->inspector_name ?? (Auth::user()?->name ?? 'QC_OFFICER');
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));

            // Validasi Master Finished Goods
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) throw new \Exception("Gagal! Part No [$partNo] tidak terdaftar di Finished Goods.");

            // 1. Simpan History Inspeksi
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $final_ok,
                'qty_ng'        => (int)$request->qty_ng_final, // Hanya NG yang ditemukan QC
                'ng_reason'     => $request->ng_reason ?? '',
                'inspector'     => $inspectorName,
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // 2. Update Stok FG
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $final_ok,
                'act_stock'    => ($fg->act_stock ?? 0) + $final_ok,
                'updated_at'   => now()
            ]);

            // 3. Log Mutasi
            DB::table('production_logs')->insert([
                'part_no' => $partNo, 'qty' => $final_ok, 'process_type' => 'FG', 'created_at' => now()
            ]);

            // 4. Finalisasi Tabel Produksi/Welding
            // ✨ FIX: Update menggunakan No Produksi agar semua record line ikut selesai
            DB::table($table)->where($updateWhere)->update([
                $colOk       => $final_ok, 
                $colNg       => $final_ng, 
                'keterangan' => $request->ng_reason ?? '',
                'status'     => 'COMPLETED',
                'qc_at'      => now(),
                'qc_by'      => $inspectorName,
                'updated_at' => now()
            ]);

            // 5. Robot Dashboard
            $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus Verifikasi QC.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin)
    {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        $today = date('Y-m-d');

        DB::table('production_actuals')->updateOrInsert(
            ['part_no' => $partNo, 'line_code' => $lineCode, 'created_at' => $today],
            [
                'qty_ok' => DB::raw("qty_ok + 0"), 
                'qty_ng' => DB::raw("qty_ng + 0"), 
                'shift' => 'N/A',
                'updated_at' => now()
            ]
        );
    }

    public function destroy($type, $id)
    {
        try {
            if ($type == 'stamping') {
                // Hapus satu batch penuh agar tidak sisa di line lain
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                DB::table('produksi_batches')->where('no_produksi', $batch->no_produksi)->delete();
            } else {
                DB::table('welding_batches')->where('id', $id)->delete();
            }
            return back()->with('success', 'Batch antrean berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $historyData = DB::table('quality_inspections')->orderBy('created_at', 'desc')->get();
        return view('Quality.history', compact('historyData'));
    }
}