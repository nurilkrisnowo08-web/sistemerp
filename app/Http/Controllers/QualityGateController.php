<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        $produksiQueue = DB::table('produksi_batches')
            ->where('status', 'WAITING_QC')
            ->select(
                'no_produksi', 'material_code', 'keterangan',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('SUM(qty_hasil_ok) as qty_hasil_ok'), 
                DB::raw('SUM(total_checked) as total_checked_so_far'), // Ambil total yang sudah dicek sebelumnya
                DB::raw('MIN(id) as id') 
            )
            ->groupBy('no_produksi', 'material_code', 'keterangan')
            ->get();
        
        $weldingQueue = DB::table('welding_batches')->where('status', 'COMPLETED')->whereNull('qc_at')->get();
        $ngStamping = DB::table('master_ngs')->whereIn('category', ['STAMPING', 'GENERAL'])->get();
        $ngWelding = DB::table('master_ngs')->whereIn('category', ['WELDING', 'GENERAL'])->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue', 'ngStamping', 'ngWelding'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            $inspector = $request->inspector_name ?? Auth::user()->name ?? 'QC_OFFICER';
            $now = now(); 
            $qcThreshold = $now->copy()->subSecond();

            // Ambil Input dari UI
            $final_ok  = (int)$request->qty_ok_final;
            $final_ng  = (int)$request->qty_ng_final;
            $final_ret = (int)($request->qty_return_final ?? 0); // Tambahan kolom return
            $checked_now = $final_ok + $final_ng + $final_ret;

            if ($type == 'stamping') {
                $ref = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch tidak ditemukan.");

                $batchNo = $ref->no_produksi;
                $partNo  = $ref->material_code;
                $origin  = 'STAMPING';

                // Hitung total target dari semua line di batch ini
                $total_target = DB::table('produksi_batches')->where('no_produksi', $batchNo)->sum('qty_hasil_ok');
                $already_checked = DB::table('produksi_batches')->where('no_produksi', $batchNo)->sum('total_checked');
                
                $total_after_this = $already_checked + $checked_now;

                // Update Progress di Tabel Produksi (Rak QC Logic)
                // Kita update di baris pertama batch ini sebagai penanda progress
                DB::table('produksi_batches')->where('id', $id)->update([
                    'total_checked' => $ref->total_checked + $checked_now,
                    'qty_hasil_ng'  => $ref->qty_hasil_ng + $final_ng,
                    'updated_at'    => $now
                ]);

                // Kapan COMPLETED? Hanya jika sudah memenuhi target
                if ($total_after_this >= $total_target) {
                    DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                        'status' => 'COMPLETED',
                        'qc_at'  => $qcThreshold,
                        'qc_by'  => $inspector
                    ]);
                }

            } else {
                // LOGIKA WELDING (PARTIAL)
                $ref = DB::table('welding_batches')->where('id', $id)->first();
                $batchNo = $ref->no_produksi_stamping;
                $partNo  = $ref->part_no;
                $origin  = 'WELDING';

                $total_target = $ref->qty_masuk;
                $already_checked = $ref->total_checked ?? 0;
                $total_after_this = $already_checked + $checked_now;

                DB::table('welding_batches')->where('id', $id)->update([
                    'qty_ok'        => $ref->qty_ok + $final_ok, 
                    'qty_ng'        => $ref->qty_ng + $final_ng,
                    'total_checked' => $already_checked + $checked_now,
                    'updated_at'    => $now
                ]);

                if ($total_after_this >= $total_target) {
                    DB::table('welding_batches')->where('id', $id)->update([
                        'status' => 'COMPLETED', 
                        'qc_at'  => $qcThreshold,
                        'qc_by'  => $inspector
                    ]);
                }
            }

            // 1. Sinkronisasi Dashboard Harian (Tetap cumulative)
            $actual_id = $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            // 2. Simpan Detail NG
            $all_ng_names = [];
            if ($request->has('ng_details')) {
                foreach ($request->ng_details as $name => $qty) {
                    if ((int)$qty > 0) {
                        $all_ng_names[] = "$name ($qty)";
                        DB::table('production_ng_logs')->insert([
                            'actual_id'   => $actual_id, 
                            'no_produksi' => $batchNo,
                            'ng_type'     => $name, 
                            'qty'         => $qty, 
                            'created_at'  => $now 
                        ]);
                    }
                }
            }
            $summary_reason = !empty($all_ng_names) ? implode(', ', $all_ng_names) : 'OK GOODS';

            // 3. Simpan ke Riwayat Inspeksi (Archive)
            // Di sini kita catat qty_ret dan total_checked rill
            DB::table('quality_inspections')->insert([
                'batch_no' => $batchNo, 
                'origin' => $origin, 
                'part_no' => $partNo,
                'qty_from_prod' => $total_target, // Target awal
                'qty_ok' => $final_ok, 
                'qty_ng' => $final_ng, 
                'qty_ret' => $final_ret, // Kolom baru rill
                'total_checked' => $checked_now, // Yang dicek di sesi ini
                'ng_reason' => $summary_reason, 
                'inspector' => $inspector, 
                'status' => ($total_after_this >= $total_target) ? 'COMPLETED' : 'PARTIAL',
                'created_at' => $now, 
                'updated_at' => $now
            ]);

            // 4. Update Stok FG (Barang OK langsung masuk gudang meskipun partial)
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if ($fg) {
                DB::table('finished_goods')->where('id', $fg->id)->update([
                    'actual_stock' => ($fg->actual_stock + $final_ok),
                    'act_stock'    => (($fg->act_stock ?? 0) + $final_ok),
                    'updated_at'   => now()
                ]);

                DB::table('production_logs')->insert([
                    'part_no'      => $partNo,
                    'qty'          => $final_ok,
                    'process_type' => 'FG', 
                    'created_at'   => $now
                ]);
            }

            DB::commit();
            return back()->with('success', ($total_after_this >= $total_target) ? "Batch Selesai Penuh!" : "Partial Sukses! Sisa " . ($total_target - $total_after_this) . " pcs.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', "GAGAL: " . $e->getMessage()); 
        }
    }

    // Fungsi updateDashboardActual, history, dan destroy TIDAK DIUBAH (Sesuai Permintaan)
    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin) {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        $today = date('Y-m-d');
        $exist = DB::table('production_actuals')->where('part_no', $partNo)->where('line_code', $lineCode)->whereDate('created_at', $today)->first();

        if ($exist) {
            DB::table('production_actuals')->where('id', $exist->id)->update([
                'qty_ok' => ($exist->qty_ok + $qtyOk), 'qty_ng' => ($exist->qty_ng + $qtyNg), 'updated_at' => now()
            ]);
            return $exist->id;
        } else {
            return DB::table('production_actuals')->insertGetId([
                'part_no' => $partNo, 'line_code' => $lineCode, 'qty_ok' => $qtyOk, 'qty_ng' => $qtyNg,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }

    public function history() {
        $historyData = DB::table('quality_inspections')->orderBy('created_at', 'desc')->get();
        return view('Quality.history', compact('historyData'));
    }

    public function destroy($type, $id) {
        try {
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                DB::table('produksi_batches')->where('no_produksi', $batch->no_produksi)->delete();
            } else {
                DB::table('welding_batches')->where('id', $id)->delete();
            }
            return back()->with('success', 'Batch dihapus.');
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }
}