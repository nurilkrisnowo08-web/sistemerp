<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProduksiController extends Controller
{
    /**
     * 1. STAMPING TERMINAL LINE CONTROL
     */
    public function index(Request $request)
    {
        $area = $request->area ?? 'all'; 
        $selectedShift = $request->shift ?? 'ALL';
        $customerFilter = $request->customer;
        $date = $request->date; 

        $query = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->leftJoin('production_plans', 'produksi_batches.plan_id', '=', 'production_plans.id')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->select(
                DB::raw('MIN(produksi_batches.id) as batch_id'), 
                'produksi_batches.no_produksi', 
                'produksi_batches.shift',
                'produksi_batches.material_code',
                'produksi_batches.keterangan', 
                'produksi_batches.status',
                'produksi_batches.qty_return', 
                'produksi_batches.created_at',
                'produksi_batches.cavity',
                'produksi_batches.qty_ambil_pcs', 
                'produksi_batches.qty_hasil_ok',
                'produksi_batches.qty_hasil_ng',
                'rm_stocks.coil_id',
                'rm_stocks.customer',
                'rm_stocks.size',
                'rm_stocks.spec',
                'rm_stocks.material_name',
                'parts.part_name',
                'production_plans.customer_code as plan_customer',
                'production_plans.plan_date', 
                DB::raw('COALESCE(line.kode_Line, "UNKNOWN") as kode_Line'),
                DB::raw('COALESCE(GROUP_CONCAT(line.kode_Line SEPARATOR ", "), line.kode_Line) as line_names'),
                DB::raw('SUM(produksi_batches.qty_ambil_pcs * produksi_batches.cavity) as total_qty_batch')
            )
            ->where(function($q) {
                $q->where('produksi_batches.status', 'PROSES')
                  ->orWhere('produksi_batches.status', 'PROBLEM')
                  ->orWhere('produksi_batches.qty_return', '>', 0);
            })
            ->where('produksi_batches.no_produksi', 'NOT LIKE', '%-S')
            ->groupBy(
                'produksi_batches.no_produksi', 
                'produksi_batches.shift', 
                'produksi_batches.material_code', 
                'produksi_batches.keterangan', 
                'produksi_batches.status', 
                'produksi_batches.qty_return',
                'produksi_batches.created_at', 
                'produksi_batches.cavity', 
                'produksi_batches.qty_ambil_pcs', 
                'produksi_batches.qty_hasil_ok',
                'produksi_batches.qty_hasil_ng',
                'rm_stocks.coil_id', 
                'rm_stocks.customer', 
                'rm_stocks.size', 
                'rm_stocks.spec', 
                'rm_stocks.material_name', 
                'parts.part_name',
                'production_plans.customer_code',
                'production_plans.plan_date',
                'line.kode_Line'
            );

        if ($area === 'small') {
            $query->where(function($q) {
                $q->where('line.kode_Line', 'LIKE', '%C%')
                  ->orWhere('line.kode_Line', 'LIKE', '%SMALL%')
                  ->orWhere('line.nama_Line', 'LIKE', '%SMALL%');
            });
        } elseif ($area === 'big') {
            $query->where(function($q) {
                $q->where('line.kode_Line', 'NOT LIKE', '%C%')
                  ->where('line.kode_Line', 'NOT LIKE', '%SMALL%');
            });
        }

        if ($selectedShift === 'S1') {
            $query->whereIn('produksi_batches.shift', ['Pagi', 'S1', 'Shift 1']);
        } elseif ($selectedShift === 'S2') {
            $query->whereIn('produksi_batches.shift', ['Malam', 'S2', 'Shift 2']);
        }

        if ($customerFilter && $customerFilter !== 'ALL') {
            $query->where('rm_stocks.customer', trim($customerFilter));
        }

        if ($date) {
            $query->whereDate('produksi_batches.created_at', $date);
        }

        $activeProductionsRaw = $query->orderBy('batch_id', 'asc')->get();
        
        $activeProductions = $activeProductionsRaw->map(function ($item) {
            $isMainPair = (strpos($item->keterangan, 'PAIR_MAIN:') === 0) || str_ends_with($item->no_produksi, '-M');
            $item->is_separating = $isMainPair;
            $item->paired_part = null;
            $item->paired_batch_id = null;

            if ($isMainPair) {
                $lastDash = strrpos($item->no_produksi, '-');
                $timePrefix = $lastDash !== false ? substr($item->no_produksi, 0, $lastDash) : $item->no_produksi;
                
                $pairedBatch = DB::table('produksi_batches')
                    ->where('no_produksi', $timePrefix . '-S')
                    ->first();

                if ($pairedBatch) {
                    $item->paired_part = $pairedBatch->material_code;
                    $item->paired_batch_id = $pairedBatch->id;
                } elseif (strpos($item->keterangan, 'PAIR_MAIN:') === 0) {
                    $item->paired_part = str_replace('PAIR_MAIN:', '', $item->keterangan);
                }
            }

            $item->display_part = $item->paired_part ? ($item->material_code . ' & ' . $item->paired_part) : $item->material_code;

            $lineCodeUpper = strtoupper($item->kode_Line ?? $item->line_names ?? '');
            if (str_contains($lineCodeUpper, 'C') || str_contains($lineCodeUpper, 'SMALL')) {
                $item->area_type = 'SMALL';
                $item->area_label = 'SMALL PRESS';
            } else {
                $item->area_type = 'BIG';
                $item->area_label = 'BIG PRESS';
            }

            $s = strtoupper(trim($item->shift ?? ''));
            if (str_contains($s, '2') || str_contains($s, 'MALAM')) {
                $item->shift_label = 'SHIFT 2 (MALAM)';
                $item->shift_code = 'S2';
            } else {
                $item->shift_label = 'SHIFT 1 (PAGI)';
                $item->shift_code = 'S1';
            }

            if (empty($item->plan_date)) {
                $item->plan_date = date('Y-m-d', strtotime($item->created_at));
            }

            return $item;
        });
        
        $groupedLines = $activeProductions->groupBy(function($item) {
            return $item->kode_Line ?? ($item->line_names ?? 'LINE-UNKNOWN');
        });

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

        return view('Produksi.index', compact('activeProductions', 'groupedLines', 'materials', 'customers', 'lines', 'area', 'selectedShift', 'customerFilter', 'date'));
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

            DB::table('rm_stocks')
                ->whereRaw("REPLACE(coil_id, ' ', '') = ?", [str_replace(' ', '', $rmInfo->coil_id)])
                ->decrement('stock_pcs', $request->qty_ambil_pcs);

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

    public function storeResult(Request $request, $id) { return back(); }

    public function updateResult(Request $request, $id)
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        if (!$p) return redirect()->back()->with('error', 'Batch tidak ditemukan!');

        DB::beginTransaction();
        try {
            $qty_ok_new = (int)$request->qty_hasil_ok; 
            $ng_parent_update = (int)$request->qty_hasil_ng; 

            $planDate = DB::table('production_plans')->where('id', $p->plan_id)->value('plan_date') ?? date('Y-m-d', strtotime($p->created_at));

            if ($request->has('qty_hasil_ok_parts') && is_array($request->qty_hasil_ok_parts)) {
                $max_ok = 0;
                $max_ng = 0;
                foreach ($request->qty_hasil_ok_parts as $part_id => $ok_val) {
                    $ok_val = (int)$ok_val;
                    $ng_val = (int)($request->qty_hasil_ng_parts[$part_id] ?? 0);
                    
                    DB::table('produksi_batch_parts')->where('id', $part_id)->update([
                        'qty_hasil_ok' => DB::raw("qty_hasil_ok + $ok_val"),
                        'qty_ng'       => DB::raw("qty_ng + $ng_val"),
                        'updated_at'   => now()
                    ]);

                    if ($ok_val > $max_ok) $max_ok = $ok_val;
                    if ($ng_val > $max_ng) $max_ng = $ng_val;
                }
                if ($qty_ok_new == 0) $qty_ok_new = $max_ok;
                if ($ng_parent_update == 0) $ng_parent_update = $max_ng;
            }

            $ng_details = [];
            if ($request->has('ng_detail_type') && is_array($request->ng_detail_type)) {
                $total_ng_spesifik = 0;
                foreach ($request->ng_detail_type as $idx => $type) {
                    $q = (int)($request->ng_detail_qty[$idx] ?? 0);
                    if ($q > 0) { 
                        $total_ng_spesifik += $q; 
                        $ng_details[] = ['type' => $type, 'qty' => $q]; 
                    }
                }
                $ng_parent_update += $total_ng_spesifik; 
            }

            $cleanPart = str_replace([' ', '-'], '', trim($p->material_code));
            $partMaster = DB::table('parts')->where('part_no', trim($p->material_code))->first() 
                ?? DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanPart])->first();
            
            $target = ($partMaster && !empty($partMaster->next_process)) ? strtoupper(trim($partMaster->next_process)) : 'FG';

            if ($qty_ok_new > 0) {
                DB::table('production_logs')->insert([
                    'part_no'      => $p->material_code, 
                    'qty'          => $qty_ok_new, 
                    'process_type' => ($target == 'WELDING') ? 'WELDING' : 'FG', 
                    'created_at'   => $planDate . ' ' . date('H:i:s'), 
                    'updated_at'   => now()
                ]);

                $fgColumns = DB::select("SHOW COLUMNS FROM finished_goods");
                $defaultValues = [];
                foreach ($fgColumns as $col) {
                    if ($col->Null === 'NO' && is_null($col->Default) && $col->Extra !== 'auto_increment') {
                        $type = strtolower($col->Type);
                        if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float')) {
                            $defaultValues[$col->Field] = 0;
                        } else {
                            $defaultValues[$col->Field] = '';
                        }
                    }
                }

                $fg = DB::table('finished_goods')->where('part_no', $p->material_code)->first();
                if (!$fg) {
                    $insertFg = array_merge($defaultValues, [
                        'part_no'        => $p->material_code,
                        'part_name'      => $partMaster->part_name ?? $p->material_code,
                        'customer'       => $partMaster->customer_code ?? 'SAI',
                        'customer_code'  => $partMaster->customer_code ?? 'SAI',
                        'actual_stock'   => ($target == 'WELDING') ? 0 : $qty_ok_new,
                        'welding_stock'  => ($target == 'WELDING') ? $qty_ok_new : 0,
                        'qty_per_unit'   => 1,
                        'qty_per_pallet' => 0,
                        'qty_order'      => 0,
                        'needs_per_day'  => 0,
                        'created_at'     => now(),
                        'updated_at'     => now()
                    ]);
                    DB::table('finished_goods')->insert($insertFg);
                } else {
                    if ($target == 'WELDING') {
                        DB::table('finished_goods')->where('part_no', $p->material_code)->increment('welding_stock', $qty_ok_new, ['updated_at' => now()]);
                    } else {
                        DB::table('finished_goods')->where('part_no', $p->material_code)->increment('actual_stock', $qty_ok_new, ['updated_at' => now()]);
                    }
                }
            }

            $qty_return_sheet = (int)$request->qty_return_warehouse; 
            if ($qty_return_sheet > 0) {
                $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
                if ($rmInfo) {
                    DB::table('rm_stocks')
                        ->whereRaw("REPLACE(coil_id, ' ', '') = ?", [str_replace(' ', '', $rmInfo->coil_id)])
                        ->increment('stock_pcs', $qty_return_sheet);
                    
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id'   => $p->rm_stock_id, 
                        'material_code' => $p->material_code,
                        'pcs_in'        => $qty_return_sheet, 
                        'source'        => 'return',
                        'no_produksi'   => $p->no_produksi, 
                        'created_at'    => $planDate . ' ' . date('H:i:s')
                    ]);
                }
            }

            $status_akhir = $request->status ?? 'COMPLETED';

            DB::table('produksi_batches')->where('id', $id)->update([
                'qty_hasil_ok'         => $p->qty_hasil_ok + $qty_ok_new,
                'qty_ng_process'       => $p->qty_ng_process + $ng_parent_update,
                'qty_hasil_ng'         => $p->qty_hasil_ng + $ng_parent_update,
                'qty_return_warehouse' => $p->qty_return_warehouse + $qty_return_sheet,
                'qty_return'           => 0, 
                'status'               => $status_akhir,
                'keterangan'           => $request->keterangan ?? $p->keterangan, 
                'updated_at'           => now() 
            ]);

            $this->syncToActual($id); 

            $actual = DB::table('production_actuals')->where('part_no', $p->material_code)->whereDate('created_at', $planDate)->first();
            if ($actual && !empty($ng_details)) {
                foreach ($ng_details as $detail) {
                    DB::table('production_ng_logs')->insert([
                        'actual_id'   => $actual->id, 
                        'no_produksi' => $p->no_produksi, 
                        'ng_type'     => $detail['type'], 
                        'qty'         => $detail['qty'], 
                        'created_at'  => $planDate . ' ' . date('H:i:s')
                    ]);
                }
            }

            $sub_id = $request->paired_batch_id;
            if (empty($sub_id) && str_ends_with($p->no_produksi, '-M')) {
                $lastDash = strrpos($p->no_produksi, '-');
                $timePrefix = $lastDash !== false ? substr($p->no_produksi, 0, $lastDash) : $p->no_produksi;
                $sub_id = DB::table('produksi_batches')->where('no_produksi', $timePrefix . '-S')->value('id');
            }

            if (!empty($sub_id)) {
                $p_sub = DB::table('produksi_batches')->where('id', $sub_id)->first();
                if ($p_sub) {
                    $qty_ok_sub = $request->has('paired_qty_ok') ? (int)$request->paired_qty_ok : $qty_ok_new;
                    $qty_ng_sub = $request->has('paired_qty_ng') ? (int)$request->paired_qty_ng : $ng_parent_update;

                    $cleanSubPart = str_replace([' ', '-'], '', trim($p_sub->material_code));
                    $subPartMaster = DB::table('parts')->where('part_no', trim($p_sub->material_code))->first()
                        ?? DB::table('parts')->whereRaw("REPLACE(REPLACE(part_no, ' ', ''), '-', '') = ?", [$cleanSubPart])->first();
                    $subTarget = ($subPartMaster && !empty($subPartMaster->next_process)) ? strtoupper(trim($subPartMaster->next_process)) : 'FG';

                    if ($qty_ok_sub > 0) {
                        DB::table('production_logs')->insert([
                            'part_no'      => $p_sub->material_code, 
                            'qty'          => $qty_ok_sub, 
                            'process_type' => ($subTarget == 'WELDING') ? 'WELDING' : 'FG', 
                            'created_at'   => $planDate . ' ' . date('H:i:s'),
                            'updated_at'   => now()
                        ]);

                        $subFg = DB::table('finished_goods')->where('part_no', $p_sub->material_code)->first();
                        if (!$subFg) {
                            $insertSubFg = array_merge($defaultValues ?? [], [
                                'part_no'        => $p_sub->material_code,
                                'part_name'      => $subPartMaster->part_name ?? $p_sub->material_code,
                                'customer'       => $subPartMaster->customer_code ?? 'SAI',
                                'customer_code'  => $subPartMaster->customer_code ?? 'SAI',
                                'actual_stock'   => ($subTarget == 'WELDING') ? 0 : $qty_ok_sub,
                                'welding_stock'  => ($subTarget == 'WELDING') ? $qty_ok_sub : 0,
                                'qty_per_unit'   => 1,
                                'qty_per_pallet' => 0,
                                'qty_order'      => 0,
                                'needs_per_day'  => 0,
                                'created_at'     => now(),
                                'updated_at'     => now()
                            ]);
                            DB::table('finished_goods')->insert($insertSubFg);
                        } else {
                            if ($subTarget == 'WELDING') {
                                DB::table('finished_goods')->where('part_no', $p_sub->material_code)->increment('welding_stock', $qty_ok_sub, ['updated_at' => now()]);
                            } else {
                                DB::table('finished_goods')->where('part_no', $p_sub->material_code)->increment('actual_stock', $qty_ok_sub, ['updated_at' => now()]);
                            }
                        }
                    }

                    DB::table('produksi_batches')->where('id', $sub_id)->update([
                        'qty_hasil_ok'   => $p_sub->qty_hasil_ok + $qty_ok_sub,
                        'qty_ng_process' => $p_sub->qty_ng_process + $qty_ng_sub,
                        'qty_hasil_ng'   => $p_sub->qty_hasil_ng + $qty_ng_sub,
                        'status'         => $status_akhir,
                        'updated_at'     => now()
                    ]);

                    $this->syncToActual($sub_id);
                }
            }

            DB::commit(); 
            return redirect()->route('produksi.index')->with('success', 'Hasil Produksi Berhasil Disimpan sesuai dengan Tanggal Jadwal!');
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
        
        $planDate = DB::table('production_plans')->where('id', $batch->plan_id)->value('plan_date');
        $dateOnly = $planDate ? $planDate : date('Y-m-d', strtotime($batch->created_at));
        
        DB::table('production_actuals')->updateOrInsert(
            ['part_no' => $batch->material_code, 'line_code' => $lineCode, 'created_at' => $dateOnly],
            ['shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'updated_at' => now()]
        );
    }

    public function getBatchDeepDive($no_produksi) { /* Tetap */ }
    
    /**
     * ✨ HISTORY & CHART MURNI BERDASARKAN YIELD (OK / TAKE) ✨
     */
    public function history(Request $request) 
    { 
        $startDate = $request->start_date ?? date('Y-m-d', strtotime('-6 days'));
        $endDate = $request->end_date ?? date('Y-m-d');
        
        $customerFilter = $request->customer;
        $partFilter = $request->part_no;

        $query = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->leftJoin('production_plans', 'produksi_batches.plan_id', '=', 'production_plans.id')
            ->select(
                'produksi_batches.*',
                'line.kode_Line',
                'line.nama_Line',
                'parts.part_name',
                'rm_stocks.coil_id',
                'rm_stocks.spec',
                'rm_stocks.size',
                'rm_stocks.customer',
                'production_plans.plan_date'
            )
            ->where('produksi_batches.status', 'COMPLETED')
            ->where('produksi_batches.no_produksi', 'NOT LIKE', '%-S') 
            ->whereBetween(DB::raw('COALESCE(production_plans.plan_date, DATE(produksi_batches.created_at))'), [$startDate, $endDate]);

        if (!empty($customerFilter)) {
            $query->where('rm_stocks.customer', trim($customerFilter));
        }
        if (!empty($partFilter)) {
            $query->where(function($q) use ($partFilter) {
                $q->where('produksi_batches.material_code', trim($partFilter))
                  ->orWhere('produksi_batches.keterangan', 'LIKE', '%' . trim($partFilter) . '%');
            });
        }

        $query->orderBy(DB::raw('COALESCE(production_plans.plan_date, DATE(produksi_batches.created_at))'), 'desc');

        $historiesRaw = $query->paginate(50); 

        $historiesRaw->getCollection()->transform(function ($item) {
            $isMainPair = (strpos($item->keterangan, 'PAIR_MAIN:') === 0) || str_ends_with($item->no_produksi, '-M');
            $item->is_separating = $isMainPair;
            $item->paired_part = null;
            $item->paired_part_name = null;
            $item->paired_ok = 0;
            $item->paired_ng = 0;
            $item->paired_ng_remark = '-';

            if ($isMainPair) {
                $lastDash = strrpos($item->no_produksi, '-');
                $timePrefix = $lastDash !== false ? substr($item->no_produksi, 0, $lastDash) : $item->no_produksi;
                
                $pairedBatch = DB::table('produksi_batches')
                    ->where('no_produksi', $timePrefix . '-S')
                    ->first();
                
                if (!$pairedBatch && strpos($item->keterangan, 'PAIR_MAIN:') === 0) {
                    $subPartCode = str_replace('PAIR_MAIN:', '', $item->keterangan);
                    $pairedBatch = DB::table('produksi_batches')
                        ->where('material_code', $subPartCode)
                        ->whereDate('created_at', date('Y-m-d', strtotime($item->created_at)))
                        ->first();
                }

                if ($pairedBatch) {
                    $item->paired_part = $pairedBatch->material_code;
                    $item->paired_part_name = DB::table('parts')->where('part_no', $pairedBatch->material_code)->value('part_name') ?? $pairedBatch->material_code;
                    $item->paired_ok = (int) $pairedBatch->qty_hasil_ok;
                    $item->paired_ng = (int) $pairedBatch->qty_hasil_ng;

                    $subNgLogs = DB::table('production_ng_logs')
                        ->where('no_produksi', $pairedBatch->no_produksi)
                        ->get();
                    
                    $item->paired_ng_details = $subNgLogs;
                    $item->paired_ng_remark = $subNgLogs->isNotEmpty()
                        ? $subNgLogs->map(fn($n) => "{$n->ng_type}: {$n->qty}")->implode(', ')
                        : ($pairedBatch->qty_hasil_ng > 0 ? "Defect: {$pairedBatch->qty_hasil_ng} pcs" : '-');
                }
            }

            $coilsUsed = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->where('rm_production_logs.no_produksi', $item->no_produksi)
                ->select(
                    'rm_stocks.coil_id',
                    'rm_stocks.spec',
                    'rm_stocks.size',
                    'rm_production_logs.pcs_used'
                )
                ->get();

            if ($coilsUsed->isNotEmpty()) {
                $item->coils_used = $coilsUsed;
                $item->coils_summary = $coilsUsed->map(function($c) {
                    return ($c->coil_id ?? 'COIL') . ' (' . number_format($c->pcs_used) . ' lbr)';
                })->implode(', ');
            } else {
                $item->coils_used = collect([
                    (object)[
                        'coil_id'  => $item->coil_id ?? '-',
                        'spec'     => $item->spec ?? '-',
                        'size'     => $item->size ?? '-',
                        'pcs_used' => $item->qty_ambil_pcs
                    ]
                ]);
                $item->coils_summary = $item->coil_id ? ($item->coil_id . ' (' . number_format($item->qty_ambil_pcs) . ' lbr)') : '-';
            }

            $ngLogs = DB::table('production_ng_logs')
                ->where('no_produksi', $item->no_produksi)
                ->get();

            $item->ng_details = $ngLogs;
            $item->ng_remark = $ngLogs->isNotEmpty() 
                ? $ngLogs->map(fn($n) => "{$n->ng_type}: {$n->qty}")->implode(', ')
                : ($item->qty_hasil_ng > 0 ? "Defect: {$item->qty_hasil_ng} pcs" : '-');

            $item->qty_ambil_pcs = $item->qty_ambil_pcs * ($item->cavity ?? 1);
            $item->qty_return_warehouse = $item->qty_return_warehouse * ($item->cavity ?? 1);

            return $item;
        });

        $histories = $historiesRaw;
        $activeProductions = $historiesRaw;

        // ✨ HITUNG STATISTIK GLOBAL & CHART (MENGGUNAKAN YIELD DARI TOTAL TAKE PCS) ✨
        $statsQuery = DB::table('produksi_batches')
            ->leftJoin('production_plans', 'produksi_batches.plan_id', '=', 'production_plans.id')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->select(
                'produksi_batches.no_produksi',
                'produksi_batches.qty_ambil_pcs',
                'produksi_batches.qty_return_warehouse',
                'produksi_batches.cavity',
                'produksi_batches.qty_hasil_ok',
                'produksi_batches.qty_hasil_ng',
                DB::raw('COALESCE(production_plans.plan_date, DATE(produksi_batches.created_at)) as tgl')
            )
            ->where('produksi_batches.status', 'COMPLETED')
            ->whereBetween(DB::raw('COALESCE(production_plans.plan_date, DATE(produksi_batches.created_at))'), [$startDate, $endDate]);

        if (!empty($customerFilter)) {
            $statsQuery->where('rm_stocks.customer', trim($customerFilter));
        }
        if (!empty($partFilter)) {
            $statsQuery->where(function($q) use ($partFilter) {
                $q->where('produksi_batches.material_code', trim($partFilter))
                  ->orWhere('produksi_batches.keterangan', 'LIKE', '%' . trim($partFilter) . '%');
            });
        }
        
        $statsRaw = $statsQuery->get();

        // 1. Simpan Effective Take untuk Part Utama (-M) 
        // agar part pasangannya (-S) juga tahu berapa total Take-nya
        $mainTakes = [];
        foreach ($statsRaw as $row) {
            if (!str_ends_with($row->no_produksi, '-S')) {
                $baseProd = preg_replace('/-[MS]$/', '', $row->no_produksi);
                $take = $row->qty_ambil_pcs * ($row->cavity ?? 1);
                $ret = ($row->qty_return_warehouse ?? 0) * ($row->cavity ?? 1);
                $mainTakes[$baseProd] = max(0, $take - $ret);
            }
        }

        // 2. Loop Semua Data (Termasuk Separating LH) Untuk Totalan Harian & Global
        $dailyAgg = [];
        $globalTakePcs = 0;
        $globalOk = 0;
        $globalNg = 0;
        $globalRet = 0;

        foreach ($statsRaw as $row) {
            $tgl = $row->tgl;
            if (!isset($dailyAgg[$tgl])) {
                $dailyAgg[$tgl] = ['ok' => 0, 'ng' => 0, 'take_pcs' => 0];
            }
            
            $ok = (int)$row->qty_hasil_ok;
            $ng = (int)$row->qty_hasil_ng;
            
            $dailyAgg[$tgl]['ok'] += $ok;
            $dailyAgg[$tgl]['ng'] += $ng;
            $globalOk += $ok;
            $globalNg += $ng;
            
            $baseProd = preg_replace('/-[MS]$/', '', $row->no_produksi);
            
            if (!str_ends_with($row->no_produksi, '-S')) {
                $effTake = $mainTakes[$baseProd] ?? 0;
                $dailyAgg[$tgl]['take_pcs'] += $effTake;
                $globalTakePcs += $effTake;
                $globalRet += ($row->qty_return_warehouse ?? 0) * ($row->cavity ?? 1);
            } else {
                // Untuk Part Separating (Pasangannya), dia memakan Take yang sama
                $effTake = $mainTakes[$baseProd] ?? 0;
                $dailyAgg[$tgl]['take_pcs'] += $effTake;
                $globalTakePcs += $effTake;
            }
        }

        // Global KPI Percentage
        $globalPerf = $globalTakePcs > 0 ? ($globalOk / $globalTakePcs) * 100 : 0;
        if ($globalPerf > 99.9 && $globalNg > 0) $globalPerf = 99.9;

        // 3. Masukkan ke Grafik (Chart)
        $chartLabels = []; 
        $chartOk = []; 
        $chartNg = []; 
        $chartPerf = [];
        
        $currentDate = strtotime($startDate);
        $lastDate = strtotime($endDate);
        
        while ($currentDate <= $lastDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $chartLabels[] = date('d M', $currentDate);
            
            if (isset($dailyAgg[$dateStr])) {
                $d = $dailyAgg[$dateStr];
                $chartOk[] = $d['ok'];
                $chartNg[] = $d['ng'];
                
                $perf = $d['take_pcs'] > 0 ? ($d['ok'] / $d['take_pcs']) * 100 : 0;
                $chartPerf[] = ($perf > 99 && $d['ng'] > 0) ? 99.9 : round($perf, 1);
            } else {
                $chartOk[] = 0;
                $chartNg[] = 0;
                $chartPerf[] = 0;
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }

        // Kembalikan Semua Kalkulasi Ke View Blade
        return view('Produksi.history', compact('histories', 'activeProductions', 'startDate', 'endDate', 'chartLabels', 'chartOk', 'chartNg', 'chartPerf', 'globalTakePcs', 'globalOk', 'globalNg', 'globalRet', 'globalPerf')); 
    }
    
    public function getSpecsByCustomer($customer) 
    {
        $specs = DB::table('rm_stocks')->where('customer', trim($customer))->where('stock_pcs', '>', 0)->select(DB::raw('TRIM(spec) as spec'), 'size', DB::raw("REPLACE(size, ' ', '') as size_clean"))->groupBy('spec', 'size', 'size_clean')->get();
        return response()->json($specs);
    }

    public function getPartsBySpec(Request $request) 
    {
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

    public function returnToRM($id) 
    {
        $p = DB::table('produksi_batches')->where('id', $id)->first();
        $rmInfo = DB::table('rm_stocks')->where('id', $p->rm_stock_id)->first();
        DB::beginTransaction();
        try { 
            if ($p && $rmInfo) { 
                DB::table('rm_stocks')
                    ->whereRaw("REPLACE(coil_id, ' ', '') = ?", [str_replace(' ', '', $rmInfo->coil_id)])
                    ->increment('stock_pcs', $p->qty_ambil_pcs); 
                
                DB::table('rm_production_logs')->where('no_produksi', $p->no_produksi)->delete(); 
            } 
            DB::table('produksi_batches')->where('no_produksi', $p->no_produksi)->delete(); 
            DB::commit(); 
            return redirect()->route('produksi.index')->with('success', 'Batch Dibatalkan.'); 
        } catch (\Exception $e) { DB::rollback(); return back(); }
    }

    public function getPartDetail($id) { return response()->json(['sisa_jalan' => 0, 'stock_pcs' => 0]); }
    public function resolveInterruption(Request $request, $id) { return $this->updateResult($request, $id); }
    public function gateConfirm(Request $request, $id) { return $this->updateResult($request, $id); }
    public function reportProblem(Request $request, $id) 
    { 
        DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROBLEM', 'keterangan' => '⚠️ DIES RUSAK: ' . $request->problem_note, 'updated_at' => now()]); 
        return redirect()->back()->with('error', 'Laporan kendala telah dikirim!'); 
    }
}