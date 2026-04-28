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
                'produksi_batches.created_at',
                'rm_stocks.coil_id',
                'rm_stocks.customer',
                'rm_stocks.size',
                'rm_stocks.spec',
                'rm_stocks.material_name',
                DB::raw('GROUP_CONCAT(line.kode_Line SEPARATOR ", ") as line_names'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs) as total_qty_batch'),
                DB::raw('MIN(produksi_batches.id) as batch_id')
            )
            ->where('produksi_batches.status', 'PROSES')
            ->groupBy(
                'no_produksi', 'shift', 'material_code', 'status', 
                'created_at', 'coil_id', 'customer', 'size', 'spec', 'material_name'
            );

        if ($customerFilter) {
            $query->where('rm_stocks.customer', trim($customerFilter));
        }

        $activeProductions = $query->orderBy('batch_id', 'desc')->get();
        
        $materials = DB::table('rm_stocks')->where('stock_pcs', '>', 0)->get(); 
        $customers = DB::table('customers')->get();
        $lines = DB::table('line')->get(); 

        return view('Produksi.index', compact('activeProductions', 'materials', 'customers', 'lines'));
    }

    public function productionStore(Request $request) { return $this->store($request); }

    /**
     * ✨ FIX: FUNGSI STORE (DITAMBAHKAN AGAR TIDAK ERROR 500)
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('produksi_batches')->insert([
                'no_produksi'   => 'PROD-' . date('Ymd-His'),
                'mesin_id'      => $request->mesin_id,
                'rm_stock_id'   => $request->rm_stock_id,
                'material_code' => $request->material_code,
                'shift'         => $request->shift,
                'qty_ambil_pcs' => $request->qty_ambil_pcs,
                'status'        => 'PROSES',
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
            
            DB::table('rm_stocks')->where('id', $request->rm_stock_id)->decrement('stock_pcs', $request->qty_ambil_pcs);

            DB::commit();
            return redirect()->back()->with('success', 'Batch Produksi Dimulai!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Start: ' . $e->getMessage());
        }
    }

    public function storeResult(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('produksi_batches')->where('id', $id)->first();
            
            DB::table('produksi_batches')->where('id', $id)->update([
                'qty_hasil_ok' => $request->qty_ok,
                'qty_hasil_ng' => $request->qty_ng,
                'status'       => 'COMPLETED',
                'updated_at'   => now()
            ]);

            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();

            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('welding_stock', $request->qty_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert([
                    'part_no'      => $batch->material_code,
                    'qty'          => $request->qty_ok,
                    'process_type' => 'WELDING',
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            } else {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('stock', $request->qty_ok, ['updated_at' => now()]);
            }

            DB::commit();
            return back()->with('success', 'Data Transmitted!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transmission Failed: ' . $e->getMessage());
        }
    }

    public function updateResult(Request $request, $id) 
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        if (!$p) return redirect()->back()->with('error', 'Batch tidak ditemukan!');

        $qty_ok = (int)$request->qty_hasil_ok; 
        
        $total_ng_spesifik = 0;
        $ng_details = [];
        if ($request->has('ng_detail_type')) {
            foreach ($request->ng_detail_type as $idx => $type) {
                $q = (int)$request->ng_detail_qty[$idx];
                if ($q > 0) {
                    $total_ng_spesifik += $q;
                    $ng_details[] = ['type' => $type, 'qty' => $q];
                }
            }
        }

        $cleanPart = str_replace([' ', '-'], '', trim($p->material_code));

        DB::beginTransaction();
        try {
            if ((int)$request->qty_return_warehouse > 0) {
                $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
                if ($rmInfo) {
                    DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', (int)$request->qty_return_warehouse);
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $p->rm_stock_id, 'material_code' => $p->material_code,
                        'pcs_in' => (int)$request->qty_return_warehouse, 'source' => 'return', 
                        'no_produksi' => $p->no_produksi, 'created_at' => now()
                    ]);
                }
            }

            $partMaster = DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])->first();
            $target = ($partMaster && $partMaster->next_process) ? strtoupper($partMaster->next_process) : 'FG';
            $status_akhir = ($target == 'WELDING' || $qty_ok == 0) ? 'COMPLETED' : 'WAITING_QC';

            DB::table('produksi_batches')->where('id', $id)->update([
                'qty_hasil_ok'         => $qty_ok, 
                'qty_ng_material'      => 0, 
                'qty_ng_process'       => $total_ng_spesifik, 
                'qty_hasil_ng'         => $total_ng_spesifik,
                'qty_return_warehouse' => (int)$request->qty_return_warehouse,
                'keterangan'           => $request->keterangan,
                'status'               => $status_akhir, 
                'updated_at'           => now()
            ]);

            // ✨ TAMBAHKAN LOGIKA TRANSMIT DI SINI AGAR OTOMATIS KE WIP WELDING ✨
            if ($target == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $p->material_code)->increment('welding_stock', $qty_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert([
                    'part_no'      => $p->material_code,
                    'qty'          => $qty_ok,
                    'process_type' => 'WELDING',
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            } else {
                DB::table('finished_goods')->where('part_no', $p->material_code)->increment('stock', $qty_ok, ['updated_at' => now()]);
            }

            $this->syncToActual($id);

            DB::table('production_ng_logs')->where('no_produksi', $p->no_produksi)->delete();
            if (!empty($ng_details)) {
                $actual = DB::table('production_actuals')->where('part_no', $p->material_code)
                             ->whereDate('created_at', date('Y-m-d', strtotime($p->created_at)))->first();
                if ($actual) {
                    foreach ($ng_details as $detail) {
                        DB::table('production_ng_logs')->insert([
                            'actual_id'   => $actual->id,
                            'no_produksi' => $p->no_produksi, 
                            'ng_type'     => $detail['type'],
                            'qty'         => $detail['qty'],
                            'created_at'  => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data Berhasil Disimpan & Transmitted!');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }

    private function syncToActual($batchId)
    {
        $batch = DB::table('produksi_batches')->where('id', $batchId)->first();
        if (!$batch) return;

        $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
        $dateOnly = date('Y-m-d', strtotime($batch->created_at));

        DB::table('production_actuals')->updateOrInsert(
            ['part_no' => $batch->material_code, 'line_code' => $lineCode, 'created_at' => $dateOnly],
            [
                'shift'      => $batch->shift,
                'qty_ok'     => $batch->qty_hasil_ok,
                'qty_ng'     => $batch->qty_hasil_ng,
                'updated_at' => now()
            ]
        );
    }

    public function getSpecsByCustomer($customer) {
        $specs = DB::table('rm_stocks')->where('customer', trim($customer))->where('stock_pcs', '>', 0)
            ->select(DB::raw('TRIM(spec) as spec'), 'size', DB::raw("REPLACE(size, ' ', '') as size_clean"))
            ->groupBy('spec', 'size', 'size_clean')->get();
        return response()->json($specs);
    }

    public function getPartsBySpec(Request $request) {
        $parts = DB::table('rm_stocks')->where('customer', trim($request->customer))
            ->where(DB::raw('TRIM(spec)'), trim($request->spec))
            ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $request->size)) 
            ->select('material_code', 'material_name')->distinct()->get();
        return response()->json($parts);
    }

    public function indexRM(Request $request) {
        $customer = trim($request->customer);
        $startDate = $request->start_date ?? date('Y-m-d'); $endDate = $request->end_date ?? date('Y-m-d');
        $materials = DB::table('rm_stocks')
            ->leftJoin('master_materials as mm', function($join) {
                $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))
                     ->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')"));
            })
            ->select('rm_stocks.*', 'mm.alias_code', 'mm.std_qty_batch')
            ->where('rm_stocks.stock_pcs', '>', 0)
            ->when($customer, fn($q)=>$q->where('rm_stocks.customer', $customer))->get();

        $groupedMaterials = $materials->groupBy(function($item) {
            return $item->alias_code ?? (trim($item->spec) . ' (' . str_replace(' ', '', $item->size) . ')');
        })->map(function($group) use ($startDate, $endDate) {
            $uniqueCoils = $group->unique('coil_id'); $ids = $group->pluck('id')->toArray();
            $totalLive = $uniqueCoils->sum('stock_pcs');
            $rep = $group->first();
            return (object)[
                'group_key' => $rep->alias_code ?? $rep->spec, 'total_live' => $totalLive, 'details' => $uniqueCoils,
                'combined_logs' => DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->get()->concat(DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->get())->sortByDesc('created_at')
            ];
        });
        $availableCustomers = DB::table('customers')->get();
        return view('Gudang.rm_store', compact('groupedMaterials', 'availableCustomers', 'customer', 'startDate', 'endDate'));
    }

    public function history() 
    {
        $history = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->select(
                'produksi_batches.no_produksi',
                'produksi_batches.material_code',
                'produksi_batches.shift',
                'produksi_batches.status',
                'produksi_batches.keterangan',
                DB::raw('MAX(produksi_batches.updated_at) as updated_at'), 
                DB::raw('SUM(produksi_batches.qty_hasil_ok) as qty_hasil_ok'),
                DB::raw('SUM(produksi_batches.qty_hasil_ng) as qty_hasil_ng'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs) as qty_ambil_pcs'),
                DB::raw('MIN(produksi_batches.id) as id'),
                DB::raw('GROUP_CONCAT(DISTINCT line.kode_Line SEPARATOR ", ") as line_names')
            )
            ->whereIn('produksi_batches.status', ['COMPLETED', 'WAITING_QC'])
            ->groupBy(
                'produksi_batches.no_produksi', 
                'produksi_batches.material_code', 
                'produksi_batches.shift',
                'produksi_batches.status',
                'produksi_batches.keterangan'
            )
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('Produksi.history', compact('history'));
    }

    public function returnToRM($id) {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
        DB::beginTransaction();
        try {
            if ($p && $rmInfo) { DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $p->qty_ambil_pcs); }
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete();
            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.');
        } catch (\Exception $e) { DB::rollback(); return back(); }
    }

    public function getBundlesByPart($material_code) {
        $current = DB::table('rm_stocks')->where('material_code', $material_code)->first();
        if ($current) {
            $bundles = DB::table('rm_stocks')
                ->where(DB::raw('TRIM(spec)'), trim($current->spec))
                ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $current->size)) 
                ->where('customer', trim($current->customer))
                ->where('stock_pcs', '>', 0)
                ->select(DB::raw('MIN(id) as id'), 'coil_id', 'stock_pcs', 'size')
                ->groupBy('coil_id', 'stock_pcs', 'size')->get();
            return response()->json($bundles);
        }
        return response()->json([]);
    }

    public function getPartDetail($id) {
        $rm = DB::table('rm_stocks')->where('id', $id)->first();
        if ($rm) {
            $std = ($rm->std_qty_batch > 0) ? $rm->std_qty_batch : 300;
            return response()->json(['material_code' => $rm->material_code, 'sisa_jalan' => floor($rm->stock_pcs / $std), 'stock_pcs' => $rm->stock_pcs, 'std_batch' => $std]);
        }
        return response()->json(['sisa_jalan' => 0, 'stock_pcs' => 0]);
    }

    public function resolveInterruption(Request $request, $id) { return $this->updateResult($request, $id); }
    public function gateConfirm(Request $request, $id) { return $this->updateResult($request, $id); }
    public function update(Request $request, $id) {
        DB::table('rm_stocks')->where('id', $id)->update(['coil_id' => $request->coil_id, 'stock_pcs' => $request->stock_pcs, 'updated_at' => now()]);
        return redirect()->back()->with('success', 'Update Berhasil!');
    }
    public function getBundles($code) { return $this->getBundlesByPart($code); }
    public function reportProblem(Request $request, $id)
    {
        DB::table('produksi_batches')->where('id', $id)->update([
            'status' => 'PROBLEM',
            'keterangan' => '⚠️ DIES RUSAK: ' . $request->problem_note,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('error', 'Laporan kendala telah dikirim ke PPIC!');
    }
}