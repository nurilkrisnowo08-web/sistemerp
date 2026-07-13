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
        
        // ✨ FIX 1: Gunakan MAX(stock_pcs) agar tidak dobel jumlah stoknya saat inisialisasi rill
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
            $requestedQty = (int)$request->qty_ambil_pcs;

            if ($request->rm_stock_id === 'AUTO') {
                // 1. Dapatkan referensi spesifikasi dasar berdasarkan material_code yang dipilih rill
                $baseMaterial = DB::table('rm_stocks')->where('material_code', trim($request->material_code))->first();
                if (!$baseMaterial) throw new \Exception("Material Reference not found!");

                // 2. Ambil semua coil yang cocok dengan spec tersebut dan masih ada isinya, urutkan dari ID terkecil (FIFO)
                $availableCoils = DB::table('rm_stocks')
                    ->where('customer', trim($baseMaterial->customer))
                    ->where(DB::raw('TRIM(spec)'), trim($baseMaterial->spec))
                    ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $baseMaterial->size))
                    ->where('stock_pcs', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                // 3. Hitung total seluruh stok gabungan coil yang tersedia
                $totalAvailableStock = $availableCoils->sum('stock_pcs');
                if ($totalAvailableStock < $requestedQty) {
                    throw new \Exception("Total stok gabungan coil tidak mencukupi! Hanya tersedia: " . $totalAvailableStock . " PCS");
                }

                $qtyRemainingToDeduct = $requestedQty;
                $firstRmStockId = null;

                // 4. Lakukan pemotongan secara berantai/gabungan ke coil-coil yang ada
                foreach ($availableCoils as $coil) {
                    if ($qtyRemainingToDeduct <= 0) break;

                    if ($firstRmStockId === null) {
                        $firstRmStockId = $coil->id; // Ambil ID pertama untuk acuan master di tabel produksi_batches
                    }

                    $currentStock = (int)$coil->stock_pcs;
                    $deduction = min($qtyRemainingToDeduct, $currentStock);

                    // Potong stok untuk baris yang punya Coil ID yang sama rill
                    DB::table('rm_stocks')->where('coil_id', trim($coil->coil_id))->decrement('stock_pcs', $deduction);

                    // Catat riwayat log penggunaan material per objek coil terkait
                    DB::table('rm_production_logs')->insert([
                        'rm_stock_id'   => $coil->id,
                        'material_code' => $request->material_code,
                        'pcs_used'      => $deduction, 
                        'no_produksi'   => $no_produksi,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);

                    $qtyRemainingToDeduct -= $deduction;
                }

                // 5. Insert master produksi utama menggunakan id coil utama/pertama yang terpangkas stoknya
                DB::table('produksi_batches')->insert([
                    'no_produksi'   => $no_produksi,
                    'mesin_id'      => $request->mesin_id,
                    'rm_stock_id'   => $firstRmStockId,
                    'material_code' => $request->material_code,
                    'shift'         => $request->shift,
                    'qty_ambil_pcs' => $requestedQty,
                    'status'        => 'PROSES',
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);

            } else {
                // Logika Standar Lama Jika Memilih Coil Satuan Tertentu Secara Manual rill
                $rmInfo = DB::table('rm_stocks')->where('id', $request->rm_stock_id)->first();
                if(!$rmInfo) throw new \Exception("Material Unit not found!");
                
                if ((int)$rmInfo->stock_pcs < $requestedQty) {
                    throw new \Exception("Stok coil " . $rmInfo->coil_id . " tidak cukup! Tersisa: " . $rmInfo->stock_pcs . " PCS");
                }

                DB::table('produksi_batches')->insert([
                    'no_produksi'   => $no_produksi,
                    'mesin_id'      => $request->mesin_id,
                    'rm_stock_id'   => $request->rm_stock_id,
                    'material_code' => $request->material_code,
                    'shift'         => $request->shift,
                    'qty_ambil_pcs' => $requestedQty,
                    'status'        => 'PROSES',
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
                
                // Potong stok untuk SEMUA baris yang punya Coil ID yang sama rill
                DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->decrement('stock_pcs', $requestedQty);

                DB::table('rm_production_logs')->insert([
                    'rm_stock_id'   => $request->rm_stock_id,
                    'material_code' => $request->material_code,
                    'pcs_used'      => $requestedQty, 
                    'no_produksi'   => $no_produksi,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
            }

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
            DB::table('produksi_batches')->where('id', $id)->update(['qty_hasil_ok' => $request->qty_ok, 'qty_hasil_ng' => $request->qty_ng, 'status' => 'COMPLETED', 'updated_at' => now()]);
            
            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();
            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('welding_stock', $request->qty_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert(['part_no' => $batch->material_code, 'qty' => $request->qty_ok, 'process_type' => 'WELDING', 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('actual_stock', $request->qty_ok, ['updated_at' => now()]);
            }
            DB::commit(); return back()->with('success', 'Data Transmitted!');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', 'Transmission Failed: ' . $e->getMessage()); }
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
                    DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', (int)$request->qty_return_warehouse);
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $p->rm_stock_id, 'material_code' => $p->material_code,
                        'pcs_in' => (int)$request->qty_return_warehouse, 'source' => 'return',
                        'no_produksi' => $p->no_produksi, 'created_at' => now()
                    ]);

                    // ✨ FIX 2: HAPUS/KOMENTARI UPDATE pcs_used AGAR LOG "OUT" TETAP NETEP (GAK JADI 0) rill
                    /* $currentLog = DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->first();
                    if($currentLog) { DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->update(['pcs_used' => ($currentLog->pcs_used - (int)$request->qty_return_warehouse)]); }
                    */
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

            if (!empty($ng_details)) {
                $actual = DB::table('production_actuals')->where('part_no', $p->material_code)->whereDate('created_at', date('Y-m-d', strtotime($p->created_at)))->first();
                if ($actual) {
                    foreach ($ng_details as $detail) {
                        DB::table('production_ng_logs')->insert(['actual_id' => $actual->id, 'no_produksi' => $p->no_produksi, 'ng_type' => $detail['type'], 'qty' => $detail['qty'], 'created_at' => now()]);
                    }
                }
            }

            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Update Success!');
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
            ['shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'updated_at' => now()]
        );
    }

    public function getBatchDeepDive($no_produksi)
    {
        $batch = DB::table('produksi_batches')->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')->select('produksi_batches.*', 'line.kode_Line')->where('no_produksi', $no_produksi)->first();
        $defects = DB::table('production_ng_logs')->where('no_produksi', $no_produksi)->select('ng_type', DB::raw('SUM(qty) as total_qty'))->groupBy('ng_type')->get();
        return response()->json(['batch' => $batch, 'defects' => $defects, 'total_reject' => $defects->sum('total_qty')]);
    }

    public function history(Request $request) 
    {
        $startDate = $request->start_date ?? date('Y-m-d'); $endDate = $request->end_date ?? date('Y-m-d');
        $history = DB::table('produksi_batches')->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->select('produksi_batches.no_produksi','produksi_batches.material_code','produksi_batches.shift','produksi_batches.status','produksi_batches.keterangan',
                DB::raw('MIN(produksi_batches.created_at) as created_at'), DB::raw('MAX(produksi_batches.updated_at) as updated_at'), 
                DB::raw('SUM(produksi_batches.qty_hasil_ok) as qty_hasil_ok'), DB::raw('SUM(produksi_batches.qty_hasil_ng) as qty_hasil_ng'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs) as qty_ambil_pcs'), DB::raw('SUM(produksi_batches.qty_return_warehouse) as qty_return_warehouse'),
                DB::raw('MIN(produksi_batches.id) as id'), DB::raw('GROUP_CONCAT(DISTINCT line.kode_Line SEPARATOR ", ") as line_names'))
            ->whereIn('produksi_batches.status', ['COMPLETED', 'WAITING_QC'])->whereBetween('produksi_batches.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('produksi_batches.no_produksi', 'produksi_batches.material_code', 'produksi_batches.shift', 'produksi_batches.status', 'produksi_batches.keterangan')
            ->orderBy('created_at', 'desc')->get();
        return view('Produksi.history', compact('history', 'startDate', 'endDate'));
    }

    public function getSpecsByCustomer($customer) {
        $specs = DB::table('rm_stocks')->where('customer', trim($customer))->where('stock_pcs', '>', 0)->select(DB::raw('TRIM(spec) as spec'), 'size', DB::raw("REPLACE(size, ' ', '') as size_clean"))->groupBy('spec', 'size', 'size_clean')->get();
        return response()->json($specs);
    }

    public function getPartsBySpec(Request $request) {
        $parts = DB::table('rm_stocks')->where('customer', trim($request->customer))->where(DB::raw('TRIM(spec)'), trim($request->spec))->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $request->size)) ->select('material_code', 'material_name')->distinct()->get();
        return response()->json($parts);
    }

    /**
     * ✨ FIX 3: DROPDOWN NO. 07 (PHYSICAL COIL) DIBUAT UNIK RILL
     */
    public function getBundlesByPart($material_code) {
        $current = DB::table('rm_stocks')->where('material_code', trim($material_code))->first();
        if ($current) { 
            $bundles = DB::table('rm_stocks')
                ->where('customer', trim($current->customer))
                ->where(DB::raw('TRIM(spec)'), trim($current->spec))
                ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $current->size))
                ->where('stock_pcs', '>', 0)
                ->select('coil_id', DB::raw('MAX(id) as id'), DB::raw('MAX(stock_pcs) as stock_pcs'), 'size')
                ->groupBy('coil_id', 'size') // ✨ Grouping berdasarkan coil_id agar tidak dobel rill
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
                DB::table('rm_stocks')->where('coil_id', trim($rmInfo->coil_id))->increment('stock_pcs', $p->qty_ambil_pcs); 
                DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->delete(); 
            } 
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete(); 
            DB::commit(); return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.'); 
        } catch (\Exception $e) { DB::rollback(); return back(); }
    }

    public function getPartDetail($id) {
        $rm = DB::table('rm_stocks')->where('id', $id)->first();
        if ($rm) { $std = ($rm->std_qty_batch > 0) ? $rm->std_qty_batch : 300; return response()->json(['material_code' => $rm->material_code, 'sisa_jalan' => floor($rm->stock_pcs / $std), 'stock_pcs' => $rm->stock_pcs, 'std_batch' => $std]); }
        return response()->json(['sisa_jalan' => 0, 'stock_pcs' => 0]);
    }

    public function resolveInterruption(Request $request, $id) { return $this->updateResult($request, $id); }
    public function gateConfirm(Request $request, $id) { return $this->updateResult($request, $id); }
    public function reportProblem(Request $request, $id) { DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROBLEM', 'keterangan' => '⚠️ DIES RUSAK: ' . $request->problem_note, 'updated_at' => now()]); return redirect()->back()->with('error', 'Laporan kendala telah dikirim!'); }
}