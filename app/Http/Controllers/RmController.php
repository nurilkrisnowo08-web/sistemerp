<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RmController extends Controller
{
    /**
     * 1. MONITORING STOK RM - DASHBOARD SINKRONISASI
     */
    public function storeIndex(Request $request)
    {
        $availableCustomers = DB::table('customers')->get();
        $customer = trim($request->customer);
        $specFilter = trim($request->spec);
        $aliasSearch = trim($request->alias); 
        
        $startDate = $request->start_date ?? date('Y-m-d'); 
        $endDate = $request->end_date ?? date('Y-m-d'); 

        $rmQuery = DB::table('rm_stocks')
            ->leftJoin('customers', 'rm_stocks.customer', '=', 'customers.code') 
            ->leftJoin('master_materials as mm', function($join) {
                $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))
                     ->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')"));
            })
            ->where('rm_stocks.stock_pcs', '>', 0)
            ->select('rm_stocks.*', 'customers.code as customer_code', 'mm.alias_code', 'mm.std_qty_batch');

        if ($aliasSearch) { $rmQuery->where('mm.alias_code', 'LIKE', '%' . $aliasSearch . '%'); }
        if ($customer) { $rmQuery->where('rm_stocks.customer', $customer); }
        if ($specFilter) { $rmQuery->where('rm_stocks.spec', $specFilter); }
        
        $rawMaterials = $rmQuery->get();

        $groupedMaterials = $rawMaterials->groupBy(function($item) {
            // ✨ FIX: Gunakan Customer + Spec + Size agar history tidak tercampur antar Client
            return trim($item->customer) . ' | ' . trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($itemsInGroup) use ($startDate, $endDate) {
            
            $rep = $itemsInGroup->first();
            
            // ✨ FIX: Filter history ID wajib menggunakan Customer agar tidak menarik log customer lain
            $allHistoricalIds = DB::table('rm_stocks')
                ->where('customer', $rep->customer)
                ->where('spec', $rep->spec)
                ->where('size', $rep->size)
                ->pluck('id')->toArray();

            $logsIn = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $allHistoricalIds)->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get();
            $logsOut = DB::table('rm_production_logs')->whereIn('rm_stock_id', $allHistoricalIds)->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get();

            // Hitung stok berdasarkan fisik Coil (Unique Coil ID)
            $totalLive = $itemsInGroup->unique('coil_id')->sum('stock_pcs'); 
            $inS = $logsIn->whereIn('source', ['supplier', null])->sum('pcs_in');
            $inR = $logsIn->where('source', 'return')->sum('pcs_in');
            $outT = $logsOut->sum('pcs_used');

            $futureIn = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $allHistoricalIds)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_in');
            $futureOut = DB::table('rm_production_logs')->whereIn('rm_stock_id', $allHistoricalIds)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_used');
            
            $stokAkhirPeriod = $totalLive + $futureOut - $futureIn;
            $totalInit = $stokAkhirPeriod - ($inS + $inR) + $outT;

            return (object)[
                'group_key' => trim($rep->spec) . ' (' . str_replace(' ', '', $rep->size) . ')',
                'alias_code' => $rep->alias_code, 'spec' => $rep->spec, 'size' => $rep->size, 'customer' => $rep->customer,
                'std_qty_batch' => $rep->std_qty_batch, 'total_live' => $totalLive, 'total_init' => $totalInit,
                'total_in_s' => $inS, 'total_in_r' => $inR, 'total_out' => $outT,
                'details' => $itemsInGroup->unique('coil_id'), 
                'all_parts' => $itemsInGroup,
                'combined_logs' => $logsIn->concat($logsOut)->sortByDesc('created_at')
            ];
        })->filter(fn($group) => $group->total_live > 0); 

        $availableSpecs = DB::table('rm_stocks')->distinct()->pluck('spec');
        return view('Gudang.rm_store', compact('groupedMaterials', 'availableCustomers', 'customer', 'startDate', 'endDate', 'availableSpecs', 'specFilter'));
    }

    public function recapLogPrint(Request $request) {
        $availableCustomers = DB::table('customers')->get(); 
        $availableSpecs = DB::table('rm_stocks')->distinct()->pluck('spec');
        
        $customer = $request->customer; 
        $specFilter = $request->spec; 
        $startDate = $request->start_date ?? date('Y-m-d'); 
        $endDate = $request->end_date ?? date('Y-m-d');

        $materials = DB::table('rm_stocks')
            ->leftJoin('master_materials as mm', function($join) {
                $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))
                     ->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')"));
            })
            ->where('rm_stocks.stock_pcs', '>', 0)
            ->select('rm_stocks.*', 'mm.alias_code');

        if ($customer) { $materials->where('rm_stocks.customer', $customer); }
        if ($specFilter) { $materials->where('rm_stocks.spec', $specFilter); }

        $historyData = $materials->get()->groupBy(function($item) {
            return $item->customer . ' | ' . ($item->alias_code ?? 'NA') . ' | ' . trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($group) use ($startDate, $endDate) {
            
            $rep = $group->first();
            $ids = DB::table('rm_stocks')
                ->where('customer', $rep->customer) // ✨ FIX: Filter customer
                ->where('spec', $rep->spec)
                ->where('size', $rep->size)
                ->pluck('id')->toArray();
            
            $in_qty = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('pcs_in');
            
            $out_qty = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('pcs_used');

            $liveNow = $group->unique('coil_id')->sum('stock_pcs');

            $future_in = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_in');
            $future_out = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_used');

            $stockAkhirPeriod = $liveNow - $future_in + $future_out;
            $stockAwalPeriod = $stockAkhirPeriod - $in_qty + $out_qty;

            return (object)[
                'alias' => $rep->alias_code, 'spec' => $rep->spec, 'size' => $rep->size,
                'initial' => $stockAwalPeriod, 'in_qty' => $in_qty, 'out_qty' => $out_qty, 'final' => $stockAkhirPeriod,
                'logs' => DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)
                    ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get()
                    ->concat(DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)
                    ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get())
                    ->sortByDesc('created_at')
            ];
        })->filter(fn($item) => $item->final > 0); 

        return view('Gudang.rm_log_print', compact('historyData', 'availableCustomers', 'availableSpecs', 'customer', 'specFilter', 'startDate', 'endDate'));
    }

    /**
     * 3. REGISTER UNIT (FIXED TRIMMING & DUPES)
     */
    public function storeBatch(Request $request)
    {
        $request->validate(['customer_code' => 'required', 'spec' => 'required', 'size' => 'required', 'coil_id' => 'required', 'stock_pcs' => 'required|numeric', 'min_stock' => 'required|numeric', 'max_stock' => 'required|numeric', 'std_qty_batch' => 'required|numeric', 'part_nos' => 'required|array']);
        
        $coilId = strtoupper(trim($request->coil_id));

        DB::beginTransaction();
        try {
            $logInserted = false; 
            foreach ($request->part_nos as $partNo) {
                $exists = DB::table('rm_stocks')->where('coil_id', $coilId)->where('material_code', trim($partNo))->exists();
                if($exists) continue; 

                $pData = DB::table('parts')->where('part_no', trim($partNo))->first();
                $rmId = DB::table('rm_stocks')->insertGetId([
                    'material_code' => trim($partNo),
                    'coil_id' => $coilId,
                    'material_name' => $pData->part_name ?? 'N/A', 
                    'spec' => trim($request->spec), 
                    'size' => trim($request->size),
                    'customer' => $request->customer_code, 
                    'stock_pcs' => $request->stock_pcs,
                    'min_stock' => $request->min_stock, 
                    'max_stock' => $request->max_stock,
                    'std_qty_batch' => $request->std_qty_batch,
                    'created_at' => now(), 
                    'updated_at' => now(),
                ]);

                if (!$logInserted) {
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $rmId, 
                        'material_code' => trim($partNo), 
                        'pcs_in' => $request->stock_pcs, 
                        'source' => 'supplier', 
                        'no_produksi' => 'REG-' . date('Ymd'), 
                        'created_at' => now()
                    ]);
                    $logInserted = true;
                }
            }
            DB::commit(); 
            return redirect()->back()->with('success', 'Coil Registered Successfully!');
        } catch (\Exception $e) { DB::rollback(); return redirect()->back()->with('error', $e->getMessage()); }
    }

    public function getPartsAndSpecs($c)
    {
        $specs = DB::table('master_materials')->where('customer_code', trim($c))->select('material_type', 'thickness', 'size', 'alias_code', 'material_type as material_name')->get();
        $parts = DB::table('parts')->where('customer_code', trim($c))->select('part_no', 'part_name')->get();
        return response()->json(['parts' => $parts, 'specs' => $specs]);
    }

    
    public function getAvailableCoils($part_no)
    {
        
        $coils = DB::table('rm_stocks')
            ->where('material_code', trim($part_no))
            ->where('stock_pcs', '>', 0)
            ->groupBy('coil_id', 'id', 'stock_pcs') // ✨ Kunci agar ID Coil unik
            ->select('id', 'coil_id', 'stock_pcs')
            ->get();

        return response()->json($coils);
    }

    public function assignPart(Request $request) {
        $source = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
        if(!$source) return back()->with('error', 'Source not found.');
        
        $part = DB::table('parts')->where('part_no', $request->part_no)->first();
        $targetCoils = DB::table('rm_stocks')
                        ->where('customer', trim($source->customer))
                        ->where(DB::raw('TRIM(spec)'), trim($source->spec))
                        ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $source->size))
                        ->distinct()
                        ->pluck('coil_id');
        
        DB::beginTransaction();
        try {
            foreach($targetCoils as $coilId) {
                $exists = DB::table('rm_stocks')->where('coil_id', $coilId)->where('material_code', $request->part_no)->exists();
                if(!$exists) {
                    $ref = DB::table('rm_stocks')->where('coil_id', $coilId)->first();
                    DB::table('rm_stocks')->insert([
                        'material_code' => $request->part_no, 
                        'material_name' => $part->part_name ?? 'N/A', 
                        'customer' => $source->customer, 
                        'spec' => $source->spec, 
                        'size' => $source->size, 
                        'coil_id' => $coilId, 
                        'stock_pcs' => $ref->stock_pcs, 
                        'min_stock' => $ref->min_stock,
                        'max_stock' => $ref->max_stock,
                        'created_at' => now(), 
                        'updated_at' => now()
                    ]);
                }
            }
            DB::commit(); return back()->with('success', 'Mapping Sync Success.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); }
    }

    public function removePartFromUnit($id) {
        $target = DB::table('rm_stocks')->where('id', $id)->first();
        if(!$target) return back()->with('error', 'Not found.');
        DB::table('rm_stocks')->where('customer', trim($target->customer))->where(DB::raw('TRIM(spec)'), trim($target->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $target->size))->where('material_code', $target->material_code)->delete();
        return back()->with('success', 'Removed.');
    }

    public function recapPrint(Request $request) {
        $targetDate = $request->date ?? date('Y-m-d'); 
        $customer = $request->customer;
        $startDaily = $targetDate . ' 00:00:00'; 
        $endDaily = $targetDate . ' 23:59:59'; 
        $startMonth = date('Y-m-01', strtotime($targetDate)) . ' 00:00:00';
        
        $query = DB::table('rm_stocks')->leftJoin('master_materials as mm', function($join) { 
            $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')")); 
        })->select('rm_stocks.customer', 'mm.alias_code', 'rm_stocks.spec', 'rm_stocks.size', DB::raw('SUM(rm_stocks.stock_pcs) as total_live_now'), DB::raw('GROUP_CONCAT(rm_stocks.id) as consolidated_ids'))
        ->where('rm_stocks.stock_pcs', '>', 0); 
        
        if ($customer) { $query->where('rm_stocks.customer', $customer); }
        
        $data = $query->groupBy('rm_stocks.customer', 'mm.alias_code', 'rm_stocks.spec', 'rm_stocks.size')->get()->map(function($group) use ($startDaily, $endDaily, $startMonth) {
            $ids = explode(',', $group->consolidated_ids);
            $group->daily_in_s = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->whereIn('source', ['supplier', null])->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_in') ?? 0;
            $group->daily_in_r = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('source', 'return')->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_in') ?? 0;
            $group->daily_out = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_used') ?? 0;
            
            $group->stok_awal = $group->total_live_now - (DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>=', $startDaily)->sum('pcs_in') ?? 0) + (DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>=', $startDaily)->sum('pcs_used') ?? 0); 
            $group->stok_akhir_hari_ini = $group->stok_awal + ($group->daily_in_s + $group->daily_in_r) - $group->daily_out;
            return $group;
        });
        return view('Gudang.rm_recap_print', compact('data', 'customer', 'targetDate'));
    }

    public function poSupplierIndex(Request $request) 
    {
        $selectedCustomer = $request->customer; 
        $posQuery = DB::table('supplier_pos')->whereIn('status', ['PENDING', 'PARTIAL']);
        if ($selectedCustomer && $selectedCustomer != 'ALL') { 
            $posQuery->where('customer_code', trim($selectedCustomer));
        }
        $pos = $posQuery->orderBy('id', 'desc')->get();
        foreach ($pos as $po) {
            $po->items = DB::table('supplier_po_items')->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')->select('supplier_po_items.*', 'mm.material_type as spec_real', 'mm.customer_code as client_code', 'mm.thickness', 'mm.size')->where('supplier_po_id', $po->id)->get();
        }
        $clients = DB::table('customers')->get(); 
        return view('Gudang.po_supplier_index', compact('pos', 'clients', 'selectedCustomer'));
    }

    public function poArrivalStore(Request $request, $id) 
    {
        $coilId = strtoupper(trim($request->coil_id));
        DB::beginTransaction();
        try {
            $item = DB::table('supplier_po_items')->where('id', $request->item_id)->first();
            $m = DB::table('master_materials')->where('alias_code', $item->material_code)->first();
            
            $exists = DB::table('rm_stocks')->where('coil_id', $coilId)->where('material_code', $m->alias_code)->exists();
            if(!$exists) {
                $newId = DB::table('rm_stocks')->insertGetId(['material_code' => $m->alias_code, 'material_name' => $m->material_type, 'customer' => trim($m->customer_code), 'spec' => trim($m->material_type), 'size' => trim($m->thickness).' X '.trim($m->size), 'coil_id' => $coilId, 'stock_pcs' => $request->qty_arrival, 'min_stock' => 500, 'max_stock' => 1000, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('rm_incoming_logs')->insert(['rm_stock_id' => $newId, 'material_code' => $m->alias_code, 'pcs_in' => $request->qty_arrival, 'source' => 'supplier', 'po_id' => $id, 'no_produksi' => $coilId, 'created_at' => now()]);
            }
            
            DB::table('supplier_po_items')->where('id', $request->item_id)->increment('qty_received', $request->qty_arrival);
            DB::commit(); return redirect()->back()->with('success', 'Inbound Processed Successfully!');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }

    public function poSupplierStore(Request $request) 
    { 
        $request->validate(['po_no' => 'required|unique:supplier_pos,no_po_supplier', 'supplier_name' => 'required', 'customer_code' => 'required', 'items' => 'required|array']); 
        DB::beginTransaction(); 
        try { 
            $poId = DB::table('supplier_pos')->insertGetId(['no_po_supplier' => strtoupper($request->po_no), 'supplier_name' => strtoupper($request->supplier_name), 'customer_code' => $request->customer_code, 'status' => 'PENDING', 'created_at' => now(), 'updated_at' => now()]); 
            foreach ($request->items as $item) { 
                if(!empty($item['spec']) && !empty($item['qty'])) {
                    DB::table('supplier_po_items')->insert(['supplier_po_id' => $poId, 'material_code' => $item['spec'], 'qty_order' => $item['qty'], 'qty_received' => 0, 'created_at' => now(), 'updated_at' => now()]); 
                }
            } 
            DB::commit(); return redirect()->back()->with('success', 'PO Initialized Successfully!'); 
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); } 
    }

    public function destroy($id) { DB::table('rm_stocks')->where('id', $id)->delete(); return back()->with('success', 'Removed.'); }
    public function storeMasterSpec(Request $request) { DB::table('master_materials')->insert(['customer_code' => trim($request->customer_code), 'material_type' => trim($request->material_type), 'thickness' => trim($request->thickness), 'size' => trim($request->size), 'alias_code' => trim($request->alias_code), 'full_spec' => trim($request->material_type) . ' ' . trim($request->thickness) . ' X ' . trim($request->size), 'created_at' => now(), 'updated_at' => now()]); return back()->with('success', 'Specification Registered.'); }
    public function updateUnitPcs(Request $request) { DB::table('rm_stocks')->where('id', $request->id)->update(['stock_pcs' => $request->new_qty, 'updated_at' => now()]); return back()->with('success', 'Stock Adjusted.'); }
}