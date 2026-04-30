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

            if ($type == 'stamping') {
                $ref = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch tidak ditemukan.");

                $batchNo = $ref->no_produksi;
                $partNo  = $ref->material_code;
                $origin  = 'STAMPING';

                $lines = DB::table('produksi_batches')->where('no_produksi', $batchNo)->get();
                $total_ok_prod = $lines->sum('qty_hasil_ok');
                
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$request->qty_ng_final;

                if ($final_ok < $total_ok_prod) {
                    $selisih = $total_ok_prod - $final_ok;
                    DB::table('produksi_batches')->where('id', $lines->first()->id)->update([
                        'qty_hasil_ok' => ($lines->first()->qty_hasil_ok - $selisih),
                        'qty_hasil_ng' => ($lines->first()->qty_hasil_ng + $selisih),
                    ]);
                }

                DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                    'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspector, 'updated_at' => now()
                ]);

            } else {
                $ref = DB::table('welding_batches')->where('id', $id)->first();
                $batchNo = $ref->no_produksi_stamping;
                $partNo  = $ref->part_no;
                $origin  = 'WELDING';
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$request->qty_ng_final;

                DB::table('welding_batches')->where('id', $id)->update([
                    'qty_ok' => $final_ok, 'qty_ng' => $ref->qty_ng + $final_ng,
                    'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspector, 'updated_at' => now()
                ]);
            }

            // 1. Sinkronisasi Dashboard Harian
            $actual_id = $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            // 2. Simpan Detail NG (VERSI KUMULATIF RILL)
            // ✨ PERBAIKAN: Baris delete() saya hapus agar temuan Produksi TIDAK TERHAPUS oleh QC ✨
            
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
                            'created_at'  => now()
                        ]);
                    }
                }
            }
            $summary_reason = !empty($all_ng_names) ? implode(', ', $all_ng_names) : 'OK GOODS';

            // 3. Simpan ke Riwayat Inspeksi (Archive)
            DB::table('quality_inspections')->insert([
                'batch_no' => $batchNo, 'origin' => $origin, 'part_no' => $partNo,
                'qty_from_prod' => ($final_ok + $final_ng), 'qty_ok' => $final_ok, 'qty_ng' => $final_ng, 
                'ng_reason' => $summary_reason, 'inspector' => $inspector, 'status' => 'APPROVED',
                'created_at' => now(), 'updated_at' => now()
            ]);

            // 4. Update Stok FG & Log Mutasi
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
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return back()->with('success', "COMMIT SUKSES rill! Angka masuk ke Dashboard & FG.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', "GAGAL: " . $e->getMessage()); 
        }
    }

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