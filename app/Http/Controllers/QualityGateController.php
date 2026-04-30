<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QualityGateController extends Controller {

    public function index() {
        // Fungsi index tetap sama persis sesuai kodingan Bapak
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
            $inspector = $request->inspector_name ?? Auth::user()?->name ?? 'QC_OFFICER';

            if ($type == 'stamping') {
                $ref = DB::table('produksi_batches')->where('id', $id)->first();
                if (!$ref) throw new \Exception("Batch tidak ditemukan.");

                $batchNo = $ref->no_produksi;
                $partNo  = $ref->material_code;

                $lines = DB::table('produksi_batches')->where('no_produksi', $batchNo)->get();
                $total_ok_prod = $lines->sum('qty_hasil_ok');
                
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$request->qty_ng_final;
                $qty_awal = $total_ok_prod;
                $origin   = 'STAMPING';

                // Logika penyesuaian selisih NG ke record produksi rill
                if ($final_ok < $total_ok_prod) {
                    $selisih = $total_ok_prod - $final_ok;
                    $firstLine = $lines->first();
                    DB::table('produksi_batches')->where('id', $firstLine->id)->update([
                        'qty_hasil_ok' => ($firstLine->qty_hasil_ok - $selisih),
                        'qty_hasil_ng' => ($firstLine->qty_hasil_ng + $selisih),
                    ]);
                }

                DB::table('produksi_batches')->where('no_produksi', $batchNo)->update([
                    'status'     => 'COMPLETED',
                    'qc_at'      => now(),
                    'qc_by'      => $inspector,
                    'updated_at' => now()
                ]);

            } else {
                // Logika Welding
                $batch = DB::table('welding_batches')->where('id', $id)->first();
                if (!$batch) throw new \Exception("Batch Welding tidak ditemukan.");

                $batchNo = $batch->no_produksi_stamping;
                $partNo  = $batch->part_no;
                $final_ok = (int)$request->qty_ok_final;
                $final_ng = (int)$request->qty_ng_final;
                $qty_awal = $batch->qty_ok;
                $origin   = 'WELDING';

                DB::table('welding_batches')->where('id', $id)->update([
                    'qty_ok'     => $final_ok,
                    'qty_ng'     => $batch->qty_ng + $final_ng,
                    'status'     => 'COMPLETED',
                    'qc_at'      => now(),
                    'qc_by'      => $inspector,
                    'updated_at' => now()
                ]);
            }

            // ✨ 1. UPDATE DASHBOARD HARI INI (Dapetin ID rill untuk detail NG)
            $actualId = $this->updateDashboardActual($partNo, $final_ok, $final_ng, $origin);

            // ✨ 2. SIMPAN RINCIAN NG DETAIL (Looping)
            $ngBreakdownSummary = [];
            if ($request->has('ng_details')) {
                // Bersihkan log lama batch ini agar tidak double rill
                DB::table('production_ng_logs')->where('no_produksi', $batchNo)->delete();

                foreach ($request->ng_details as $ngName => $ngQty) {
                    if ((int)$ngQty > 0) {
                        DB::table('production_ng_logs')->insert([
                            'actual_id'   => $actualId,
                            'no_produksi' => $batchNo,
                            'ng_type'     => $ngName,
                            'qty'         => $ngQty,
                            'created_at'  => now()
                        ]);
                        $ngBreakdownSummary[] = "$ngName ($ngQty)";
                    }
                }
            }

            // Gabungkan alasan jadi string untuk archive history
            $finalNgReason = count($ngBreakdownSummary) > 0 ? implode(', ', $ngBreakdownSummary) : 'OK_GOODS';

            // ✨ 3. SIMPAN KE QUALITY_INSPECTIONS (Archive History)
            DB::table('quality_inspections')->insert([
                'batch_no'      => $batchNo,
                'origin'        => $origin,
                'part_no'       => $partNo,
                'qty_from_prod' => $qty_awal,
                'qty_ok'        => $final_ok,
                'qty_ng'        => $final_ng, 
                'ng_reason'     => $finalNgReason, 
                'inspector'     => $inspector,
                'status'        => 'APPROVED',
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // ✨ 4. UPDATE STOK FG (Logic lama Bapak yang diperkuat)
            $cleanPart = str_replace([' ', '-'], '', trim($partNo));
            $fg = DB::table('finished_goods')
                ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                ->first();

            if ($fg) {
                // Update KEDUA kolom stok Bapak sesuai struktur database rill
                DB::table('finished_goods')->where('id', $fg->id)->update([
                    'actual_stock' => ($fg->actual_stock + $final_ok),
                    'act_stock'    => (($fg->act_stock ?? 0) + $final_ok),
                    'updated_at'   => now()
                ]);

                // ✨ 5. INSERT LOG MUTASI (Agar angka +IN muncul di Dashboard Inventory Bapak!)
                DB::table('production_logs')->insert([
                    'part_no'      => $partNo,
                    'qty'          => $final_ok,
                    'process_type' => ($origin == 'STAMPING' ? 'STP' : 'WLD'),
                    'no_produksi'  => $batchNo,
                    'created_at'   => now()
                ]);
            } else {
                throw new \Exception("Part No [$partNo] Gak Ada di Finished Goods. Data dibatalkan rill!");
            }

            DB::commit();
            return back()->with('success', "Barang $partNo Berhasil Lulus Verifikasi QC.");

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', "GAGAL: " . $e->getMessage()); 
        }
    }

    // ✨ FUNGSI SAKTI: Kembalikan ID untuk sinkronisasi Log NG rill
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
                'part_no' => $partNo,
                'line_code' => $lineCode,
                'qty_ok' => $qtyOk,
                'qty_ng' => $qtyNg,
                'created_at' => now(),
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