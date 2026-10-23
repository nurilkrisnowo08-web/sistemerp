<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RmController extends Controller
{
    /**
     * 1. MONITORING STOK RM - DASHBOARD SINKRONISASI (GROUPED BY SURAT JALAN)
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
            ->select('rm_stocks.*', 'customers.code as customer_code', 'mm.alias_code', 'mm.std_qty_batch');

        if ($aliasSearch) { $rmQuery->where('mm.alias_code', 'LIKE', '%' . $aliasSearch . '%'); }
        if ($customer) { $rmQuery->where('rm_stocks.customer', $customer); }
        if ($specFilter) { $rmQuery->where('rm_stocks.spec', $specFilter); }
        
        $rawMaterials = $rmQuery->get();

        $groupedMaterials = $rawMaterials->groupBy(function($item) {
            return trim($item->customer) . ' | ' . trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($itemsInGroup) use ($startDate, $endDate) {
            
            $rep = $itemsInGroup->first();
            
            $allHistoricalIds = DB::table('rm_stocks')
                ->where('customer', $rep->customer)
                ->where('spec', $rep->spec)
                ->where('size', $rep->size)
                ->pluck('id')->toArray();

            // Log Inbound lengkap dengan Coil ID
            $logsInRaw = DB::table('rm_incoming_logs')
                ->leftJoin('rm_stocks', 'rm_incoming_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_incoming_logs.rm_stock_id', $allHistoricalIds)
                ->whereBetween('rm_incoming_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->select('rm_incoming_logs.*', 'rm_stocks.coil_id')
                ->get();

            // Log Outbound lengkap dengan Coil ID
            $logsOut = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_production_logs.rm_stock_id', $allHistoricalIds)
                ->whereBetween('rm_production_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->select('rm_production_logs.*', 'rm_stocks.coil_id')
                ->get();

            $totalLive = $itemsInGroup->unique('coil_id')->sum('stock_pcs'); 
            $inS = $logsInRaw->whereIn('source', ['supplier', null])->sum('pcs_in');
            $inR = $logsInRaw->where('source', 'return')->sum('pcs_in');
            $outT = $logsOut->sum('pcs_used');

            $futureIn = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $allHistoricalIds)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_in');
            $futureOut = DB::table('rm_production_logs')->whereIn('rm_stock_id', $allHistoricalIds)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_used');
            
            $stokAkhirPeriod = $totalLive - $futureIn + $futureOut;
            $totalInit = $stokAkhirPeriod - ($inS + $inR) + $outT;

            // Grouping log Inbound per Surat Jalan
            $groupedInboundLogs = $logsInRaw->groupBy(function($item) {
                return $item->no_produksi ?? 'TANPA-SJ';
            })->map(function($bundleGroup, $sjNumber) {
                return (object)[
                    'type'         => 'INBOUND_GROUP',
                    'no_sj'        => $sjNumber,
                    'source'       => $bundleGroup->first()->source ?? 'supplier',
                    'total_qty'    => $bundleGroup->sum('pcs_in'),
                    'bundle_count' => $bundleGroup->count(),
                    'bundles'      => $bundleGroup,
                    'created_at'   => $bundleGroup->first()->created_at
                ];
            });

            // Log Keluar Produksi mencatat Coil ID
            $formattedOutLogs = $logsOut->map(function($item) {
                return (object)[
                    'type'         => 'PROD_EXIT',
                    'no_sj'        => $item->no_produksi,
                    'coil_id'      => $item->coil_id,
                    'total_qty'    => $item->pcs_used,
                    'bundle_count' => 1,
                    'created_at'   => $item->created_at
                ];
            });

            $combinedMutations = $groupedInboundLogs->concat($formattedOutLogs)->sortByDesc('created_at')->values();

            return (object)[
                'group_key'     => trim($rep->spec) . ' (' . str_replace(' ', '', $rep->size) . ')',
                'alias_code'    => $rep->alias_code, 
                'spec'          => $rep->spec, 
                'size'          => $rep->size, 
                'customer'      => $rep->customer,
                'std_qty_batch' => $rep->std_qty_batch, 
                'total_live'    => $totalLive, 
                'total_init'    => $totalInit,
                'total_in_s'    => $inS, 
                'total_in_r'    => $inR, 
                'total_out'     => $outT,
                'details'       => $itemsInGroup->where('stock_pcs', '>', 0)->unique('coil_id'), 
                'all_parts'     => $itemsInGroup,
                'combined_logs' => $combinedMutations
            ];
        })->filter(fn($group) => $group->total_live > 0 || $group->combined_logs->count() > 0); 

        $availableSpecs = DB::table('rm_stocks')->distinct()->pluck('spec');
        return view('Gudang.rm_store', compact('groupedMaterials', 'availableCustomers', 'customer', 'startDate', 'endDate', 'availableSpecs', 'specFilter'));
    }

    /**
     * 2. REKAP LOG PRINT (AUDIT TRAIL GROUPED BY SURAT JALAN & COIL KELUAR)
     */
    public function recapLogPrint(Request $request) 
    {
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
            ->select('rm_stocks.*', 'mm.alias_code');

        if ($customer) { $materials->where('rm_stocks.customer', $customer); }
        if ($specFilter) { $materials->where('rm_stocks.spec', $specFilter); }

        $historyData = $materials->get()->groupBy(function($item) {
            return $item->customer . ' | ' . ($item->alias_code ?? 'NA') . ' | ' . trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($group) use ($startDate, $endDate) {
            
            $rep = $group->first();
            $ids = DB::table('rm_stocks')
                ->where('customer', $rep->customer)
                ->where('spec', $rep->spec)
                ->where('size', $rep->size)
                ->pluck('id')->toArray();
            
            $logsInRaw = DB::table('rm_incoming_logs')
                ->leftJoin('rm_stocks', 'rm_incoming_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_incoming_logs.rm_stock_id', $ids)
                ->whereBetween(DB::raw('DATE(rm_incoming_logs.created_at)'), [$startDate, $endDate])
                ->select('rm_incoming_logs.*', 'rm_stocks.coil_id')
                ->get();
            
            // Catat identitas coil pada log keluar
            $logsOut = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_production_logs.rm_stock_id', $ids)
                ->whereBetween(DB::raw('DATE(rm_production_logs.created_at)'), [$startDate, $endDate])
                ->select('rm_production_logs.*', 'rm_stocks.coil_id')
                ->get();

            $liveNow = $group->unique('coil_id')->sum('stock_pcs');

            $future_in = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_in');
            $future_out = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>', $endDate.' 23:59:59')->sum('pcs_used');

            $in_qty = $logsInRaw->sum('pcs_in');
            $out_qty = $logsOut->sum('pcs_used');

            $stockAkhirPeriod = $liveNow - $future_in + $future_out;
            $stockAwalPeriod = $stockAkhirPeriod - $in_qty + $out_qty;

            // Grouping log Inbound audit trail
            $auditTrailIn = $logsInRaw->groupBy(function($item) {
                return $item->no_produksi ?? '-';
            })->map(function($bundleGroup, $sjNo) {
                $bundles = $bundleGroup->map(fn($b) => (object)[
                    'coil_id' => $b->coil_id ?? 'COIL',
                    'qty'     => $b->pcs_in
                ]);

                return (object)[
                    'created_at'   => $bundleGroup->first()->created_at,
                    'type'         => 'SUPPLY_IN (SJ)',
                    'ref_id'       => $sjNo,
                    'qty'          => $bundleGroup->sum('pcs_in'),
                    'bundle_count' => $bundleGroup->count(),
                    'bundles'      => $bundles
                ];
            });

            // Grouping log Outbound audit trail menjabarkan coil yang keluar
            $auditTrailOut = $logsOut->groupBy(function($item) {
                return $item->no_produksi ?? 'PROD-OUT';
            })->map(function($bundleGroup, $wosNo) {
                $bundles = $bundleGroup->map(fn($b) => (object)[
                    'coil_id' => $b->coil_id ?? 'COIL',
                    'qty'     => $b->pcs_used
                ]);

                return (object)[
                    'created_at'   => $bundleGroup->first()->created_at,
                    'type'         => 'USAGE_OUT',
                    'ref_id'       => $wosNo,
                    'qty'          => -$bundleGroup->sum('pcs_used'),
                    'bundle_count' => $bundleGroup->count(),
                    'bundles'      => $bundles
                ];
            });

            return (object)[
                'alias'      => $rep->alias_code, 
                'spec'       => $rep->spec, 
                'size'       => $rep->size, 
                'initial'    => $stockAwalPeriod, 
                'in_qty'     => $in_qty, 
                'out_qty'    => $out_qty, 
                'final'      => $stockAkhirPeriod, 
                'logs'       => $auditTrailIn->concat($auditTrailOut)->sortByDesc('created_at')->values()
            ];
        })->filter(fn($item) => $item->final > 0 || $item->in_qty > 0 || $item->out_qty > 0); 

        return view('Gudang.rm_log_print', compact('historyData', 'availableCustomers', 'availableSpecs', 'customer', 'specFilter', 'startDate', 'endDate'));
    }

    /**
     * 3. STORE BATCH REGISTER COIL
     */
    public function storeBatch(Request $request)
    {
        $request->validate([
            'customer_code' => 'required', 'spec' => 'required', 'size' => 'required', 
            'coil_id' => 'required', 'stock_pcs' => 'required|numeric', 
            'min_stock' => 'required|numeric', 'max_stock' => 'required|numeric', 
            'std_qty_batch' => 'required|numeric', 'part_nos' => 'required|array'
        ]);
        
        $coilId = strtoupper(trim($request->coil_id));

        DB::beginTransaction();
        try {
            $logInserted = false; 
            foreach ($request->part_nos as $partNo) {
                $exists = DB::table('rm_stocks')->where('coil_id', $coilId)->where('material_code', trim($partNo))->exists();
                if($exists) continue; 

                $pData = DB::table('parts')->where('part_no', trim($partNo))->first();
                $autoCavity = (int) ($request->cavity ?? DB::table('rm_stocks')->where('material_code', trim($partNo))->where('cavity', '>', 0)->value('cavity') ?? 1);

                $insertData = [
                    'material_code' => trim($partNo),
                    'coil_id'       => $coilId,
                    'material_name' => $pData->part_name ?? 'N/A', 
                    'spec'          => trim($request->spec), 
                    'size'          => trim($request->size),
                    'customer'      => $request->customer_code, 
                    'stock_pcs'     => $request->stock_pcs,
                    'cavity'        => $autoCavity,
                    'min_stock'     => $request->min_stock, 
                    'max_stock'     => $request->max_stock, 
                    'std_qty_batch' => $request->std_qty_batch,
                    'created_at'    => now(), 
                    'updated_at'    => now(),
                ];

                if (Schema::hasColumn('rm_stocks', 'no_surat_jalan') && $request->filled('no_surat_jalan')) {
                    $insertData['no_surat_jalan'] = strtoupper(trim($request->no_surat_jalan));
                }

                $rmId = DB::table('rm_stocks')->insertGetId($insertData);

                if (!$logInserted) {
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id'   => $rmId, 
                        'material_code' => trim($partNo), 
                        'pcs_in'        => $request->stock_pcs, 
                        'source'        => 'supplier', 
                        'no_produksi'   => $request->filled('no_surat_jalan') ? strtoupper(trim($request->no_surat_jalan)) : ('REG-' . date('Ymd')), 
                        'created_at'    => now()
                    ]);
                    $logInserted = true;
                }
            }
            DB::commit(); 
            return redirect()->back()->with('success', 'Coil Registered Successfully!');
        } catch (\Exception $e) { 
            DB::rollback(); 
            return redirect()->back()->with('error', $e->getMessage()); 
        }
    }

    /**
     * 4. GET PARTS AND SPECS (AJAX)
     */
    public function getPartsAndSpecs($c)
    {
        $specs = DB::table('master_materials')->where('customer_code', trim($c))->select('material_type', 'thickness', 'size', 'alias_code', 'material_type as material_name')->get();
        $parts = DB::table('parts')->where('customer_code', trim($c))->select('part_no', 'part_name')->get();
        return response()->json(['parts' => $parts, 'specs' => $specs]);
    }

    /**
     * 5. GET AVAILABLE COILS (AJAX)
     */
    public function getAvailableCoils($part_no)
    {
        $coils = DB::table('rm_stocks')
            ->where('material_code', trim($part_no))
            ->where('stock_pcs', '>', 0)
            ->select(
                'coil_id', 
                DB::raw('MAX(id) as id'), 
                DB::raw('MAX(stock_pcs) as stock_pcs') 
            )
            ->groupBy('coil_id')
            ->get();

        return response()->json($coils);
    }

    /**
     * 6. ASSIGN PART MAPPING
     */
    public function assignPart(Request $request) 
    {
        $source = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
        if(!$source) return back()->with('error', 'Source not found.');
        
        $part = DB::table('parts')->where('part_no', $request->part_no)->first();
        $autoCavity = (int) ($request->cavity ?? DB::table('rm_stocks')->where('material_code', $request->part_no)->where('cavity', '>', 0)->value('cavity') ?? 1);

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
                    $insertData = [
                        'material_code' => $request->part_no, 
                        'material_name' => $part->part_name ?? 'N/A', 
                        'customer'      => $source->customer, 
                        'spec'          => $source->spec, 
                        'size'          => $source->size, 
                        'coil_id'       => $coilId, 
                        'stock_pcs'     => $ref->stock_pcs, 
                        'cavity'        => $autoCavity,
                        'min_stock'     => $ref->min_stock, 
                        'max_stock'     => $ref->max_stock, 
                        'created_at'    => now(), 
                        'updated_at'    => now()
                    ];

                    if (Schema::hasColumn('rm_stocks', 'no_surat_jalan') && isset($ref->no_surat_jalan)) {
                        $insertData['no_surat_jalan'] = $ref->no_surat_jalan;
                    }

                    DB::table('rm_stocks')->insert($insertData);
                }
            }
            DB::commit(); 
            return back()->with('success', 'Mapping Sync Success.');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    /**
     * 7. REMOVE PART FROM UNIT
     */
    public function removePartFromUnit($id) 
    {
        $target = DB::table('rm_stocks')->where('id', $id)->first();
        if(!$target) return back()->with('error', 'Not found.');
        DB::table('rm_stocks')->where('customer', trim($target->customer))->where(DB::raw('TRIM(spec)'), trim($target->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $target->size))->where('material_code', $target->material_code)->delete();
        return back()->with('success', 'Removed.');
    }

    /**
     * ✨ 8. RECAP PRINT (MENAMPILKAN RINCIAN COIL MASUK & KELUAR) ✨
     */
    /**
     * ✨ 8. RECAP PRINT (MENAMPILKAN RINCIAN COIL MASUK & KELUAR DENGAN FILTER PART) ✨
     */
    public function recapPrint(Request $request) 
    {
        $customer = $request->customer;
        $partSize = $request->part_size; // Menangkap request filter part/size
        $filterType = $request->filter_type ?? ($request->has('month') && !$request->has('date') ? 'monthly' : 'daily');

        if ($filterType === 'monthly') {
            $selectedMonth = $request->month ?? date('Y-m');
            $startDate = date('Y-m-01', strtotime($selectedMonth . '-01'));
            $endDate = date('Y-m-t', strtotime($selectedMonth . '-01'));
            $targetDate = $endDate;
            $periodTitle = "REKAP BULAN " . strtoupper(date('F Y', strtotime($startDate)));
        } else {
            $startDate = $request->start_date ?? $request->date ?? date('Y-m-d');
            $endDate = $request->end_date ?? $request->date ?? $startDate;
            $targetDate = $endDate;
            $selectedMonth = date('Y-m', strtotime($startDate));
            $periodTitle = ($startDate === $endDate) 
                ? "TANGGAL: " . date('d F Y', strtotime($startDate))
                : "PERIODE: " . date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate));
        }

        $startTimestamp = $startDate . ' 00:00:00';
        $endTimestamp = $endDate . ' 23:59:59';
        $currentMonthStart = date('Y-m-01 00:00:00', strtotime($endDate));

        $rmStocksQuery = DB::table('rm_stocks')
            ->leftJoin('master_materials as mm', function($join) { 
                $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))
                     ->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')")); 
            })
            ->select('rm_stocks.*', 'mm.alias_code');

        // Filter 1: Customer
        if ($customer && $customer !== 'ALL') {
            $rmStocksQuery->where('rm_stocks.customer', $customer);
        }
        
        // Filter 2: Part / Size
        if ($partSize && $partSize !== 'ALL') {
            $rmStocksQuery->where(function($q) use ($partSize) {
                $q->where('rm_stocks.spec', $partSize)
                  ->orWhere('rm_stocks.material_code', $partSize)
                  ->orWhere('mm.alias_code', $partSize)
                  ->orWhere(DB::raw("CONCAT(rm_stocks.spec, ' ', rm_stocks.size)"), 'LIKE', "%{$partSize}%");
            });
        }

        $allStocks = $rmStocksQuery->get();

        $data = $allStocks->groupBy(function($item) {
            return trim($item->customer) . ' | ' . ($item->alias_code ?? 'NA') . ' | ' . trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($group) use ($startTimestamp, $endTimestamp, $currentMonthStart) {
            $rep = $group->first();

            $historicalIds = DB::table('rm_stocks')
                ->where('customer', $rep->customer)
                ->where('spec', $rep->spec)
                ->where('size', $rep->size)
                ->pluck('id')
                ->toArray();

            $totalLiveNow = $group->unique('coil_id')->sum('stock_pcs');

            // 1. Ambil Inbound lengkap dengan identitas Coil ID
            $logsIn = DB::table('rm_incoming_logs')
                ->leftJoin('rm_stocks', 'rm_incoming_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_incoming_logs.rm_stock_id', $historicalIds)
                ->whereBetween('rm_incoming_logs.created_at', [$startTimestamp, $endTimestamp])
                ->select('rm_incoming_logs.*', 'rm_stocks.coil_id')
                ->get();

            // 2. Ambil Outbound produksi lengkap dengan identitas Coil ID
            $logsOut = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->whereIn('rm_production_logs.rm_stock_id', $historicalIds)
                ->whereBetween('rm_production_logs.created_at', [$startTimestamp, $endTimestamp])
                ->select('rm_production_logs.*', 'rm_stocks.coil_id', 'rm_production_logs.no_produksi')
                ->get();

            $inS = $logsIn->whereIn('source', ['supplier', null])->sum('pcs_in');
            $inR = $logsIn->where('source', 'return')->sum('pcs_in');
            $outProd = $logsOut->sum('pcs_used');

            // Rangkuman detail per coil keluar
            $coilsOut = $logsOut->groupBy(function($item) {
                return trim($item->coil_id ?? 'COIL-OUT');
            })->map(function($items, $cId) {
                return (object)[
                    'coil_id' => $cId,
                    'qty'     => $items->sum('pcs_used'),
                    'no_dokumen' => $items->pluck('no_produksi')->unique()->implode(', ')
                ];
            })->values();

            // Rangkuman detail per coil masuk dari supplier
            $coilsIn = $logsIn->whereIn('source', ['supplier', null])->groupBy(function($item) {
                return trim($item->coil_id ?? 'COIL-IN');
            })->map(function($items, $cId) {
                return (object)[
                    'coil_id' => $cId,
                    'qty'     => $items->sum('pcs_in'),
                    'no_dokumen' => $items->pluck('no_produksi')->unique()->implode(', ')
                ];
            })->values();

            // Transaksi SETELAH periode terpilih (Masa Depan)
            $futureIn = DB::table('rm_incoming_logs')
                ->whereIn('rm_stock_id', $historicalIds)
                ->where('created_at', '>', $endTimestamp)
                ->sum('pcs_in') ?? 0;

            $futureOut = DB::table('rm_production_logs')
                ->whereIn('rm_stock_id', $historicalIds)
                ->where('created_at', '>', $endTimestamp)
                ->sum('pcs_used') ?? 0;

            // 🔥 LOGIKA MATEMATIKA YANG DIBENARKAN 🔥
            // Stok Akhir = Live Sekarang - Masuk (Masa Depan) + Keluar (Masa Depan)
            $stokAkhir = $totalLiveNow - $futureIn + $futureOut;
            
            // Stok Awal = Stok Akhir - Masuk (Saat Ini) + Keluar (Saat Ini)
            $stokAwal = $stokAkhir - ($inS + $inR) + $outProd;

            // Akumulasi bulanan
            $monthlyInS = DB::table('rm_incoming_logs')
                ->whereIn('rm_stock_id', $historicalIds)
                ->whereIn('source', ['supplier', null])
                ->whereBetween('created_at', [$currentMonthStart, $endTimestamp])
                ->sum('pcs_in') ?? 0;

            $monthlyInR = DB::table('rm_incoming_logs')
                ->whereIn('rm_stock_id', $historicalIds)
                ->where('source', 'return')
                ->whereBetween('created_at', [$currentMonthStart, $endTimestamp])
                ->sum('pcs_in') ?? 0;

            $monthlyOut = DB::table('rm_production_logs')
                ->whereIn('rm_stock_id', $historicalIds)
                ->whereBetween('created_at', [$currentMonthStart, $endTimestamp])
                ->sum('pcs_used') ?? 0;

            return (object)[
                'customer'            => $rep->customer,
                'alias_code'          => $rep->alias_code,
                'spec'                => $rep->spec,
                'size'                => $rep->size,
                'stok_awal'           => (int) $stokAwal,
                'daily_in_s'          => (int) $inS,
                'daily_in_r'          => (int) $inR,
                'daily_out'           => (int) $outProd,
                'coils_in'            => $coilsIn,
                'coils_out'           => $coilsOut,
                'monthly_in_s'        => (int) $monthlyInS,
                'monthly_in_r'        => (int) $monthlyInR,
                'monthly_out'         => (int) $monthlyOut,
                'stok_akhir_hari_ini' => (int) $stokAkhir,
            ];
        })->filter(fn($item) => $item->stok_akhir_hari_ini > 0 || $item->stok_awal > 0 || $item->daily_in_s > 0 || $item->daily_out > 0)->values();

        $availableCustomers = DB::table('customers')->get();
        
        // Mengirimkan list part untuk mengisi dropdown filter di Blade
        $availableParts = DB::table('rm_stocks')
            ->select(DB::raw("CONCAT(spec, ' ', size) as part_name"), 'spec', 'size')
            ->when($customer && $customer !== 'ALL', function($q) use ($customer) {
                return $q->where('customer', $customer);
            })
            ->distinct()
            ->get();

        return view('Gudang.rm_recap_print', compact(
            'data', 'customer', 'partSize', 'targetDate', 'startDate', 'endDate', 
            'filterType', 'selectedMonth', 'periodTitle', 'availableCustomers', 'availableParts'
        ));
    }

    /**
     * 9. PO SUPPLIER INDEX (OPERASIONAL AKTIF)
     */
    public function poSupplierIndex(Request $request) 
    { 
        $selectedCustomer = $request->customer; 
        $posQuery = DB::table('supplier_pos')->whereIn('status', ['PENDING', 'PARTIAL']);
        if ($selectedCustomer && $selectedCustomer != 'ALL') { $posQuery->where('customer_code', trim($selectedCustomer)); }
        $pos = $posQuery->orderBy('id', 'desc')->get();
        foreach ($pos as $po) {
            $po->items = DB::table('supplier_po_items')->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')->select('supplier_po_items.*', 'mm.material_type as spec_real', 'mm.customer_code as client_code', 'mm.thickness', 'mm.size')->where('supplier_po_id', $po->id)->get();
        }
        $clients = DB::table('customers')->get(); 
        return view('Gudang.po_supplier_index', compact('pos', 'clients', 'selectedCustomer'));
    }

    /**
     * 9B. PO SUPPLIER HISTORY (ARCHIVE VAULT - FIX ERROR 500 CALL TO UNDEFINED METHOD)
     */
    public function poSupplierHistory(Request $request)
    {
        $selectedCustomer = $request->customer ?? 'ALL';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $clients = DB::table('customers')->orderBy('name', 'asc')->get();

        $posQuery = DB::table('supplier_pos');

        if ($selectedCustomer && $selectedCustomer !== 'ALL') {
            $posQuery->where('customer_code', trim($selectedCustomer));
        }

        if ($startDate && $endDate) {
            $posQuery->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        } elseif ($startDate) {
            $posQuery->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $posQuery->whereDate('created_at', '<=', $endDate);
        }

        $pos = $posQuery->orderBy('updated_at', 'desc')->paginate(15);

        foreach ($pos as $po) {
            $po->items = DB::table('supplier_po_items')
                ->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')
                ->select(
                    'supplier_po_items.*', 
                    'mm.material_type', 
                    'mm.material_type as spec_real', 
                    'mm.customer_code as client_code', 
                    'mm.thickness', 
                    'mm.size'
                )
                ->where('supplier_po_id', $po->id)
                ->get();
        }

        return view('Gudang.po_supplier_history', compact('pos', 'clients', 'selectedCustomer', 'startDate', 'endDate'));
    }

    /**
     * 9C. PRINT PO SUPPLIER
     */
    public function printPo($id)
    {
        $po = DB::table('supplier_pos')->where('id', $id)->first();
        if (!$po) return redirect()->back()->with('error', 'PO Supplier tidak ditemukan.');

        $items = DB::table('supplier_po_items')
            ->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')
            ->select('supplier_po_items.*', 'mm.material_type', 'mm.thickness', 'mm.size')
            ->where('supplier_po_id', $id)
            ->get();

        return view('Gudang.print_po', compact('po', 'items'));
    }

    /**
     * 10. PO ARRIVAL STORE (AUTO-DETECT PART ASLI & CAVITY DARI RM_STOCKS + NO SURAT JALAN)
     */
    public function poArrivalStore(Request $request, $id) 
    { 
        DB::beginTransaction();
        try {
            $po = DB::table('supplier_pos')->where('id', $id)->first();
            if (!$po) throw new \Exception("PO Supplier tidak ditemukan!");

            $noSJ = strtoupper(trim($request->no_surat_jalan ?? $request->no_sj ?? 'SJ-' . date('YmdHis')));

            $bundles = [];
            if ($request->has('bundles') && is_array($request->bundles)) {
                $bundles = $request->bundles;
            } elseif ($request->filled('coil_id') && $request->filled('item_id')) {
                $bundles[] = [
                    'item_id'     => $request->item_id,
                    'coil_id'     => $request->coil_id,
                    'qty_arrival' => $request->qty_arrival ?? 0,
                    'part_no'     => $request->part_no ?? null,
                    'cavity'      => $request->cavity ?? null
                ];
            }

            if (empty($bundles)) {
                throw new \Exception("Data penerimaan bundle/coil kosong!");
            }

            foreach ($bundles as $b) {
                $coilId = strtoupper(trim($b['coil_id']));
                $qtyArrival = (int) ($b['qty_arrival'] ?? $b['qty_pcs'] ?? 0);
                $itemId = $b['item_id'] ?? null;

                if ($qtyArrival <= 0 || empty($coilId)) continue;

                $item = DB::table('supplier_po_items')->where('id', $itemId)->first();
                if (!$item) continue;

                $m = DB::table('master_materials')->where('alias_code', $item->material_code)->first();
                $spec = $m ? trim($m->material_type) : '-';
                $size = $m ? (trim($m->thickness) . ' X ' . trim($m->size)) : '-';
                $customerCode = $m ? trim($m->customer_code) : trim($po->customer_code);

                $knownMappings = DB::table('rm_stocks')
                    ->where('customer', $customerCode)
                    ->where(DB::raw('TRIM(spec)'), $spec)
                    ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $size))
                    ->where('material_code', 'NOT LIKE', 'SAI%')
                    ->where('cavity', '>', 0)
                    ->select('material_code', 'material_name', 'cavity')
                    ->distinct()
                    ->get();

                $targetParts = collect();

                if (!empty($b['part_no'])) {
                    $pData = DB::table('parts')->where('part_no', trim($b['part_no']))->first();
                    $cav = (int) ($b['cavity'] ?? DB::table('rm_stocks')->where('material_code', trim($b['part_no']))->where('cavity', '>', 0)->value('cavity') ?? 1);
                    $targetParts->push((object)[
                        'material_code' => trim($b['part_no']),
                        'material_name' => $pData->part_name ?? $m->material_type,
                        'cavity'        => $cav
                    ]);
                } elseif ($knownMappings->isNotEmpty()) {
                    $targetParts = $knownMappings;
                } else {
                    $targetParts->push((object)[
                        'material_code' => $item->material_code,
                        'material_name' => $m ? $m->material_type : $item->material_code,
                        'cavity'        => (int) ($b['cavity'] ?? 1)
                    ]);
                }

                $primaryRmId = null;
                $hasSjCol = Schema::hasColumn('rm_stocks', 'no_surat_jalan');

                foreach ($targetParts as $tPart) {
                    $existingStock = DB::table('rm_stocks')
                        ->where('coil_id', $coilId)
                        ->where('material_code', $tPart->material_code)
                        ->first();

                    if (!$existingStock) {
                        $insertData = [
                            'material_code' => $tPart->material_code,
                            'material_name' => $tPart->material_name, 
                            'customer'      => $customerCode, 
                            'spec'          => $spec, 
                            'size'          => $size, 
                            'coil_id'       => $coilId, 
                            'stock_pcs'     => $qtyArrival, 
                            'cavity'        => (int) $tPart->cavity,
                            'min_stock'     => 500, 
                            'max_stock'     => 1000, 
                            'created_at'    => now(), 
                            'updated_at'    => now()
                        ];

                        if ($hasSjCol) {
                            $insertData['no_surat_jalan'] = $noSJ;
                        }

                        $newId = DB::table('rm_stocks')->insertGetId($insertData);
                        if (!$primaryRmId) $primaryRmId = $newId;
                    } else {
                        $updateData = [
                            'stock_pcs'  => $existingStock->stock_pcs + $qtyArrival,
                            'cavity'     => (int) $tPart->cavity,
                            'updated_at' => now()
                        ];

                        if ($hasSjCol) {
                            $updateData['no_surat_jalan'] = $noSJ;
                        }

                        DB::table('rm_stocks')->where('id', $existingStock->id)->update($updateData);
                        if (!$primaryRmId) $primaryRmId = $existingStock->id;
                    }
                }

                if ($primaryRmId) {
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id'   => $primaryRmId, 
                        'material_code' => $targetParts->first()->material_code, 
                        'pcs_in'        => $qtyArrival, 
                        'source'        => 'supplier', 
                        'po_id'         => $id, 
                        'no_produksi'   => $noSJ, 
                        'created_at'    => now()
                    ]);
                }

                DB::table('supplier_po_items')->where('id', $itemId)->increment('qty_received', $qtyArrival);
            }

            $poItems = DB::table('supplier_po_items')->where('supplier_po_id', $id)->get();
            $totalOrder = $poItems->sum('qty_order');
            $totalReceived = $poItems->sum('qty_received');

            $newStatus = 'PENDING';
            if ($totalReceived >= $totalOrder && $totalOrder > 0) {
                $newStatus = 'COMPLETED';
            } elseif ($totalReceived > 0) {
                $newStatus = 'PARTIAL';
            }
            DB::table('supplier_pos')->where('id', $id)->update(['status' => $newStatus, 'updated_at' => now()]);

            DB::commit(); 
            return redirect()->back()->with('success', "Penerimaan Surat Jalan [{$noSJ}] Sukses!");
        } catch (\Exception $e) { 
            DB::rollback(); 
            return back()->with('error', $e->getMessage()); 
        }
    }

    /**
     * 11. PO SUPPLIER STORE
     */
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

    /**
     * 12. DESTROY RM STOCK
     */
    public function destroy($id) { DB::table('rm_stocks')->where('id', $id)->delete(); return back()->with('success', 'Removed.'); }

    /**
     * 13. STORE MASTER SPEC
     */
    public function storeMasterSpec(Request $request) { DB::table('master_materials')->insert(['customer_code' => trim($request->customer_code), 'material_type' => trim($request->material_type), 'thickness' => trim($request->thickness), 'size' => trim($request->size), 'alias_code' => trim($request->alias_code), 'full_spec' => trim($request->material_type) . ' ' . trim($request->thickness) . ' X ' . trim($request->size), 'created_at' => now(), 'updated_at' => now()]); return back()->with('success', 'Specification Registered.'); }

    /**
     * 14. UPDATE UNIT PCS
     */
    public function updateUnitPcs(Request $request) { DB::table('rm_stocks')->where('id', $request->id)->update(['stock_pcs' => $request->new_qty, 'updated_at' => now()]); return back()->with('success', 'Stock Adjusted.'); }
}