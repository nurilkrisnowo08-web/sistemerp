<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProduksiController extends Controller
{
    public function index()
    {
        $customerFilter = request('customer');

        $query = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->select(
                'produksi_batches.no_produksi', 
                'produksi_batches.shift',
                'produksi_batches.material_code',
                'produksi_batches.status',
                'produksi_batches.qty_return', 
                'produksi_batches.created_at',
                'produksi_batches.cavity',
                'rm_stocks.coil_id',
                'rm_stocks.customer',
                'rm_stocks.size',
                'rm_stocks.spec',
                'rm_stocks.material_name',
                DB::raw('GROUP_CONCAT(line.kode_Line SEPARATOR ", ") as line_names'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs * produksi_batches.cavity) as total_qty_batch'), 
                DB::raw('MIN(produksi_batches.id) as batch_id')
            )
            ->where(function($q) {
                $q->where('produksi_batches.status', 'PROSES')
                  ->orWhere('produksi_batches.qty_return', '>', 0);
            })
            ->groupBy(
                'no_produksi', 'shift', 'material_code', 'status', 'qty_return',
                'created_at', 'produksi_batches.cavity', 'coil_id', 'customer', 'size', 'spec', 'material_name'
            );

        if ($customerFilter) {
            $query->where('rm_stocks.customer', trim($customerFilter));
        }

        $activeProductions = $query->orderBy('batch_id', 'desc')->get();
        
        $materials = DB::table('rm_stocks')
            ->where('stock_pcs', '>', 0)
            ->select(
                'coil_id', 
                DB::raw('MAX(id) as id'), 
                DB::raw('MAX(stock_pcs) as stock_pcs'), 
                DB::raw('MAX(spec) as spec'), 
                DB::raw('MAX(size) as size'), 
                DB::raw('MAX(customer) as customer')
            )
            ->groupBy('coil_id') 
            ->get(); 

        $customers = DB::table('customers')->get();
        $lines = DB::table('line')->get(); 

        return view('Produksi.index', compact('activeProductions', 'materials', 'customers', 'lines'));
    }

    public function productionStore(Request $request) { return $this->store($request); }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $no_produksi = 'PROD-' . date('Ymd-His');
            $rmInfo = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
            
            if(!$rmInfo) throw new \Exception("Material Unit not found!");

            $parts = $request->part_no;
            if (!is_array($parts)) { $parts = [$parts]; }
            
            $primary_part = $parts[0] ?? $request->material_code;

            $batchId = DB::table('produksi_batches')->insertGetId([
                'no_produksi'   => $no_produksi,
                'mesin_id'      => $request->mesin_id,
                'rm_stock_id'   => $request->rm_stock_id,
                'material_code' => $primary_part, 
                'shift'         => $request->shift,
                'qty_ambil_pcs' => $request->qty_ambil_pcs,
                'cavity'        => $request->cavity ?? 1,
                'status'        => 'PROSES',
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
            
            if (is_array($parts)) {
                foreach ($parts as $index => $part_number) {
                    if (!empty($part_number)) {
                        DB::table('produksi_batch_parts')->insert([
                            'batch_id'     => $batchId,
                            'part_no'      => $part_number,
                            'qty_hasil_ok' => 0,
                            'qty_ng'       => 0,
                            'created_at'   => now(),
                            'updated_at'   => now()
                        ]);
                    }
                }
            }

            DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->decrement('stock_pcs', $request->qty_ambil_pcs);

            DB::table('rm_production_logs')->insert([
                'rm_stock_id'   => $request->rm_stock_id,
                'material_code' => $primary_part,
                'pcs_used'      => $request->qty_ambil_pcs, 
                'no_produksi'   => $no_produksi,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Batch Produksi Multi-Part Dimulai!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Start: ' . $e->getMessage());
        }
    }

    public function storeResult(Request $request, $id) { /* Tetap */ return back(); }

    public function updateResult(Request $request, $id)
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        if (!$p) return redirect()->back()->with('error', 'Batch tidak ditemukan!');

        DB::beginTransaction();
        try {
            $total_ng_spesifik = 0;
            $ng_details = [];
            if ($request->has('ng_detail_type')) {
                foreach ($request->ng_detail_type as $idx => $type) {
                    $q = (int)$request->ng_detail_qty[$idx];
                    if ($q > 0) { $total_ng_spesifik += $q; $ng_details[] = ['type' => $type, 'qty' => $q]; }
                }
            }

            $first_ok = 0;
            $first_ng = 0;
            $qty_ok_new = (int)$request->qty_hasil_ok; 

            // LOGIKA MULTI-PART (Input OK dan NG per part)
            if ($request->has('qty_hasil_ok_parts')) {
                $is_first = true;
                foreach ($request->qty_hasil_ok_parts as $part_id => $qty) {
                    $qty_ok = (int)$qty;
                    $qty_ng = (int)($request->qty_hasil_ng_parts[$part_id] ?? 0);

                    if ($is_first) { $first_ok = $qty_ok; $first_ng = $qty_ng; $is_first = false; } 

                    $child = DB::table('produksi_batch_parts')->where('id', $part_id)->first();
                    if ($child) {
                        DB::table('produksi_batch_parts')->where('id', $part_id)->update([
                            'qty_hasil_ok' => $child->qty_hasil_ok + $qty_ok,
                            'qty_ng'       => $child->qty_ng + $qty_ng,
                            'updated_at'   => now()
                        ]);

                        $cleanPart = str_replace([' ', '-'], '', trim($child->part_no));
                        $partMaster = DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])->first();
                        $target = ($partMaster && $partMaster->next_process) ? strtoupper($partMaster->next_process) : 'FG';

                        if ($qty_ok > 0) {
                            DB::table('production_logs')->insert([
                                'part_no' => $child->part_no, 'qty' => $qty_ok, 
                                'process_type' => ($target == 'WELDING') ? 'WELDING' : 'FG', 
                                'created_at' => now(), 'updated_at' => now()
                            ]);

                            if ($target == 'WELDING') {
                                DB::table('finished_goods')->where('part_no', $child->part_no)->increment('welding_stock', $qty_ok, ['updated_at' => now()]);
                            } else {
                                DB::table('finished_goods')->where('part_no', $child->part_no)->increment('actual_stock', $qty_ok, ['updated_at' => now()]);
                            }
                        }
                    }
                }
                $qty_ok_new = $first_ok;
                $ng_parent_update = $first_ng; 
            } else {
                $ng_parent_update = $total_ng_spesifik; 
                
                $cleanPart = str_replace([' ', '-'], '', trim($p->material_code));
                $partMaster = DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])->first();
                $target = ($partMaster && $partMaster->next_process) ? strtoupper($partMaster->next_process) : 'FG';

                if ($qty_ok_new > 0) {
                    DB::table('production_logs')->insert(['part_no' => $p->material_code, 'qty' => $qty_ok_new, 'process_type' => ($target == 'WELDING') ? 'WELDING' : 'FG', 'created_at' => now(), 'updated_at' => now()]);
                }

                if ($target == 'WELDING') {
                    DB::table('finished_goods')->where('part_no', $p->material_code)->increment('welding_stock', $qty_ok_new, ['updated_at' => now()]);
                } else {
                    DB::table('finished_goods')->where('part_no', $p->material_code)->increment('actual_stock', $qty_ok_new, ['updated_at' => now()]);
                }
            }

            // ✨ LOGIKA BARU: RETURN MURNI DALAM BENTUK SHEET/LEMBAR
            // Nggak ada lagi acara bagi-bagian. Lu masukin 28, yang balik ke gudang ya 28.
            $qty_return_sheet = (int)$request->qty_return_warehouse; 

            if ($qty_return_sheet > 0) {
                $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
                if ($rmInfo) {
                    DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $qty_return_sheet);
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $p->rm_stock_id, 'material_code' => $p->material_code,
                        'pcs_in' => $qty_return_sheet, 'source' => 'return',
                        'no_produksi' => $p->no_produksi, 'created_at' => now()
                    ]);
                }
            }

            $status_akhir = $request->status ?? 'COMPLETED';

            DB::table('produksi_batches')->where('id', $id)->update([
                'qty_hasil_ok' => $p->qty_hasil_ok + $qty_ok_new,
                'qty_ng_process' => $p->qty_ng_process + $ng_parent_update,
                'qty_hasil_ng' => $p->qty_hasil_ng + $ng_parent_update,
                'qty_return_warehouse' => $p->qty_return_warehouse + $qty_return_sheet, // Masuk Murni Sheet
                'qty_return' => 0, 
                'status' => $status_akhir,
                'keterangan' => $request->keterangan,
                'updated_at' => now()
            ]);

            $this->syncToActual($id); 

            $actual = DB::table('production_actuals')->where('part_no', $p->material_code)->whereDate('created_at', date('Y-m-d', strtotime($p->created_at)))->first();
            if ($actual) {
                if (!empty($ng_details)) {
                    foreach ($ng_details as $detail) {
                        DB::table('production_ng_logs')->insert(['actual_id' => $actual->id, 'no_produksi' => $p->no_produksi, 'ng_type' => $detail['type'], 'qty' => $detail['qty'], 'created_at' => now()]);
                    }
                }
            }

            DB::commit(); 
            return redirect()->route('produksi.index')->with('success', 'Hasil Produksi Dikirim! Return tercatat murni dalam Lembar/Sheet.');
        } catch (\Exception $e) { 
            DB::rollback(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    private function syncToActual($batchId)
    {
        $batch = DB::table('produksi_batches')->where('id', $batchId)->first();
        if (!$batch) return;
        $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
        $dateOnly = date('Y-m-d', strtotime($batch->created_at));
        DB::table('production_actuals')->updateOrInsert(
            ['part_no' => $batch->material_code, 'line_code' => $lineCode, 'created_at' => $dateOnly],
            ['shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'updated_at' => now()]
        );
    }

    public function getBatchDeepDive($no_produksi) { /* Tetap */ }
    public function history(Request $request) { /* Tetap */ }
    
    public function getSpecsByCustomer($customer) {
        $specs = DB::table('rm_stocks')->where('customer', trim($customer))->where('stock_pcs', '>', 0)->select(DB::raw('TRIM(spec) as spec'), 'size', DB::raw("REPLACE(size, ' ', '') as size_clean"))->groupBy('spec', 'size', 'size_clean')->get();
        return response()->json($specs);
    }

    public function getPartsBySpec(Request $request) {
        $parts = DB::table('rm_stocks')->where('customer', trim($request->customer))->where(DB::raw('TRIM(spec)'), trim($request->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $request->size)) ->select('material_code', 'material_name')->distinct()->get();
        return response()->json($parts);
    }

    public function getBundlesByPart(Request $request, $material_code = null) 
    {
        $code = $material_code ? urldecode($material_code) : ($request->material_code ?? $request->input('material_code'));
        $customer = $request->customer;
        $spec = $request->spec;
        $size = $request->size;

        $query = DB::table('rm_stocks')->where('stock_pcs', '>', 0);

        if ($customer) $query->where('customer', trim($customer));
        if ($spec) $query->where(DB::raw('TRIM(spec)'), trim($spec));
        if ($size) $query->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $size));
        
        if ($code) {
            $cleanCode = trim($code);
            $query->where(function($q) use ($cleanCode) {
                $q->where('material_code', $cleanCode)
                  ->orWhere('material_name', $cleanCode)
                  ->orWhereRaw("REPLACE(material_code, ' ', '') = ?", [str_replace(' ', '', $cleanCode)]);
            });
        }

        $bundles = $query->select('coil_id', DB::raw('MAX(id) as id'), DB::raw('MAX(stock_pcs) as stock_pcs'), 'size', DB::raw('MAX(cavity) as cavity'))
            ->groupBy('coil_id', 'size')
            ->get();

        if ($bundles->isEmpty() && $code) {
            $cleanCode = trim($code);
            $bundles = DB::table('rm_stocks')
                ->where('stock_pcs', '>', 0)
                ->where(function($q) use ($cleanCode) {
                    $q->where('material_code', $cleanCode)
                      ->orWhere('material_name', $cleanCode)
                      ->orWhereRaw("REPLACE(material_code, ' ', '') = ?", [str_replace(' ', '', $cleanCode)]);
                })
                ->select('coil_id', DB::raw('MAX(id) as id'), DB::raw('MAX(stock_pcs) as stock_pcs'), 'size', DB::raw('MAX(cavity) as cavity'))
                ->groupBy('coil_id', 'size')
                ->get();
        }

        return response()->json($bundles);
    }

    public function returnToRM($id) {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
        DB::beginTransaction();
        try { 
            if ($p && $rmInfo) { 
                DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $p->qty_ambil_pcs); 
                DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->delete(); 
            } 
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete(); 
            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.'); 
        } catch (\Exception $e) { DB::rollback(); return back(); }
    }

    public function getPartDetail($id) { /* Tetap */ return response()->json(['sisa_jalan' => 0, 'stock_pcs' => 0]); }
    public function resolveInterruption(Request $request, $id) { return $this->updateResult($request, $id); }
    public function gateConfirm(Request $request, $id) { return $this->updateResult($request, $id); }
    public function reportProblem(Request $request, $id) { DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROBLEM', 'keterangan' => '⚠️ DIES RUSAK: ' . $request->problem_note, 'updated_at' => now()]); return redirect()->back()->with('error', 'Laporan kendala telah dikirim!'); }
}