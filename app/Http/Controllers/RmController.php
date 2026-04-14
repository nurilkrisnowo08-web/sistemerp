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
            ->select('rm_stocks.*', 'customers.code as customer_code', 'mm.alias_code', 'mm.std_qty_batch')
            ->where('rm_stocks.stock_pcs', '>', 0);

        if ($aliasSearch) { $rmQuery->where('mm.alias_code', 'LIKE', '%' . $aliasSearch . '%'); }
        if ($customer) { $rmQuery->where('rm_stocks.customer', $customer); }
        if ($specFilter) { $rmQuery->where('rm_stocks.spec', $specFilter); }
        
        $rawMaterials = $rmQuery->get();

        $groupedMaterials = $rawMaterials->groupBy(function($item) {
            return trim($item->spec) . ' | ' . str_replace(' ', '', $item->size);
        })->map(function($itemsInGroup) use ($startDate, $endDate) {
            
            $uniqueCoils = $itemsInGroup->unique('coil_id');
            $allIdsInGroup = $itemsInGroup->pluck('id')->toArray(); 

            $logsIn = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $allIdsInGroup)->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get();
            $logsOut = DB::table('rm_production_logs')->whereIn('rm_stock_id', $allIdsInGroup)->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->get();

            $totalLive = $uniqueCoils->sum('stock_pcs'); 
            $inS = $logsIn->whereIn('source', ['supplier', null])->sum('pcs_in');
            $inR = $logsIn->where('source', 'return')->sum('pcs_in');
            $outT = $logsOut->sum('pcs_used');

            $inSinceStart = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $allIdsInGroup)->where('created_at', '>=', $startDate.' 00:00:00')->sum('pcs_in');
            $outSinceStart = DB::table('rm_production_logs')->whereIn('rm_stock_id', $allIdsInGroup)->where('created_at', '>=', $startDate.' 00:00:00')->sum('pcs_used');
            $totalInit = $totalLive - $inSinceStart + $outSinceStart;

            $rep = $itemsInGroup->first();
            return (object)[
                'group_key' => trim($rep->spec) . ' (' . str_replace(' ', '', $rep->size) . ')',
                'alias_code' => $rep->alias_code, 'spec' => $rep->spec, 'size' => $rep->size,
                'std_qty_batch' => $rep->std_qty_batch, 'total_live' => $totalLive, 'total_init' => $totalInit,
                'total_in_s' => $inS, 'total_in_r' => $inR, 'total_out' => $outT,
                'details' => $uniqueCoils, 'all_parts' => $itemsInGroup,
                'combined_logs' => $logsIn->concat($logsOut)->sortByDesc('created_at')
            ];
        });

        $availableSpecs = DB::table('rm_stocks')->where('stock_pcs', '>', 0)->distinct()->pluck('spec');
        return view('Gudang.rm_store', compact('groupedMaterials', 'availableCustomers', 'customer', 'startDate', 'endDate', 'availableSpecs', 'specFilter'));
    }

    public function storeBatch(Request $request)
    {
        $request->validate(['customer_code' => 'required', 'spec' => 'required', 'size' => 'required', 'coil_id' => 'required', 'stock_pcs' => 'required|numeric', 'min_stock' => 'required|numeric', 'max_stock' => 'required|numeric', 'std_qty_batch' => 'required|numeric', 'part_nos' => 'required|array']);
        DB::beginTransaction();
        try {
            foreach ($request->part_nos as $partNo) {
                // ✨ FIX: Cari Nama Material di tabel PARTS rill!
                $pData = DB::table('parts')->where('part_no', $partNo)->first();
                $rmId = DB::table('rm_stocks')->insertGetId([
                    'material_code' => $partNo,
                    'coil_id' => strtoupper(trim($request->coil_id)),
                    'material_name' => $pData->part_name ?? 'N/A', // ✨ Biar Label gak N/A lagi rill!
                    'spec' => trim($request->spec), 'size' => trim($request->size),
                    'customer' => $request->customer_code, 'stock_pcs' => $request->stock_pcs,
                    'min_stock' => $request->min_stock, 'max_stock' => $request->max_stock,
                    'std_qty_batch' => $request->std_qty_batch,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('rm_incoming_logs')->insert(['rm_stock_id' => $rmId, 'material_code' => $partNo, 'pcs_in' => $request->stock_pcs, 'source' => 'supplier', 'no_produksi' => 'REG-' . date('Ymd'), 'created_at' => now()]);
            }
            DB::commit(); return redirect()->back()->with('success', 'Coil Registered rill!');
        } catch (\Exception $e) { DB::rollback(); return redirect()->back()->with('error', $e->getMessage()); }
    }

   public function getPartsAndSpecs($c)
    {
        // 1. Ambil Spek dari master_materials rill
        // ✨ KUNCINYA: Kita kasih alias 'material_type as material_name' 
        // supaya Javascript lu dapet data buat ditampilin di dropdown rill!
        $specs = DB::table('master_materials')
            ->where('customer_code', trim($c))
            ->select(
                'material_type', 
                'thickness', 
                'size', 
                'alias_code',
                'material_type as material_name' // Biar dropdown Speck gak kosong rill
            )
            ->get();

        // 2. Ambil Daftar Part dari tabel 'parts' rill
        // Ini yang buat box abu-abu (Mapped Parts) biar isinya muncul rill
        $parts = DB::table('parts')
            ->where('customer_code', trim($c))
            ->select('part_no', 'part_name')
            ->get();

        return response()->json([
            'parts' => $parts, 
            'specs' => $specs
        ]);
    }

    public function assignPart(Request $request) {
        $source = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
        if(!$source) return back()->with('error', 'Source not found.');
        $part = DB::table('parts')->where('part_no', $request->part_no)->first();
        $targetCoils = DB::table('rm_stocks')->where('customer', trim($source->customer))->where(DB::raw('TRIM(spec)'), trim($source->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $source->size))->distinct()->pluck('coil_id');
        DB::beginTransaction();
        try {
            foreach($targetCoils as $coilId) {
                $exists = DB::table('rm_stocks')->where('coil_id', $coilId)->where('material_code', $request->part_no)->exists();
                if(!$exists) {
                    $ref = DB::table('rm_stocks')->where('coil_id', $coilId)->first();
                    DB::table('rm_stocks')->insert(['material_code' => $request->part_no, 'material_name' => $part->part_name ?? 'N/A', 'customer' => $source->customer, 'spec' => $source->spec, 'size' => $source->size, 'coil_id' => $coilId, 'stock_pcs' => $ref->stock_pcs, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            DB::commit(); return back()->with('success', 'Sync success.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); }
    }

    public function removePartFromUnit($id) {
        $target = DB::table('rm_stocks')->where('id', $id)->first();
        if(!$target) return back()->with('error', 'Not found.');
        DB::table('rm_stocks')->where('customer', trim($target->customer))->where(DB::raw('TRIM(spec)'), trim($target->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $target->size))->where('material_code', $target->material_code)->delete();
        return back()->with('success', 'Removed.');
    }

    public function recapLogPrint(Request $request) {
        $availableCustomers = DB::table('customers')->get(); $availableSpecs = DB::table('rm_stocks')->distinct()->pluck('spec');
        $customer = $request->customer; $specFilter = $request->spec; $startDate = $request->start_date ?? date('Y-m-d'); $endDate = $request->end_date ?? date('Y-m-d');
        $inQuery = DB::table('rm_incoming_logs')->leftJoin('supplier_pos', 'rm_incoming_logs.po_id', '=', 'supplier_pos.id')->leftJoin('rm_stocks', 'rm_incoming_logs.rm_stock_id', '=', 'rm_stocks.id')->select('rm_incoming_logs.*', 'supplier_pos.no_po_supplier as po_identitas', 'rm_stocks.spec')->whereBetween('rm_incoming_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($customer) { $inQuery->where('rm_stocks.customer', $customer); } if ($specFilter) { $inQuery->where('rm_stocks.spec', $specFilter); } $logIn = $inQuery->orderBy('created_at', 'desc')->get();
        $outQuery = DB::table('rm_production_logs')->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')->select('rm_production_logs.*', 'rm_stocks.spec')->whereBetween('rm_production_logs.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($customer) { $outQuery->where('rm_stocks.customer', $customer); } if ($specFilter) { $outQuery->where('rm_stocks.spec', $specFilter); } $logOut = $outQuery->orderBy('created_at', 'desc')->get();
        return view('Gudang.rm_log_print', compact('logIn', 'logOut', 'availableCustomers', 'availableSpecs', 'customer', 'specFilter', 'startDate', 'endDate'));
    }

    public function recapPrint(Request $request) {
        $targetDate = $request->date ?? date('Y-m-d'); $customer = $request->customer;
        $startDaily = $targetDate . ' 00:00:00'; $endDaily = $targetDate . ' 23:59:59'; $startMonth = date('Y-m-01', strtotime($targetDate)) . ' 00:00:00';
        $title = "INVENTORY RECAP REPORT: " . date('d F Y', strtotime($targetDate));
        $query = DB::table('rm_stocks')->leftJoin('master_materials as mm', function($join) { $join->on(DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))->on(DB::raw("REPLACE(rm_stocks.size, ' ', '')"), '=', DB::raw("REPLACE(CONCAT(mm.thickness, 'X', mm.size), ' ', '')")); })->select('mm.alias_code', 'rm_stocks.spec', 'rm_stocks.size', DB::raw('SUM(rm_stocks.stock_pcs) as total_live_now'), DB::raw('GROUP_CONCAT(rm_stocks.id) as consolidated_ids'));
        if ($customer) { $query->where('rm_stocks.customer', $customer); }
        $data = $query->groupBy('mm.alias_code', 'rm_stocks.spec', 'rm_stocks.size')->get()->map(function($group) use ($startDaily, $endDaily, $startMonth) {
            $ids = explode(',', $group->consolidated_ids);
            $group->daily_in_s = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->whereIn('source', ['supplier', null])->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_in') ?? 0;
            $group->daily_in_r = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('source', 'return')->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_in') ?? 0;
            $group->daily_out = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->whereBetween('created_at', [$startDaily, $endDaily])->sum('pcs_used') ?? 0;
            $group->monthly_in_s = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->whereIn('source', ['supplier', null])->whereBetween('created_at', [$startMonth, $endDaily])->sum('pcs_in') ?? 0;
            $group->monthly_in_r = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('source', 'return')->whereBetween('created_at', [$startMonth, $endDaily])->sum('pcs_in') ?? 0;
            $group->monthly_out = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->whereBetween('created_at', [$startMonth, $endDaily])->sum('pcs_used') ?? 0;
            $in_since_target = DB::table('rm_incoming_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>=', $startDaily)->sum('pcs_in') ?? 0;
            $out_since_target = DB::table('rm_production_logs')->whereIn('rm_stock_id', $ids)->where('created_at', '>=', $startDaily)->sum('pcs_used') ?? 0;
            $group->stok_awal = $group->total_live_now - $in_since_target + $out_since_target; $group->stok_akhir_hari_ini = $group->stok_awal + ($group->daily_in_s + $group->daily_in_r) - $group->daily_out;
            return $group;
        });
        return view('Gudang.rm_recap_print', compact('data', 'title', 'customer', 'targetDate'));
    }

public function poSupplierIndex(Request $request) 
{
    $selectedCustomer = $request->customer; 
    
    // ✨ FIX: Hanya ambil status PENDING dan PARTIAL saja. 
    // RECEIVED dan COMPLETED kita buang karena sudah masuk History.
    $posQuery = DB::table('supplier_pos')->whereIn('status', ['PENDING', 'PARTIAL']);

    // Logic Otomatis: Cari berdasarkan PT di Header atau Material di dalemnya
    if ($selectedCustomer && $selectedCustomer != 'ALL') { 
        $posQuery->where(function($q) use ($selectedCustomer) {
            $q->where('customer_code', trim($selectedCustomer))
              ->orWhereExists(function ($query) use ($selectedCustomer) {
                  $query->select(DB::raw(1))
                        ->from('supplier_po_items')
                        ->leftJoin('master_materials', 'supplier_po_items.material_code', '=', 'master_materials.alias_code')
                        ->whereColumn('supplier_po_items.supplier_po_id', 'supplier_pos.id')
                        ->where('master_materials.customer_code', trim($selectedCustomer));
              });
        });
    }

    $pos = $posQuery->orderBy('id', 'desc')->get();

    // Mapping Detail (Tetap aman sesuai struktur lu)
    foreach ($pos as $po) {
        $po->items = DB::table('supplier_po_items')
            ->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')
            ->select('supplier_po_items.*', 'mm.material_type as spec_real', 'mm.alias_code as alias_real', 'mm.thickness', 'mm.size', 'mm.customer_code as client_code')
            ->where('supplier_po_id', $po->id)
            ->get();

        foreach($po->items as $item) { 
            $item->target_parts = DB::table('rm_stocks')
                ->where('customer', trim($item->client_code))
                ->where('spec', trim($item->spec_real))
                ->where(DB::raw("REPLACE(size, ' ', '')"), '=', str_replace(' ', '', trim($item->thickness) . 'X' . trim($item->size)))
                ->select('material_code as part_no', 'material_name as part_name')
                ->distinct()
                ->get(); 
        }
    }

    $clients = DB::table('customers')->get(); 
    $masterMaterials = DB::table('master_materials')->get();
    
    return view('Gudang.po_supplier_index', compact('pos', 'clients', 'masterMaterials', 'selectedCustomer'));
}

 public function poArrivalStore(Request $request, $id) 
{
    // 1. Validasi Input
    $request->validate([
        'item_id' => 'required',
        'coil_id' => 'required',
        'qty_arrival' => 'required|numeric|min:1'
    ]);

    // 2. Ambil data item
    $item = DB::table('supplier_po_items')->where('id', $request->item_id)->first();

    if (!$item) {
        return back()->with('error', 'Item PO tidak ditemukan di database rill!');
    }

    // 3. Cek sisa kuota (Safety Check)
    $sisaBarang = $item->qty_order - $item->qty_received;
    if ($request->qty_arrival > $sisaBarang) {
        return back()->with('error', "Gagal! Input ({$request->qty_arrival}) melebihi sisa pesanan ({$sisaBarang}).");
    }

    DB::beginTransaction();
    try {
        // Ambil spek asli dari master material
        $m = DB::table('master_materials')->where('alias_code', $item->material_code)->first();
        if (!$m) { throw new \Exception("Master Material '{$item->material_code}' tidak ditemukan!"); }

        $specTarget = trim($m->material_type); 
        $sizeTarget = trim($m->thickness) . ' X ' . trim($m->size);
        
        // Cari apakah material ini sudah pernah ada di stok
        $previousMapping = DB::table('rm_stocks')
            ->where('spec', $specTarget)
            ->where(DB::raw("REPLACE(size, ' ', '')"), '=', str_replace(' ', '', $sizeTarget))
            ->where('customer', trim($m->customer_code))
            ->select('material_code', 'material_name')
            ->distinct()
            ->get();

        $logCreated = false;

        // Jika mapping kosong atau ada, tetep buat baris stok baru (sesuai logic lu rill)
        if ($previousMapping->isEmpty()) {
            $newId = DB::table('rm_stocks')->insertGetId([
                'material_code' => $m->alias_code, 
                'material_name' => $m->material_type, 
                'customer' => trim($m->customer_code) ?? '-', 
                'spec' => $specTarget, 
                'size' => $sizeTarget, 
                'coil_id' => strtoupper(trim($request->coil_id)), 
                'stock_pcs' => $request->qty_arrival, 
                'min_stock' => 500, 'max_stock' => 1000, 
                'created_at' => now(), 'updated_at' => now()
            ]);
            
            DB::table('rm_incoming_logs')->insert([
                'rm_stock_id' => $newId, 
                'material_code' => $m->alias_code, 
                'pcs_in' => $request->qty_arrival, 
                'source' => 'supplier', 
                'no_produksi' => strtoupper(trim($request->coil_id)), 
                'created_at' => now()
            ]);
        } else { 
            foreach ($previousMapping as $p) {
                $newId = DB::table('rm_stocks')->insertGetId([
                    'material_code' => $p->material_code, 
                    'material_name' => $p->material_name, 
                    'customer' => trim($m->customer_code) ?? '-', 
                    'spec' => $specTarget, 'size' => $sizeTarget, 
                    'coil_id' => strtoupper(trim($request->coil_id)), 
                    'stock_pcs' => $request->qty_arrival, 
                    'min_stock' => 500, 'max_stock' => 1000, 
                    'created_at' => now(), 'updated_at' => now()
                ]);
                
                if (!$logCreated) {
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $newId, 
                        'material_code' => $p->material_code, 
                        'pcs_in' => $request->qty_arrival, 
                        'source' => 'supplier', 
                        'no_produksi' => strtoupper(trim($request->coil_id)), 
                        'created_at' => now()
                    ]);
                    $logCreated = true;
                }
            } 
        }

        // ✨ 4. UPDATE QTY RECEIVED (Ini yang bikin Balance keganti)
        DB::table('supplier_po_items')->where('id', $request->item_id)->increment('qty_received', $request->qty_arrival);

        // ✨ 5. UPDATE STATUS PO (Header)
        // Pastiin nama kolomnya 'supplier_po_id' ya rill, sesuai database lu
        $totalItems = DB::table('supplier_po_items')->where('supplier_po_id', $id)->count(); 
        $doneItems = DB::table('supplier_po_items')->where('supplier_po_id', $id)->whereRaw('qty_received >= qty_order')->count();
        
        $status = ($doneItems == $totalItems) ? 'COMPLETED' : 'PARTIAL'; 
        DB::table('supplier_pos')->where('id', $id)->update(['status' => $status, 'updated_at' => now()]);

        DB::commit(); 
        return redirect()->back()->with('success', 'Data masuk berhasil disimpan rill!');

    } catch (\Exception $e) { 
        DB::rollback(); 
        // Kalau gagal, kita bakal tau erornya apa rill!
        return back()->with('error', 'Gagal Simpan: ' . $e->getMessage()); 
    }
}

/**
 * Update Data Coil/Unit RM
 */
public function updateUnit(Request $request, $id) 
{
    // ✨ Validasi biar aman rill
    $request->validate([
        'coil_id' => 'required',
        'stock_pcs' => 'required|numeric'
    ]);

    try {
        // Update data di tabel rm_stocks
        DB::table('rm_stocks')->where('id', $id)->update([
            'coil_id'   => strtoupper(trim($request->coil_id)),
            'stock_pcs' => $request->stock_pcs,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Unit Profile Updated rill!');
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
    }
}

    public function printPO($id) {
        $po = DB::table('supplier_pos')->where('id', $id)->first(); if (!$po) return "Not found.";
        $po->items = DB::table('supplier_po_items')->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')->select('supplier_po_items.*', 'mm.material_type', 'mm.thickness', 'mm.size', 'mm.customer_code as client_code')->where('supplier_po_id', $id)->get();
        foreach($po->items as $item) { $item->target_parts = DB::table('rm_stocks')->where('customer', trim($item->client_code))->where('spec', trim($item->material_type))->where(DB::raw("REPLACE(size, ' ', '')"), '=', str_replace(' ', '', trim($item->thickness) . 'X' . trim($item->size)))->select('material_code', 'material_name')->distinct()->get(); }
        return view('Gudang.print_po', compact('po'));
    }

   public function poSupplierStore(Request $request) 
{ 
    // Validasi Wajib ada customer_code rill!
    $request->validate([
        'po_no' => 'required|unique:supplier_pos,no_po_supplier', 
        'supplier_name' => 'required', 
        'customer_code' => 'required', // ✨ FIX: Wajib diisi
        'items' => 'required|array'
    ]); 

    DB::beginTransaction(); 
    try { 
        // 1. Simpan Header (Tabel supplier_pos)
        $poId = DB::table('supplier_pos')->insertGetId([
            'no_po_supplier' => strtoupper($request->po_no), 
            'supplier_name'  => strtoupper($request->supplier_name), 
            'customer_code'  => $request->customer_code, // ✨ FIX: Simpan kode PT-nya rill!
            'status'         => 'PENDING', 
            'created_at'     => now(), 
            'updated_at'     => now()
        ]); 

        // 2. Simpan Detail Item (Tabel supplier_po_items)
        foreach ($request->items as $item) { 
            // Pastikan data spec dan qty ada isinya
            if(!empty($item['spec']) && !empty($item['qty'])) {
                DB::table('supplier_po_items')->insert([
                    'supplier_po_id' => $poId, 
                    'material_code'  => $item['spec'], 
                    'qty_order'      => $item['qty'], 
                    'qty_received'   => 0, 
                    'created_at'     => now(), 
                    'updated_at'     => now()
                ]); 
            }
        } 

        DB::commit(); 
        return redirect()->back()->with('success', 'PO Initialized Successfully rill!'); 

    } catch (\Exception $e) { 
        DB::rollBack(); 
        return back()->with('error', 'Gagal Simpan: ' . $e->getMessage()); 
    } 
}

    /**
     * ✨ FIX: Fungsi Hapus (image_bf54dd) gue balikin rill!
     */
    public function destroy($id) { 
        DB::table('rm_stocks')->where('id', $id)->delete(); 
        return back()->with('success', 'Data successfully removed.'); 
    }

    public function storeMasterSpec(Request $request) { 
        DB::table('master_materials')->insert(['customer_code' => trim($request->customer_code), 'material_type' => trim($request->material_type), 'thickness' => trim($request->thickness), 'size' => trim($request->size), 'alias_code' => trim($request->alias_code), 'full_spec' => trim($request->material_type) . ' ' . trim($request->thickness) . ' X ' . trim($request->size), 'created_at' => now(), 'updated_at' => now()]); 
        return back()->with('success', 'Specification registered.'); 
    }
    /**
 * Menampilkan Riwayat PO yang sudah selesai (COMPLETED)
 */
public function poSupplierHistory(Request $request) 
{
    $selectedCustomer = $request->customer; 
    
    // Ambil data yang statusnya COMPLETED (Atau status final lainnya)
    $posQuery = DB::table('supplier_pos')->where('status', 'COMPLETED');

    // Filter per PT kalau ada yang dipilih
    if ($selectedCustomer && $selectedCustomer != 'ALL') { 
        $posQuery->where('customer_code', trim($selectedCustomer));
    }

    $pos = $posQuery->orderBy('updated_at', 'desc')->get();

    foreach ($pos as $po) {
        $po->items = DB::table('supplier_po_items')
            ->leftJoin('master_materials as mm', 'supplier_po_items.material_code', '=', 'mm.alias_code')
            ->select('supplier_po_items.*', 'mm.material_type as spec_real', 'mm.alias_code as alias_real', 'mm.thickness', 'mm.size')
            ->where('supplier_po_id', $po->id) // Sesuaikan nama kolom FK lu rill!
            ->get();
    }

    $clients = DB::table('customers')->get(); 
    
    // Pastiin lu punya file view: resources/views/Gudang/po_supplier_history.blade.php
    return view('Gudang.po_supplier_history', compact('pos', 'clients', 'selectedCustomer'));
}
}