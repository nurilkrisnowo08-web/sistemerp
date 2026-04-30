<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // ✨ ANTI-DOUBLE: Menggabungkan batch yang terpecah di banyak line menjadi satu tampilan
        $produksiQueue = DB::table('produksi_batches')
            ->where('status', 'WAITING_QC')
            ->select(
                'no_produksi',
                'material_code',
                'keterangan',
                'created_at',
                DB::raw('SUM(qty_hasil_ok) as qty_hasil_ok'), 
                DB::raw('MIN(id) as id') 
            )
            ->groupBy('no_produksi', 'material_code', 'keterangan', 'created_at')
            ->get();
        
        $weldingQueue = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->whereNull('qc_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // ✨ KAMAR NG: Memisahkan alasan NG agar tidak bercampur
        $ngStamping = DB::table('master_ngs')
            ->whereIn('category', ['STAMPING', 'GENERAL'])
            ->get();

        $ngWelding = DB::table('master_ngs')
            ->whereIn('category', ['WELDING', 'GENERAL'])
            ->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue', 'ngStamping', 'ngWelding'));
    }

   public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            if ($type == 'stamping') {
                $ref = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch tidak ditemukan.");

                $batchNo = $ref->no_produksi;
                $partNo  = $ref->material_code;

                $lines = DB::table('produksi_batches')->where('no_produksi', $batchNo)->get();
                $total_ok_prod = $lines->sum('qty_hasil_ok');
                $qc_verified_ok = (int)$request->qty_ok_final;
                $qc_verified_ng = (int)$request->qty_ng_final; // Ambil input NG dari QC rill

                // 1. Logika penyesuaian selisih NG di tabel Produksi
                if ($qc_verified_ok < $total_ok_prod) {
                    $selisih = $total_ok_prod - $qc_verified_ok;
                    $firstLine = $lines->first();
                    
                    DB::table('produksi_batches')->where('id', $firstLine->id)->update([
                        'qty_hasil_ok' => ($firstLine->qty_hasil_ok - $selisih),
                        'qty_hasil_ng' => ($firstLine->qty_hasil_ng + $selisih),
                    ]);
                }

                DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                    'status'     => 'COMPLETED',
                    'qc_at'      => now(),
                    'qc_by'      => $request->inspector_name ?? Auth::user()?->name,
                    'updated_at' => now()
                ]);
                
                $final_ok = $qc_verified_ok;
                $final_ng = $qc_verified_ng;
                $origin   = 'STAMPING';
                $qty_awal = $total_ok_prod;

            } else {
                // Logika Welding
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                $batchNo = $batch->no_produksi_stamping;
                $partNo  = $batch->part_no;
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$request->qty_ng_final;

                DB::table('welding_batches')->where('id', $id)->update([
                    'qty_ok'     => $final_ok,
                    'qty_ng'     => $batch->qty_ng + $final_ng,
                    'status'     => 'COMPLETED',
                    'qc_at'      => now(),
                    'qc_by'      => $request->inspector_name ?? Auth::user()?->name,
                    'updated_at' => now()
                ]);
                
                $qty_awal = $batch->qty_ok;
                $origin   = 'WELDING';
            }

            // Validasi Stok FG
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if (!$fg) throw new \Exception("Part No [$partNo] tidak terdaftar di Finished Goods.");

            // ✨ 2. SIMPAN INSPEKSI (Persis Gambar image_2824e5.png)
            $ng_reason = ($final_ng > 0) ? ($request->ng_reason ?? 'OTHER_DEFECT') : 'OK_GOODS';
            
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $final_ok,
                'qty_ng'        => $final_ng, 
                'ng_reason'     => $ng_reason,
                'inspector'     => $request->inspector_name ?? Auth::user()?->name ?? 'QC_OFFICER',
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // ✨ 3. SINKRONISASI KE PRODUCTION_NG_LOGS (Agar Breakdown Merah di History Muncul rill!)
            if ($final_ng > 0) {
                // Pastikan ada row di production_actuals dulu
                $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);
                $actualId = DB::table('production_actuals')
                            ->where('part_no', $partNo)
                            ->whereDate('created_at', date('Y-m-d'))
                            ->value('id');

                if ($actualId) {
                    DB::table('production_ng_logs')->insert([
                        'actual_id'   => $actualId,
                        'no_produksi' => $batchNo,
                        'ng_type'     => "QC_FINDING: " . $ng_reason, // Tandai kalau ini temuan QC
                        'qty'         => $final_ng,
                        'created_at'  => now()
                    ]);
                }
            }

            // Update Stok FG
            DB::table('finished_goods')->where('id', $fg->id)->update([
                'actual_stock' => $fg->actual_stock + $final_ok,
                'act_stock'    => ($fg->act_stock ?? 0) + $final_ok,
                'updated_at'   => now()
            ]);

            // Log Mutasi
            DB::table('production_logs')->insert([
                'part_no' => $partNo, 'qty' => $final_ok, 'process_type' => 'FG', 'created_at' => now()
            ]);

            // Jalankan update Dashboard (Sudah dipanggil di atas juga tapi aman rill)
            $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus Verifikasi QC.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin) {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        DB::table('production_actuals')->updateOrInsert(
            ['part_no' => $partNo, 'line_code' => $lineCode, 'created_at' => date('Y-m-d')],
            ['updated_at' => now()]
        );
    }

    public function destroy($type, $id) {
        try {
            if ($type == 'stamping') {
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

    public function history() {
        $historyData = DB::table('quality_inspections')->orderBy('created_at', 'desc')->get();
        return view('Quality.history', compact('historyData'));
    }
}