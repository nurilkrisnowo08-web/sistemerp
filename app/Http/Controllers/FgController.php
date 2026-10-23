<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FgController extends Controller
{
    public function index(Request $request)
    {
        $availableCustomers = DB::table('customers')->get();
        $customer = $request->customer ? trim($request->customer) : null;
        $partFilter = $request->part_no ? trim($request->part_no) : null;
        $date = $request->date ?? date('Y-m-d');

        $parts = DB::table('parts')->get(); 
        $masterMaterials = DB::table('master_materials')->get(); 

        $delivDateCol = Schema::hasColumn('deliveries', 'delivery_date') ? 'delivery_date' : 'created_at';
        $delivQtyCol  = Schema::hasColumn('deliveries', 'qty_delivery') ? 'qty_delivery' : (Schema::hasColumn('deliveries', 'qty') ? 'qty' : 'qty_delivery');
        $delivCustCol = Schema::hasColumn('deliveries', 'customer_code') ? 'customer_code' : 'customer';

        $availableParts = collect();

        if ($customer) {
            $partsQuery = DB::table('finished_goods')->select('part_no', 'part_name')->orderBy('part_no', 'asc');
            if ($customer !== 'ALL') {
                $partsQuery->where('customer', $customer);
            }
            $availableParts = $partsQuery->get();

            $queryFG = DB::table('finished_goods');
            if ($customer !== 'ALL') {
                $queryFG->where(function($q) use ($customer) {
                    $q->where('customer', $customer)->orWhere('customer', 'LIKE', "%$customer%");
                });
            }
            if ($partFilter) {
                $queryFG->where('part_no', $partFilter);
            }
            $allFG = $queryFG->get();
            
            foreach ($allFG as $fg) {
                $cleanPart = trim($fg->part_no);

                $fg->in_stp = DB::table('production_logs')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->where('process_type', 'FG') 
                    ->whereDate('created_at', $date)
                    ->where('qty', '>', 0) 
                    ->sum('qty');

                $fg->out_delv = DB::table('deliveries')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->whereDate($delivDateCol, $date)
                    ->sum($delivQtyCol);

                $total_in_setelah = DB::table('production_logs')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->where('process_type', 'FG')
                    ->whereDate('created_at', '>', $date)
                    ->where('qty', '>', 0)
                    ->sum('qty');

                $total_out_setelah = DB::table('deliveries')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->whereDate($delivDateCol, '>', $date)
                    ->sum($delivQtyCol);

                $fg->stock_akhir = ($fg->actual_stock ?? 0) - $total_in_setelah + $total_out_setelah;
                $fg->stock_awal = $fg->stock_akhir - $fg->in_stp + $fg->out_delv;
                
                $fg->stock_day = ($fg->needs_per_day > 0) ? round($fg->stock_akhir / $fg->needs_per_day, 1) : 0;
            }

            $stockOutQuery = DB::table('deliveries')->whereDate($delivDateCol, $date);
            if ($customer !== 'ALL') {
                $stockOutQuery->where(function($q) use ($customer, $delivCustCol) {
                    $q->where($delivCustCol, $customer);
                    if (Schema::hasColumn('deliveries', 'customer')) {
                        $q->orWhere('customer', $customer);
                    }
                });
            }
            if ($partFilter) {
                $stockOutQuery->where('part_no', $partFilter);
            }
            $stockOut = $stockOutQuery->orderBy($delivDateCol, 'desc')->get();
            
            $stockInQuery = DB::table('production_logs')
                ->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
                ->where('production_logs.process_type', 'FG')
                ->where('production_logs.qty', '>', 0)
                ->whereDate('production_logs.created_at', $date);
            if ($customer !== 'ALL') {
                $stockInQuery->where('finished_goods.customer', $customer);
            }
            if ($partFilter) {
                $stockInQuery->where('production_logs.part_no', $partFilter);
            }
            $stockIn = $stockInQuery->select('production_logs.*')->orderBy('production_logs.created_at', 'desc')->get();
        } else {
            $allFG = collect(); 
            $stockOut = collect(); 
            $stockIn = collect();
        }
        
        $labels = $allFG->pluck('part_no')->toArray();
        $actStockData = $allFG->pluck('stock_akhir')->toArray();
        $minStockData = $allFG->pluck('min_stock_pcs')->toArray();

        return view('finished_goods.index', compact(
            'allFG', 'labels', 'actStockData', 'minStockData', 'stockOut', 
            'stockIn', 'date', 'availableCustomers', 'parts', 'masterMaterials', 
            'customer', 'availableParts', 'partFilter'
        ));
    }

    public function create() 
    { 
        $customers = DB::table('customers')->get(); 
        return view('finished_goods.create', compact('customers')); 
    }

    public function store(Request $request)
    {
        DB::table('finished_goods')->insert([
            'customer'       => $request->customer,
            'part_no'        => $request->part_no,
            'part_name'      => $request->part_name,
            'needs_per_day'  => $request->needs_per_day,
            'actual_stock'   => $request->actual_stock,
            'min_stock_pcs'  => $request->min_stock_pcs,
            'max_stock_pcs'  => $request->max_stock_pcs,
            'qty_per_pallet' => $request->qty_per_pallet ?? 0,
            'created_at'     => now(), 
            'updated_at'     => now(),
        ]);
        return redirect()->route('fg.index', ['customer' => $request->customer])->with('success', 'Part Berhasil Ditambah!');
    }

    public function edit($id) 
    { 
        $fg = DB::table('finished_goods')->where('id', $id)->first(); 
        $customers = DB::table('customers')->get(); 
        return view('finished_goods.edit', compact('fg', 'customers')); 
    }

    public function monthlyRecap(Request $request)
    {
        $customer = $request->customer ? trim($request->customer) : null;
        $partFilter = $request->part_no ? trim($request->part_no) : null;
        
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        
        $customers = DB::table('customers')->get();
        $availableParts = collect();

        $delivDateCol = Schema::hasColumn('deliveries', 'delivery_date') ? 'delivery_date' : 'created_at';
        $delivQtyCol  = Schema::hasColumn('deliveries', 'qty_delivery') ? 'qty_delivery' : (Schema::hasColumn('deliveries', 'qty') ? 'qty' : 'qty_delivery');
        $delivCustCol = Schema::hasColumn('deliveries', 'customer_code') ? 'customer_code' : 'customer';

        if ($customer) {
            $partsQuery = DB::table('finished_goods')->select('part_no', 'part_name')->orderBy('part_no', 'asc');
            if ($customer !== 'ALL') {
                $partsQuery->where('customer', $customer);
            }
            $availableParts = $partsQuery->get();

            $queryFG = DB::table('finished_goods');
            if ($customer !== 'ALL') {
                $queryFG->where('customer', $customer);
            }
            if ($partFilter) {
                $queryFG->where('part_no', $partFilter);
            }
            $recap = $queryFG->get();
            
            $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

            foreach ($recap as $fg) {
                $cleanPart = trim($fg->part_no);

                $fg->total_in = DB::table('production_logs')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->where('process_type', 'FG') 
                    ->where('qty', '>', 0)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->sum('qty');

                $fg->total_out = DB::table('deliveries')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->whereMonth($delivDateCol, $month)
                    ->whereYear($delivDateCol, $year)
                    ->sum($delivQtyCol);

                $future_in = DB::table('production_logs')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->where('process_type', 'FG')
                    ->where('qty', '>', 0)
                    ->whereDate('created_at', '>', $endOfMonth)
                    ->sum('qty');

                $future_out = DB::table('deliveries')
                    ->where(DB::raw('TRIM(part_no)'), $cleanPart)
                    ->whereDate($delivDateCol, '>', $endOfMonth)
                    ->sum($delivQtyCol);

                $fg->stock_akhir = ($fg->actual_stock ?? 0) - $future_in + $future_out;
                $fg->stock_awal = $fg->stock_akhir - $fg->total_in + $fg->total_out;
            }

            // 1. Ambil seluruh data IN
            $in_logsQuery = DB::table('production_logs')
                ->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
                ->where('production_logs.process_type', 'FG')
                ->where('production_logs.qty', '>', 0)
                ->whereMonth('production_logs.created_at', $month)
                ->whereYear('production_logs.created_at', $year);
            
            if ($customer !== 'ALL') {
                $in_logsQuery->where('finished_goods.customer', $customer);
            }
            if ($partFilter) {
                $in_logsQuery->where('production_logs.part_no', $partFilter);
            }
            
            // Logika Cerdas: Tarik production_logs lalu intip tabel produksi_batches
            $in_logs = $in_logsQuery->select('production_logs.*')->get()->map(function($item) {
                $doc = 'WOS-SYSTEM';

                // Jika tabel memiliki nomor WOS secara langsung
                if (!empty($item->no_wos)) $doc = $item->no_wos;
                elseif (!empty($item->wos_no)) $doc = $item->wos_no;
                elseif (!empty($item->no_produksi)) $doc = $item->no_produksi;
                else {
                    // Cari manual dari tabel produksi_batches berdasarkan material_code
                    if (Schema::hasTable('produksi_batches')) {
                        $fk_id = $item->produksi_id ?? $item->produksi_batch_id ?? $item->batch_id ?? $item->job_id ?? null;
                        
                        if ($fk_id) {
                            $batch = DB::table('produksi_batches')->where('id', $fk_id)->first();
                            if ($batch && !empty($batch->no_produksi)) {
                                $doc = $batch->no_produksi;
                            }
                        } else {
                            // Cocokkan berdasarkan part_no dan tanggal yang sama
                            $query = DB::table('produksi_batches')->where('material_code', $item->part_no);
                            if (Schema::hasColumn('produksi_batches', 'created_at')) {
                                $query->whereDate('created_at', date('Y-m-d', strtotime($item->created_at)));
                            }
                            $batch = $query->first();
                            
                            // Jika gagal menemukan di tanggal yang sama, ambil data batch yang terakhir diproduksi
                            if (!$batch) {
                                $batch = DB::table('produksi_batches')->where('material_code', $item->part_no)->orderBy('id', 'desc')->first();
                            }
                            if ($batch && !empty($batch->no_produksi)) {
                                $doc = $batch->no_produksi;
                            }
                        }
                    }
                }

                return (object)[
                    'tgl' => date('Y-m-d', strtotime($item->created_at)),
                    'jam' => $item->created_at,
                    'part_no' => $item->part_no,
                    'in_qty' => $item->qty,
                    'out_qty' => 0,
                    'no_dokumen' => $doc,
                    'type' => 'IN'
                ];
            });
            
            // 2. Ambil seluruh data OUT
            $dailyDetailsQuery = DB::table('deliveries')
                ->whereMonth($delivDateCol, $month)
                ->whereYear($delivDateCol, $year);
            
            if ($customer !== 'ALL') {
                $dailyDetailsQuery->where(function($q) use ($customer, $delivCustCol) {
                    $q->where($delivCustCol, $customer);
                    if (Schema::hasColumn('deliveries', 'customer')) {
                        $q->orWhere('customer', $customer);
                    }
                });
            }
            if ($partFilter) {
                $dailyDetailsQuery->where('part_no', $partFilter);
            }
            
            $out_logs = $dailyDetailsQuery->select('*')->get()->map(function($item) use ($delivDateCol, $delivQtyCol) {
                $doc = $item->no_surat_jalan ?? $item->no_sj ?? $item->sj_no ?? $item->delivery_no ?? $item->do_no ?? 'SJ-SYSTEM';
                return (object)[
                    'tgl' => date('Y-m-d', strtotime($item->{$delivDateCol})),
                    'jam' => $item->{$delivDateCol},
                    'part_no' => $item->part_no,
                    'in_qty' => 0,
                    'out_qty' => $item->{$delivQtyCol},
                    'no_dokumen' => $doc,
                    'type' => 'OUT'
                ];
            });

            // 3. Gabungkan dan Urutkan
            $dailyDetails = $in_logs->concat($out_logs)->sortByDesc('jam')->values();
            
        } else { 
            $recap = collect(); 
            $dailyDetails = collect(); 
        }

        return view('finished_goods.monthly_recap', compact('recap', 'dailyDetails', 'customers', 'availableParts', 'partFilter', 'customer', 'month', 'year'));
    }

    public function printRecap(Request $request)
    {
        return $this->monthlyRecap($request); 
    }

    public function update(Request $request, $id)
    {
        DB::table('finished_goods')->where('id', $id)->update([
            'part_no'        => $request->part_no, 
            'part_name'      => $request->part_name, 
            'needs_per_day'  => $request->needs_per_day,
            'actual_stock'   => $request->actual_stock, 
            'min_stock_pcs'  => $request->min_stock_pcs, 
            'max_stock_pcs'  => $request->max_stock_pcs, 
            'updated_at'     => now(),
        ]);
        return redirect()->route('fg.index', ['customer' => $request->customer])->with('success', 'Data Berhasil Diupdate!');
    }

    public function destroyLog($id)
    {
        DB::beginTransaction();
        try {
            $log = DB::table('production_logs')->where('id', $id)->first();
            if ($log) {
                if ($log->process_type == 'FG') {
                    DB::table('finished_goods')->where('part_no', $log->part_no)->decrement('actual_stock', $log->qty);
                }
                DB::table('production_logs')->where('id', $id)->delete();
            }
            DB::commit(); 
            return redirect()->back()->with('success', 'Log berhasil dihapus!');
        } catch (\Exception $e) { 
            DB::rollBack(); 
            return redirect()->back()->with('error', 'Gagal!'); 
        }
    }

    public function getParts($customer) 
    { 
        $parts = DB::table('parts')->where('customer_code', $customer)->select('part_no', 'part_name')->get(); 
        return response()->json($parts); 
    }

    public function destroy($id) 
    { 
        DB::table('finished_goods')->where('id', $id)->delete(); 
        return redirect()->back()->with('success', 'Data Part Berhasil Dihapus, Guru!'); 
    }

    public function history(Request $request)
    {
        $customers = DB::table('customers')->get();
        $customer = $request->customer;
        
        $query = DB::table('purchase_orders')->where('status', 'closed');
        if ($customer) { 
            $query->where('customer_code', $customer); 
        }
        
        $purchaseOrders = $query->orderBy('updated_at', 'desc')->get();
        return view('finished_goods.history', compact('purchaseOrders', 'customers'));
    }
}