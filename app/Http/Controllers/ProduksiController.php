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
                'rm_stocks.coil_id',
                'rm_stocks.customer',
                'rm_stocks.size',
                'rm_stocks.spec',
                'rm_stocks.material_name',
                DB::raw('GROUP_CONCAT(line.kode_Line SEPARATOR ", ") as line_names'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs) as total_qty_batch'),
                DB::raw('MIN(produksi_batches.id) as batch_id')
            )
            ->where(function($q) {
                $q->where('produksi_batches.status', 'PROSES')
                  ->orWhere('produksi_batches.qty_return', '>', 0);
            })
            ->groupBy(
                'no_produksi', 'shift', 'material_code', 'status', 'qty_return',
                'created_at', 'coil_id', 'customer', 'size', 'spec', 'material_name'
            );

        if ($customerFilter) {
            $query->where('rm_stocks.customer', trim($customerFilter));
        }

        $activeProductions = $query->orderBy('batch_id', 'desc')->get();
        
        // ✨ FIX: Ambil material unik per Coil ID rill
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

            // Ambil info coil dulu rill
            $rmInfo = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
            if(!$rmInfo) throw new \Exception("Material Unit not found!");

            DB::table('produksi_batches')->insert([
                'no_produksi'   => $no_produksi,
                'mesin_id'      => $request->mesin_id,
                'rm_stock_id'   => $request->rm_stock_id,
                'material_code' => $request->material_code,
                'shift'         => $request->shift,
                'qty_ambil_pcs' => $request->qty_ambil_pcs,
                'status'        => 'PROSES',
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
            
            // ✨ FIX: Potong stok untuk SEMUA part yang nempel di Coil yang sama rill
            DB::table('rm_stocks')
                ->where('coil_id', trim($rmInfo->coil_id))
                ->decrement('stock_pcs', $request->qty_ambil_pcs);

            DB::table('rm_production_logs')->insert([
                'rm_stock_id'   => $request->rm_stock_id,
                'material_code' => $request->material_code,
                'pcs_used'      => $request->qty_ambil_pcs, 
                'no_produksi'   => $no_produksi,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Batch Produksi Dimulai!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Start: ' . $e->getMessage());
        }
    }

    // ... (fungsi storeResult tetap sama)
    public function storeResult(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('produksi_batches')->where('id', $id)->first();
            DB::table('produksi_batches')->where('id', $id)->update(['qty_hasil_ok' => $request->qty_ok, 'qty_hasil_ng' => $request->qty_ng, 'status' => 'COMPLETED', 'updated_at' => now()]);
            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();
            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('welding_stock', $request->qty_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert(['part_no' => $batch->material_code, 'qty' => $request->qty_ok, 'process_type' => 'WELDING', 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('actual_stock', $request->qty_ok, ['updated_at' => now()]);
            }
            DB::commit(); return back()->with('success', 'Data Transmitted!');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); }
    }

    public function updateResult(Request $request, $id)
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        if (!$p) return redirect()->back()->with('error', 'Batch tidak ditemukan!');

        $qty_ok_new = (int)$request->qty_hasil_ok;
        $total_ng_spesifik = 0;
        $ng_details = [];
        if ($request->has('ng_detail_type')) {
            foreach ($request->ng_detail_type as $idx => $type) {
                $q = (int)$request->ng_detail_qty[$idx];
                if ($q > 0) { $total_ng_spesifik += $q; $ng_details[] = ['type' => $type, 'qty' => $q]; }
            }
        }

        DB::beginTransaction();
        try {
            if ((int)$request->qty_return_warehouse > 0) {
                $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
                if ($rmInfo) {
                    // ✨ FIX: Kembalikan stok ke SEMUA part di coil tersebut rill
                    DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', (int)$request->qty_return_warehouse);
                    DB::table('rm_incoming_logs')->insert(['rm_stock_id' => $p->rm_stock_id, 'material_code' => $p->material_code, 'pcs_in' => (int)$request->qty_return_warehouse, 'source' => 'return', 'no_produksi' => $p->no_produksi, 'created_at' => now()]);
                    
                    $currentLog = DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->first();
                    if($currentLog) { DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->update(['pcs_used' => ($currentLog->pcs_used - (int)$request->qty_return_warehouse)]); }
                }
            }

            $cleanPart = str_replace([' ', '-'], '', trim($p->material_code));
            $partMaster = DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])->first();
            $target = ($partMaster && $partMaster->next_process) ? strtoupper($partMaster->next_process) : 'FG';
            $status_akhir = $request->status ?? (($target == 'WELDING' || ($p->qty_hasil_ok + $qty_ok_new) == 0) ? 'COMPLETED' : 'WAITING_QC');

            DB::table('produksi_batches')->where('id', $id)->update([
                'qty_hasil_ok' => $p->qty_hasil_ok + $qty_ok_new,
                'qty_ng_process' => $p->qty_ng_process + $total_ng_spesifik,
                'qty_hasil_ng' => $p->qty_hasil_ng + $total_ng_spesifik,
                'qty_return_warehouse' => $p->qty_return_warehouse + (int)$request->qty_return_warehouse,
                'qty_return' => 0, 
                'keterangan' => $request->keterangan,
                'status' => $status_akhir,
                'updated_at' => now()
            ]);

            if ($qty_ok_new > 0) {
                DB::table('production_logs')->insert(['part_no' => $p->material_code, 'qty' => $qty_ok_new, 'process_type' => ($target == 'WELDING') ? 'WELDING' : 'FG', 'created_at' => now(), 'updated_at' => now()]);
            }

            if ($target == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $p->material_code)->increment('welding_stock', $qty_ok_new, ['updated_at' => now()]);
            } else if($status_akhir == 'COMPLETED') {
                DB::table('finished_goods')->where('part_no', $p->material_code)->increment('actual_stock', $qty_ok_new, ['updated_at' => now()]);
            }

            $this->syncToActual($id);
            // ... (log ng details tetap sama)
            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Update Success!');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }

    // ... (fungsi syncToActual, getBatchDeepDive, history tetap sama)

    /**
     * ✨ FIX: DROPDOWN NO. 07 (PHYSICAL COIL) BIAR GAK DOBEL RILL
     */
    public function getBundlesByPart($material_code) {
        $current = DB::table('rm_stocks')->where('material_code', trim($material_code))->first();
        if ($current) { 
            // Kita group by coil_id agar yang muncul cuma satu per nama coil rill
            $bundles = DB::table('rm_stocks')
                ->where('customer', trim($current->customer))
                ->where(DB::raw('TRIM(spec)'), trim($current->spec))
                ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $current->size))
                ->where('stock_pcs', '>', 0)
                ->select(
                    'coil_id', 
                    DB::raw('MAX(id) as id'), 
                    DB::raw('MAX(stock_pcs) as stock_pcs'), 
                    'size'
                )
                ->groupBy('coil_id', 'size')
                ->get(); 
            return response()->json($bundles); 
        }
        return response()->json([]);
    }

    public function returnToRM($id) {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
        DB::beginTransaction();
        try { 
            if ($p && $rmInfo) { 
                // ✨ FIX: Kembalikan stok ke seluruh mapping coil rill
                DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $p->qty_ambil_pcs); 
                DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->delete(); 
            } 
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete(); 
            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.'); 
        } catch (\Exception $e) { DB::rollback(); return back(); }
    }

    // ... (fungsi sisanya tetap sama)
}