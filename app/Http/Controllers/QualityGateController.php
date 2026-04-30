<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // Gabungkan batch agar tidak pecah di tampilan QC
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

        $ngStamping = DB::table('master_ngs')->whereIn('category', ['STAMPING', 'GENERAL'])->get();
        $ngWelding = DB::table('master_ngs')->whereIn('category', ['WELDING', 'GENERAL'])->get();

        return view('Quality.index', compact('produksiQueue', 'weldingQueue', 'ngStamping', 'ngWelding'));
    }

    public function approve(Request $request, $type, $id) {
        DB::beginTransaction();
        try {
            // Ambil data inspector rill
            $inspector = $request->inspector_name ?? Auth::user()->name ?? 'QC_SYSTEM';

            if ($type == 'stamping') {
                $ref = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch Stamping Gak Ketemu rill!");

                $batchNo = $ref->no_produksi;
                $partNo  = $ref->material_code;
                $origin  = 'STAMPING';

                $lines = DB::table('produksi_batches')->where('no_produksi', $batchNo)->get();
                $total_ok_prod = $lines->sum('qty_hasil_ok');
                $qty_awal = $total_ok_prod;

                $qc_verified_ok = (int)$request->qty_ok_final;
                $qc_verified_ng = (int)$request->qty_ng_final;

                // Update selisih kalau QC nemu NG baru
                if ($qc_verified_ok < $total_ok_prod) {
                    $selisih = $total_ok_prod - $qc_verified_ok;
                    $firstLine = $lines->first();
                    DB::table('produksi_batches')->where('id', $firstLine->id)->update([
                        'qty_hasil_ok' => ($firstLine->qty_hasil_ok - $selisih),
                        'qty_hasil_ng' => ($firstLine->qty_hasil_ng + $selisih),
                    ]);
                }

                // Kunci Batch jadi COMPLETED
                DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                    'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspector, 'updated_at' => now()
                ]);

            } else {
                $ref = DB::table('welding_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch Welding Gak Ketemu!");

                $batchNo = $ref->no_produksi_stamping;
                $partNo  = $ref->part_no;
                $origin  = 'WELDING';
                $qty_awal = $ref->qty_ok;

                $qc_verified_ok = (int)$request->qty_ok_final;
                $qc_verified_ng = (int)$request->qty_ng_final;

                DB::table('welding_batches')->where('id', $id)->update([
                    'qty_ok' => $qc_verified_ok,
                    'qty_ng' => $ref->qty_ng + $qc_verified_ng,
                    'status' => 'COMPLETED', 'qc_at' => now(), 'qc_by' => $inspector, 'updated_at' => now()
                ]);
            }

            // ✨ 1. SINKRONISASI DASHBOARD ACTUAL (Buat Grafik Naga)
            $actual_id = $this->updateDashboardActual($partNo, $qc_verified_ok, $qc_verified_ng, $origin);

            // ✨ 2. SIMPAN RINCIAN NG KE PRODUCTION_NG_LOGS
            DB::table('production_ng_logs')->where('no_produksi', $batchNo)->delete();
            $all_ng_names = [];
            if ($request->has('ng_details')) {
                foreach ($request->ng_details as $ng_name => $qty) {
                    if ((int)$qty > 0) {
                        $all_ng_names[] = "$ng_name ($qty)";
                        DB::table('production_ng_logs')->insert([
                            'actual_id'   => $actual_id,
                            'no_produksi' => $batchNo,
                            'ng_type'     => $ng_name,
                            'qty'         => (int)$qty,
                            'created_at'  => now()
                        ]);
                    }
                }
            }
            $summary_reason = !empty($all_ng_names) ? implode(', ', $all_ng_names) : 'OK GOODS';

            // ✨ 3. SIMPAN KE QUALITY_INSPECTIONS (Archive History)
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal + $qc_verified_ng,
                'qty_ok'        => $qc_verified_ok,
                'qty_ng'        => $qc_verified_ng, 
                'ng_reason'     => $summary_reason,
                'inspector'     => $inspector,
                'status'        => 'APPROVED',
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            // ✨ 4. UPDATE STOK & LOG INVENTORY (BIAR MUNCUL DI image_1d957c.png)
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if ($fg) {
                DB::table('finished_goods')->where('id', $fg->id)->update([
                    'actual_stock' => ($fg->actual_stock + $qc_verified_ok),
                    'act_stock'    => (($fg->act_stock ?? 0) + $qc_verified_ok),
                    'updated_at'   => now()
                ]);

                // ✨ BARIS INI WAJIB: Log Masuk Dashboard Inventory rill!
                DB::table('production_logs')->insert([
                    'part_no'      => $partNo,
                    'qty'          => $qc_verified_ok,
                    'process_type' => ($origin == 'STAMPING' ? 'STP' : 'WLD'),
                    'no_produksi'  => $batchNo,
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return back()->with('success', "COMMIT BERHASIL rill! Data sudah terkirim ke Inventory.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            // Kalau gagal, ini bakal munculin tulisan merah apa yang salah rill!
            return back()->with('error', "GAGAL COMMIT: " . $e->getMessage()); 
        }
    }

    private function updateDashboardActual($partNo, $qtyOk, $qtyNg, $origin) {
        $lineCode = ($origin == 'STAMPING') ? 'LINE A' : 'WELDING AREA';
        $today = date('Y-m-d');

        $exist = DB::table('production_actuals')
            ->where('part_no', $partNo)
            ->where('line_code', $lineCode)
            ->whereDate('created_at', $today)
            ->first();

        if ($exist) {
            DB::table('production_actuals')->where('id', $exist->id)->update([
                'qty_ok' => ($exist->qty_ok + $qtyOk),
                'qty_ng' => ($exist->qty_ng + $qtyNg),
                'updated_at' => now()
            ]);
            return $exist->id;
        } else {
            return DB::table('production_actuals')->insertGetId([
                'part_no' => $partNo, 'line_code' => $lineCode,
                'qty_ok' => $qtyOk, 'qty_ng' => $qtyNg,
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