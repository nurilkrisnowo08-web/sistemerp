<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // ✨ FIX GROUPING: Grouping berdasarkan no_produksi saja agar tidak pecah rill
        $produksiQueue = DB::table('produksi_batches')
            ->where('status', 'WAITING_QC')
            ->select(
                'no_produksi',
                'material_code',
                'keterangan',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('SUM(qty_hasil_ok) as qty_hasil_ok'), 
                DB::raw('MIN(id) as id') 
            )
            ->groupBy('no_produksi', 'material_code', 'keterangan')
            ->get();
        
        $weldingQueue = DB::table('welding_batches')
            ->where('status', 'COMPLETED')
            ->whereNull('qc_at')
            ->orderBy('created_at', 'desc')
            ->get();

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

                // Hitung total dari semua line batch ini
                $lines = DB::table('produksi_batches')->where('no_produksi', $batchNo)->get();
                $total_ok_prod = $lines->sum('qty_hasil_ok');
                
                $qc_verified_ok = (int)$request->qty_ok_final;
                $qc_verified_ng = (int)$request->qty_ng_final;

                // Update baris pertama untuk selisih NG jika ada
                if ($qc_verified_ok < $total_ok_prod) {
                    $selisih = $total_ok_prod - $qc_verified_ok;
                    $firstLine = $lines->first();
                    DB::table('produksi_batches')->where('id', $firstLine->id)->update([
                        'qty_hasil_ok' => ($firstLine->qty_hasil_ok - $selisih),
                        'qty_hasil_ng' => ($firstLine->qty_hasil_ng + $selisih),
                    ]);
                }

                // Selesaikan semua batch terkait
                DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                    'status'     => 'COMPLETED',
                    'qc_at'      => now(),
                    'qc_by'      => Auth::user()->name,
                    'updated_at' => now()
                ]);
                
                $final_ok = $qc_verified_ok;
                $final_ng = $qc_verified_ng;
                $origin   = 'STAMPING';
                $qty_awal = $total_ok_prod;

            } else {
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
                    'qc_by'      => Auth::user()->name,
                    'updated_at' => now()
                ]);
                
                $qty_awal = $batch->qty_ok;
                $origin   = 'WELDING';
            }

            // ✨ 1. SIMPAN RINCIAN NG KE LOGS ✨
            DB::table('production_ng_logs')->where('no_produksi', $batchNo)->delete();
            $all_ng_names = [];
            if ($request->has('ng_details')) {
                foreach ($request->ng_details as $ng_name => $qty) {
                    if ((int)$qty > 0) {
                        $all_ng_names[] = "$ng_name ($qty)";
                        DB::table('production_ng_logs')->insert([
                            'no_produksi' => $batchNo,
                            'ng_type'     => $ng_name,
                            'qty'         => $qty,
                            'created_at'  => now()
                        ]);
                    }
                }
            }
            $summary_reason = !empty($all_ng_names) ? implode(', ', $all_ng_names) : 'OK GOODS';

            // ✨ 2. SIMPAN KE QUALITY_INSPECTIONS (Data image_2824e5.png) ✨
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $final_ok,
                'qty_ng'        => $final_ng, 
                'ng_reason'     => $summary_reason, 
                'inspector'     => Auth::user()->name,
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // ✨ 3. UPDATE STOK FG (Gunakan pengecekan aman) ✨
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
            }

            // Update Dashboard
            $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            DB::commit();
            return back()->with('success', "Batch $batchNo Lulus Verifikasi QC rill!");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', "GAGAL SIMPAN: " . $e->getMessage()); 
        }
    }

    // ✨ FIX SAKTI: Pengecekan Eksistensi Sebelum Update Dashboard rill ✨
    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin) {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        $today = date('Y-m-d');

        $exist = DB::table('production_actuals')
            ->where('part_no', $partNo)
            ->where('line_code', $lineCode)
            ->where('created_at', $today)
            ->first();

        if ($exist) {
            DB::table('production_actuals')->where('id', $exist->id)->update([
                'qty_ok' => ($exist->qty_ok + $qtyOk),
                'qty_ng' => ($exist->qty_ng + $qtyNg),
                'updated_at' => now()
            ]);
        } else {
            DB::table('production_actuals')->insert([
                'part_no' => $partNo,
                'line_code' => $lineCode,
                'created_at' => $today,
                'qty_ok' => $qtyOk,
                'qty_ng' => $qtyNg,
                'updated_at' => now()
            ]);
        }
    }

    public function destroy($type, $id) {
        try {
            if ($type == 'stamping') {
                $batch = DB::table('produksi_batches')->where('id', $id)->first();
                DB::table('produksi_batches')->where('no_produksi', $batch->no_produksi)->delete();
            } else {
                DB::table('welding_batches')->where('id', $id)->delete();
            }
            return back()->with('success', 'Batch berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function history() {
        $historyData = DB::table('quality_inspections')->orderBy('created_at', 'desc')->get();
        return view('Quality.history', compact('historyData'));
    }
}