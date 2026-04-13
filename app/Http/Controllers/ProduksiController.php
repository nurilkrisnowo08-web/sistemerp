<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request)
    {
        $rm = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
        $totalPcs = (int)$request->qty_ambil_pcs;
        $lineIds = $request->line_ids;

        if (!$rm || !$lineIds) return redirect()->back()->with('error', 'Pilih Line dulu!');

        DB::beginTransaction();
        try {
            DB::table('rm_stocks')->where('coil_id', trim($rm->coil_id))->decrement('stock_pcs', $totalPcs);
            
            $noProduksi = $request->no_produksi;

            DB::table('rm_production_logs')->insert([
                'rm_stock_id' => $rm->id, 'material_code' => $request->material_code, 
                'pcs_used' => $totalPcs, 'no_produksi' => $noProduksi, 'created_at' => now()
            ]);

            $numLines = count($lineIds);
            $splitQty = floor($totalPcs / $numLines);
            $remainder = $totalPcs % $numLines;

            foreach ($lineIds as $index => $lineId) {
                $currentQty = ($index === 0) ? ($splitQty + $remainder) : $splitQty;
                
                DB::table('produksi_batches')->insert([
                    'no_produksi' => $noProduksi, 'shift' => $request->shift, 
                    'mesin_id' => $lineId, 'material_code' => $request->material_code, 
                    'rm_stock_id' => $rm->id, 'qty_ambil_pcs' => $currentQty, 
                    'status' => 'PROSES', 'created_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Batch dideploy ke ' . $numLines . ' Line.');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }

    /**
     * ✨ FIX ROUTING LOGIC: Biar IN di Welding Terminal Gak Nol rill!
     */
    public function updateResult(Request $request, $id) 
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        if (!$p) return redirect()->back()->with('error', 'Batch tidak ditemukan!');

        $qty_return = (int)$request->qty_return_warehouse;
        $qty_ok = (int)$request->qty_hasil_ok; 
        $cleanPart = str_replace([' ', '-'], '', trim($p->material_code));

        DB::beginTransaction();
        try {
            // 1. Logic Return Material
            if ($qty_return > 0) {
                $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
                if ($rmInfo) {
                    DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $qty_return);
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $p->rm_stock_id, 'material_code' => $p->material_code,
                        'pcs_in' => $qty_return, 'source' => 'return', 
                        'no_produksi' => $p->no_produksi, 'created_at' => now()
                    ]);
                }
            }

            // 2. Routing Logic (FG vs WELDING)
            if ($qty_ok > 0) {
                $partMaster = DB::table('parts')
                    ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                    ->first();
                
                $target = ($partMaster && $partMaster->next_process) ? strtoupper($partMaster->next_process) : 'FG';

                if ($target == 'WELDING') {
                    // Update Stok WIP Welding
                    $affected = DB::table('finished_goods')
                        ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                        ->increment('welding_stock', $qty_ok, ['updated_at' => now()]);
                    
                    if ($affected === 0) { throw new \Exception("Gagal! Part [ $cleanPart ] tidak terdaftar di database FG rill!"); }

                    // ✨ FIX IN: Catat Log Produksi agar angka IN di Terminal Welding muncul!
                    DB::table('production_logs')->insert([
                        'part_no'    => $p->material_code,
                        'qty'        => $qty_ok,
                        'created_at' => now(),
                    ]);
                    
                    $routeMsg = "Barang ditransfer ke GUDANG WELDING.";
                } else {
                    // Update Stok FG Final
                    $affected = DB::table('finished_goods')
                        ->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])
                        ->increment('actual_stock', $qty_ok, ['updated_at' => now()]);
                    
                    if ($affected > 0) {
                        DB::table('production_logs')->insert([
                            'part_no'    => $p->material_code,
                            'qty'        => $qty_ok,
                            'created_at' => now(),
                        ]);
                        $routeMsg = "Stok FG berhasil bertambah.";
                    } else { throw new \Exception("Gagal! Part [ $cleanPart ] tidak ditemukan di Master FG."); }
                }
            }

            // 3. Selesaikan status batch
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->update([
                'qty_hasil_ok'         => $qty_ok, 
                'qty_ng_material'      => $request->qty_ng_material ?? 0,
                'qty_ng_process'       => $request->qty_ng_process ?? 0,
                'qty_return_warehouse' => $qty_return,
                'status'               => 'COMPLETED', 
                'updated_at'           => now()
            ]);

            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Batch Selesai rill. ' . ($routeMsg ?? ''));

        } catch (\Exception $e) { 
            DB::rollback(); 
            return back()->with('error', 'Gagal Transmisi! Pesan: ' . $e->getMessage()); 
        }
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
                'produksi_batches.updated_at',
                'produksi_batches.qty_hasil_ok',
                'produksi_batches.qty_ng_material',
                'produksi_batches.qty_ng_process',
                'produksi_batches.qty_return_warehouse',
                'produksi_batches.status',
                DB::raw('MIN(produksi_batches.id) as id'),
                DB::raw('GROUP_CONCAT(line.kode_Line SEPARATOR ", ") as line_names'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs) as qty_ambil_pcs') 
            )
            ->where('produksi_batches.status', 'COMPLETED')
            ->groupBy(
                'no_produksi', 'material_code', 'shift', 'updated_at', 
                'qty_hasil_ok', 'qty_ng_material', 'qty_ng_process', 'qty_return_warehouse', 'status'
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
            if ($p && $rmInfo) {
                DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $p->qty_ambil_pcs);
            }
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete();
            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.');
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
}