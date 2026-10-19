<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PPICController extends Controller
{
    /**
     * 1. DASHBOARD UTAMA (STAMPING)
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $today = date('Y-m-d');
        
        $alerts = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->where('produksi_batches.status', 'PROBLEM')
            ->select('produksi_batches.id', 'produksi_batches.no_produksi', 'produksi_batches.material_code', 'line.kode_Line', 'produksi_batches.keterangan', 'produksi_batches.updated_at')
            ->get();

        $plans = DB::table('production_plans')->where('plan_date', $date)->get();
        $statusCount = ['waiting' => 0, 'running' => 0, 'completed' => 0, 'shortage' => 0];
        $chartLabels = []; $chartTargets = []; $chartActuals = [];

        foreach($plans as $p) {
            $targetPerPart = ($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            $actualPerPart = DB::table('production_actuals')
                ->where('part_no', $p->part_no)
                ->whereDate('created_at', $date)
                ->where('line_code', '!=', 'WELDING AREA')
                ->sum('qty_ok');
            
            $p->actual_qty = (int)$actualPerPart;
            $p->plan_qty = (int)$targetPerPart;
            $chartLabels[] = $p->part_no;
            $chartTargets[] = (int)$targetPerPart;
            $chartActuals[] = (int)$actualPerPart;

            $batchStatuses = DB::table('produksi_batches')->where('plan_id', $p->id)->pluck('status');
            $isAllBatchCompleted = $batchStatuses->isNotEmpty() && $batchStatuses->every(fn($st) => $st === 'COMPLETED');

            if ($isAllBatchCompleted || ($targetPerPart > 0 && $actualPerPart >= $targetPerPart)) { 
                $statusCount['completed']++; 
            }
            elseif ($date < $today && $actualPerPart < $targetPerPart) { 
                $statusCount['shortage']++; 
            }
            elseif ($actualPerPart > 0) { 
                $statusCount['running']++; 
            }
            else { 
                $statusCount['waiting']++; 
            }
        }

        $totalPlan = $plans->sum('plan_qty') ?: 0;
        $totalActual = $plans->sum('actual_qty') ?: 0;
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $dailyLabels = []; $dailyOk = []; $dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok');
            $dailyNg[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ng');
        }

        $monthlyLabels = []; $monthlyOk = []; $monthlyNg = [];
        for ($i = 5; $i >= 0; $i--) {
            $mDate = date('Y-m', strtotime("-$i months"));
            $monthlyLabels[] = date('M', strtotime("-$i months"));
            $monthlyOk[] = DB::table('production_actuals')->where('created_at', 'LIKE', "$mDate%")->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok');
            $monthlyNg[] = DB::table('production_actuals')->where('created_at', 'LIKE', "$mDate%")->where('line_code', '!=', 'WELDING AREA')->sum('qty_ng');
        }

        return view('PPIC.ppic_planning', compact(
            'plans', 'statusCount', 'achievementRate', 'date', 'totalPlan', 
            'totalActual', 'chartLabels', 'chartTargets', 'chartActuals', 
            'monthlyLabels', 'monthlyOk', 'monthlyNg', 'dailyLabels', 'dailyOk', 'dailyNg',
            'alerts'
        ));
    }

    /**
     * 2. DAILY MPS (GABUNG S1 & S2)
     */
    public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        $allPlans = DB::table('production_plans')
            ->where('plan_date', $date)
            ->orderBy('line_code', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalPlanQty = 0;
        $totalWorkingHoursS1 = 0; 
        $totalWorkingHoursS2 = 0; 
        $totalDandory = 0;

        $processedPlans = collect();
        $lineFinishTimeS1 = []; 
        $lineFinishTimeS2 = [];

        foreach($allPlans as $p) {
            $cekSub = DB::table('produksi_batches')->where('plan_id', $p->id)->where('no_produksi', 'LIKE', '%-S')->exists();
            if ($cekSub) {
                continue; 
            }

            $t_s1 = $p->s1_plan_reg + $p->s1_plan_ot;
            $t_s2 = $p->s2_plan_reg + $p->s2_plan_ot;
            $dandory = $p->dandory_time ?? 15;

            if ($t_s1 > 0) {
                $actualS1 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Pagi')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s1 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS1[$p->line_code] ?? "07:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS1[$p->line_code] = $finish;

                $item = clone $p; 
                $item->display_shift = 'S1';
                $item->total_target = $t_s1;
                $item->total_actual = (int)$actualS1;
                $item->balance = $t_s1 - $actualS1;
                $item->start_time = $start;
                $item->ahir_time = $finish;
                $processedPlans->push($item);

                $totalPlanQty += $t_s1;
                $totalWorkingHoursS1 += $dur; 
                $totalDandory += $dandory;
            }

            if ($t_s2 > 0) {
                $actualS2 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Malam')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s2 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS2[$p->line_code] ?? "19:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS2[$p->line_code] = $finish;

                $item = clone $p; 
                $item->display_shift = 'S2';
                $item->total_target = $t_s2;
                $item->total_actual = (int)$actualS2;
                $item->balance = $t_s2 - $actualS2;
                $item->start_time = $start;
                $item->ahir_time = $finish;
                $processedPlans->push($item);

                $totalPlanQty += $t_s2;
                $totalWorkingHoursS2 += $dur; 
                $totalDandory += $dandory;
            }
        }

        $groupedPlans = $processedPlans->groupBy('line_code');
        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        return view('PPIC.mps_index', compact(
            'groupedPlans', 'date', 'availableLines', 'availableCustomers', 
            'totalPlanQty', 'totalWorkingHoursS1', 'totalWorkingHoursS2', 'totalDandory'
        ));
    }

    /**
     * 3. STORE MPS STAMPING: KELOLA MULTI-BUNDLE FIFO & SIMPAN PENANDA SEPARATING
     */
    public function mpsStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);
            $total_target = $target_s1 + $target_s2;
            $part_separator = $request->part_no_separator; 

            if ($total_target <= 0) {
                throw new \Exception("Target produksi tidak boleh kosong / 0.");
            }

            $cleanPart = trim($request->part_no);

            // Ambil semua bundle fisik yang terikat ke part ini
            $mappedCoilIds = DB::table('rm_stocks')
                ->where(function($q) use ($cleanPart) {
                    $q->where('material_code', $cleanPart)
                      ->orWhere('material_name', $cleanPart)
                      ->orWhereRaw("REPLACE(material_code, ' ', '') = ?", [str_replace(' ', '', $cleanPart)]);
                })
                ->pluck('coil_id')
                ->unique();

            $availableCoils = DB::table('rm_stocks')
                ->whereIn('coil_id', $mappedCoilIds)
                ->where('stock_pcs', '>', 0)
                ->orderBy('id', 'asc')
                ->get()
                ->unique('coil_id');

            // Fallback spesifikasi material
            if ($availableCoils->isEmpty()) {
                $refStock = DB::table('rm_stocks')->where('material_code', $cleanPart)->first();
                if ($refStock) {
                    $availableCoils = DB::table('rm_stocks')
                        ->where('customer', trim($refStock->customer))
                        ->where(DB::raw('TRIM(spec)'), trim($refStock->spec))
                        ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $refStock->size))
                        ->where('stock_pcs', '>', 0)
                        ->orderBy('id', 'asc')
                        ->get()
                        ->unique('coil_id');
                }
            }

            if ($availableCoils->isEmpty()) {
                throw new \Exception("Material untuk Part No [{$request->part_no}] kosong atau tidak terdaftar!");
            }

            $firstCoil = $availableCoils->first();
            $cavity = $firstCoil->cavity > 0 ? (int)$firstCoil->cavity : 1;
            
            $kebutuhan_lembar = (int) ceil($total_target / $cavity);
            $total_stok_tersedia = (int) $availableCoils->sum('stock_pcs');

            if ($total_stok_tersedia < $kebutuhan_lembar) {
                throw new \Exception("❌ PLAN DITOLAK: TOTAL STOK KURANG! Butuh {$kebutuhan_lembar} Lembar, total dari seluruh bundle hanya {$total_stok_tersedia} Lembar.");
            }

            // Simpan Plan Utama
            $planIdMain = DB::table('production_plans')->insertGetId([
                'plan_date'     => $request->plan_date, 
                'part_no'       => $request->part_no, 
                'customer_code' => $request->customer_code,
                'line_code'     => $request->line_code, 
                'manpower'      => $request->manpower ?? 8, 
                'process_qty'   => $request->process_qty ?? 4,
                'qty_lot'       => $request->qty_lot ?? 200, 
                'cap_per_hour'  => $request->cap_per_hour ?? 320,
                's1_plan_reg'   => $request->s1_plan_reg ?? 0, 
                's1_plan_ot'    => $request->s1_plan_ot ?? 0,
                's2_plan_reg'   => $request->s2_plan_reg ?? 0, 
                's2_plan_ot'    => $request->s2_plan_ot ?? 0,
                'dandory_time'  => $request->dandory_time ?? 15, 
                'created_at'    => now(), 
                'updated_at'    => now()
            ]);

            // Simpan Plan Sub (Separating)
            $planIdSub = null;
            if (!empty($part_separator)) {
                $planIdSub = DB::table('production_plans')->insertGetId([
                    'plan_date'     => $request->plan_date, 
                    'part_no'       => $part_separator, 
                    'customer_code' => $request->customer_code,
                    'line_code'     => $request->line_code, 
                    'manpower'      => $request->manpower ?? 8, 
                    'process_qty'   => $request->process_qty ?? 4,
                    'qty_lot'       => $request->qty_lot ?? 200, 
                    'cap_per_hour'  => $request->cap_per_hour ?? 320,
                    's1_plan_reg'   => $request->s1_plan_reg ?? 0, 
                    's1_plan_ot'    => $request->s1_plan_ot ?? 0,
                    's2_plan_reg'   => $request->s2_plan_reg ?? 0, 
                    's2_plan_ot'    => $request->s2_plan_ot ?? 0,
                    'dandory_time'  => $request->dandory_time ?? 15, 
                    'created_at'    => now(), 
                    'updated_at'    => now()
                ]);
            }

            $mesin = DB::table('line')->where('kode_Line', $request->line_code)->first();
            $mesin_id = $mesin ? $mesin->id : null;

            $qty_lembar_s1 = $target_s1 > 0 ? (int) ceil($target_s1 / $cavity) : 0;
            $qty_lembar_s2 = max(0, $kebutuhan_lembar - $qty_lembar_s1);

            $logTimestamp = date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s')));
            
            // Penanda PAIR_MAIN wajib ada agar terbaca di Terminal Line Control
            $ket = !empty($part_separator) ? "PAIR_MAIN:{$part_separator}" : "AUTO-DEPLOY FROM PPIC";
            $main_suffix = !empty($part_separator) ? '-M' : '';

            $coilPool = [];
            foreach ($availableCoils as $c) {
                $coilPool[$c->coil_id] = [
                    'id'            => $c->id,
                    'coil_id'       => $c->coil_id,
                    'stock'         => (int)$c->stock_pcs,
                    'material_code' => $c->material_code
                ];
            }

            // 1. ALOKASI SHIFT 1 (POTONG BERTAHAP MAKSIMAL SESUAI SALDO TIAP BUNDLE)
            if ($qty_lembar_s1 > 0 && $mesin_id) {
                $base_prod_s1 = 'WOS-S1-' . date('His') . $main_suffix;
                $remS1 = $qty_lembar_s1;

                foreach ($coilPool as $cKey => &$cData) {
                    if ($remS1 <= 0) break;
                    if ($cData['stock'] <= 0) continue;

                    $take = min($cData['stock'], $remS1);
                    $cData['stock'] -= $take;
                    $remS1 -= $take;

                    // Potong seluruh part pada coil_id ini di rm_stocks
                    DB::table('rm_stocks')->where('coil_id', trim($cData['coil_id']))->decrement('stock_pcs', $take);

                    // Catat log per bundle fisik dengan no_produksi batch utama
                    DB::table('rm_production_logs')->insert([
                        'rm_stock_id'   => $cData['id'],
                        'material_code' => $request->part_no,
                        'pcs_used'      => $take,
                        'no_produksi'   => $base_prod_s1,
                        'created_at'    => $logTimestamp
                    ]);
                }
                unset($cData);

                DB::table('produksi_batches')->insert([
                    'no_produksi'   => $base_prod_s1,
                    'plan_id'       => $planIdMain,
                    'shift'         => 'Pagi',
                    'mesin_id'      => $mesin_id,
                    'rm_stock_id'   => $firstCoil->id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s1,
                    'cavity'        => $cavity,
                    'status'        => 'PROSES',
                    'keterangan'    => $ket,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);

                if ($planIdSub) {
                    $subNoProdS1 = substr($base_prod_s1, 0, strrpos($base_prod_s1, '-')) . '-S';
                    DB::table('produksi_batches')->insert([
                        'no_produksi'   => $subNoProdS1,
                        'plan_id'       => $planIdSub,
                        'shift'         => 'Pagi',
                        'mesin_id'      => $mesin_id,
                        'rm_stock_id'   => 0,
                        'material_code' => $part_separator,
                        'qty_ambil_pcs' => 0,
                        'cavity'        => 1,
                        'status'        => 'PROSES',
                        'keterangan'    => $ket,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);
                }
            }

            // 2. ALOKASI SHIFT 2
            if ($qty_lembar_s2 > 0 && $mesin_id) {
                $base_prod_s2 = 'WOS-S2-' . date('His', strtotime('+1 second')) . $main_suffix;
                $remS2 = $qty_lembar_s2;

                foreach ($coilPool as $cKey => &$cData) {
                    if ($remS2 <= 0) break;
                    if ($cData['stock'] <= 0) continue;

                    $take = min($cData['stock'], $remS2);
                    $cData['stock'] -= $take;
                    $remS2 -= $take;

                    DB::table('rm_stocks')->where('coil_id', trim($cData['coil_id']))->decrement('stock_pcs', $take);

                    DB::table('rm_production_logs')->insert([
                        'rm_stock_id'   => $cData['id'],
                        'material_code' => $request->part_no,
                        'pcs_used'      => $take,
                        'no_produksi'   => $base_prod_s2,
                        'created_at'    => $logTimestamp
                    ]);
                }
                unset($cData);

                DB::table('produksi_batches')->insert([
                    'no_produksi'   => $base_prod_s2,
                    'plan_id'       => $planIdMain,
                    'shift'         => 'Malam',
                    'mesin_id'      => $mesin_id,
                    'rm_stock_id'   => $firstCoil->id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s2,
                    'cavity'        => $cavity,
                    'status'        => 'PROSES',
                    'keterangan'    => $ket,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);

                if ($planIdSub) {
                    $subNoProdS2 = substr($base_prod_s2, 0, strrpos($base_prod_s2, '-')) . '-S';
                    DB::table('produksi_batches')->insert([
                        'no_produksi'   => $subNoProdS2,
                        'plan_id'       => $planIdSub,
                        'shift'         => 'Malam',
                        'mesin_id'      => $mesin_id,
                        'rm_stock_id'   => 0,
                        'material_code' => $part_separator,
                        'qty_ambil_pcs' => 0,
                        'cavity'        => 1,
                        'status'        => 'PROSES',
                        'keterangan'    => $ket,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "✅ Jadwal Berhasil Dideploy! Total {$kebutuhan_lembar} Lembar dipotong otomatis dari RM.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. UPDATE & REVISI WOS STAMPING
     */
    public function updateWos(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $plan = DB::table('production_plans')->where('id', $id)->first();
            if (!$plan) throw new \Exception("Schedule tidak ditemukan.");

            $existingBatches = DB::table('produksi_batches')->where('plan_id', $id)->get();
            
            $isStarted = $existingBatches->contains(function($b) {
                return $b->qty_hasil_ok > 0 || $b->qty_hasil_ng > 0 || $b->status === 'COMPLETED';
            });

            if ($isStarted) {
                throw new \Exception("❌ REVISI DITOLAK! WOS ini sudah mulai dikerjakan (Aktual > 0) atau sudah ditutup.");
            }

            $alreadyReturnedByProd = $existingBatches->sum('qty_return_warehouse');
            $sampleBatch = $existingBatches->first();
            
            $suffix = '';
            if ($sampleBatch) {
                if (str_ends_with($sampleBatch->no_produksi, '-M')) $suffix = '-M';
                if (str_ends_with($sampleBatch->no_produksi, '-S')) $suffix = '-S';
            }
            $is_separating_sub = ($suffix === '-S');

            $old_target_s1 = $plan->s1_plan_reg + $plan->s1_plan_ot;
            $old_target_s2 = $plan->s2_plan_reg + $plan->s2_plan_ot;
            $old_total = $old_target_s1 + $old_target_s2;

            $new_target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $new_target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);
            $new_total = $new_target_s1 + $new_target_s2;

            $logTimestamp = date('Y-m-d H:i:s', strtotime($plan->plan_date . ' ' . date('H:i:s')));

            if (!$is_separating_sub) {
                $availableStocks = DB::table('rm_stocks')->where('material_code', $plan->part_no)->get()->unique('coil_id');
                $firstStock = $availableStocks->first();
                if (!$firstStock) throw new \Exception("Database Material tidak terdeteksi.");

                $cavity = $firstStock->cavity > 0 ? $firstStock->cavity : 1;
                $old_lembar = ceil($old_total / $cavity);
                $new_lembar = ceil($new_total / $cavity);
                $selisih_lembar = $new_lembar - $old_lembar;

                $totalStockNow = $availableStocks->sum('stock_pcs');

                if ($selisih_lembar > 0) {
                    if ($totalStockNow < $selisih_lembar) throw new \Exception("❌ REVISI DITOLAK! Sisa total stok hanya {$totalStockNow}.");
                    
                    $remAdd = $selisih_lembar;
                    foreach ($availableStocks as $stk) {
                        if ($remAdd <= 0) break;
                        if ($stk->stock_pcs <= 0) continue;
                        $take = min($stk->stock_pcs, $remAdd);
                        DB::table('rm_stocks')->where('coil_id', trim($stk->coil_id))->decrement('stock_pcs', $take);
                        DB::table('rm_production_logs')->insert(['rm_stock_id' => $stk->id, 'material_code' => $plan->part_no, 'pcs_used' => $take, 'no_produksi' => 'REV-OUT-' . date('YmdHis'), 'created_at' => $logTimestamp]);
                        $remAdd -= $take;
                    }
                } elseif ($selisih_lembar < 0) {
                    $raw_return = abs($selisih_lembar);
                    $net_return = max(0, $raw_return - $alreadyReturnedByProd);
                    if ($net_return > 0) {
                        // Kembalikan proporsional ke coil yang pernah dipotong
                        $usedLogs = DB::table('rm_production_logs')
                            ->whereIn('no_produksi', $existingBatches->pluck('no_produksi'))
                            ->orderBy('id', 'desc')
                            ->get();

                        $remReturn = $net_return;
                        foreach ($usedLogs as $log) {
                            if ($remReturn <= 0) break;
                            $refCoil = DB::table('rm_stocks')->where('id', $log->rm_stock_id)->first();
                            if ($refCoil) {
                                $ret = min($log->pcs_used, $remReturn);
                                DB::table('rm_stocks')->where('coil_id', trim($refCoil->coil_id))->increment('stock_pcs', $ret);
                                DB::table('rm_incoming_logs')->insert(['rm_stock_id' => $refCoil->id, 'material_code' => $plan->part_no, 'pcs_in' => $ret, 'source' => 'return', 'no_produksi' => 'REV-RTN-' . date('YmdHis'), 'created_at' => $logTimestamp]);
                                $remReturn -= $ret;
                            }
                        }
                    }
                }
                $rm_stock_id = $firstStock->id;
            } else {
                $cavity = 1; $rm_stock_id = 0; $selisih_lembar = 0;
            }

            DB::table('production_plans')->where('id', $id)->update([
                's1_plan_reg' => $request->s1_plan_reg ?? 0, 's1_plan_ot' => $request->s1_plan_ot ?? 0,
                's2_plan_reg' => $request->s2_plan_reg ?? 0, 's2_plan_ot' => $request->s2_plan_ot ?? 0,
                'cap_per_hour' => $request->cap_per_hour ?? $plan->cap_per_hour, 'dandory_time' => $request->dandory_time ?? $plan->dandory_time, 'updated_at' => now()
            ]);

            DB::table('produksi_batches')->where('plan_id', $id)->where('status', '!=', 'COMPLETED')->delete();

            $qty_lembar_s1 = $is_separating_sub ? 0 : ceil($new_target_s1 / $cavity);
            $qty_lembar_s2 = $is_separating_sub ? 0 : max(0, ceil($new_total / $cavity) - $qty_lembar_s1);
            $mesin = DB::table('line')->where('kode_Line', $plan->line_code)->first();

            if ($new_target_s1 > 0) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S1-REV-' . date('His') . $suffix, 'plan_id' => $id, 'shift' => 'Pagi', 'mesin_id' => $mesin ? $mesin->id : null,
                    'rm_stock_id' => $rm_stock_id, 'material_code' => $plan->part_no, 'qty_ambil_pcs' => $qty_lembar_s1, 'cavity' => $cavity,
                    'status' => 'PROSES', 'keterangan' => 'DIREVISI PPIC', 'created_at' => now(), 'updated_at' => now()
                ]);
            }

            if ($new_target_s2 > 0) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S2-REV-' . date('His') . $suffix, 'plan_id' => $id, 'shift' => 'Malam', 'mesin_id' => $mesin ? $mesin->id : null,
                    'rm_stock_id' => $rm_stock_id, 'material_code' => $plan->part_no, 'qty_ambil_pcs' => $qty_lembar_s2, 'cavity' => $cavity,
                    'status' => 'PROSES', 'keterangan' => 'DIREVISI PPIC', 'created_at' => now(), 'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', "Revisi Sukses!");
        } catch (\Exception $e) {
            DB::rollBack(); return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5. PRINT WOS 
     */
    public function printWos($date, $shift, $line_code)
    {
        $plansS1 = DB::table('production_plans')->where('plan_date', $date)->where('line_code', $line_code)->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0)->orderBy('id', 'asc')->get();
        $plansS2 = DB::table('production_plans')->where('plan_date', $date)->where('line_code', $line_code)->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0)->orderBy('id', 'asc')->get();

        if ($plansS1->isEmpty() && $plansS2->isEmpty()) return redirect()->back()->with('error', 'Tidak ada jadwal di mesin ini untuk dicetak.');

        $planIds = collect()->merge($plansS1)->merge($plansS2)->pluck('id')->unique()->toArray();
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        $plansS1 = $plansS1->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Pagi')->first();
            return !($batchData && str_ends_with($batchData->no_produksi, '-S'));
        })->values();

        $plansS2 = $plansS2->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Malam')->first();
            return !($batchData && str_ends_with($batchData->no_produksi, '-S'));
        })->values();

        return view('PPIC.print_wos', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * 5B. PRINT WOS BUNDLE (BIG & SMALL)
     */
    public function printWosBundle($date)
    {
        $allPlans = DB::table('production_plans')->where('plan_date', $date)->where(DB::raw('s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot'), '>', 0)->orderBy('id', 'asc')->get();
        if ($allPlans->isEmpty()) return redirect()->back()->with('error', 'Belum ada jadwal produksi pada tanggal ini untuk dicetak.');

        $planIds = $allPlans->pluck('id')->toArray();
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        $allPlansFiltered = $allPlans->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->first();
            return !($batchData && str_ends_with($batchData->no_produksi, '-S'));
        });

        $bigPlans = $allPlansFiltered->filter(function($p) { $code = strtoupper($p->line_code); return !str_contains($code, 'C') && !str_contains($code, 'SMALL'); })->values();
        $smallPlans = $allPlansFiltered->filter(function($p) { $code = strtoupper($p->line_code); return str_contains($code, 'C') || str_contains($code, 'SMALL'); })->values();

        return view('PPIC.print_wos_bundle', compact('bigPlans', 'smallPlans', 'batches', 'date'));
    }

    /**
     * 6. PRINT SURAT SERAH TERIMA MATERIAL (BPRM OTOMATIS MENJABARKAN SETIAP COIL FISIK)
     */
    public function printSerahTerima($date, $shift, $line_code)
    {
        $planIds = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code)
            ->pluck('id');

        $batchesRaw = DB::table('produksi_batches')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->where('produksi_batches.no_produksi', 'NOT LIKE', '%-S')
            ->select('produksi_batches.*', 'parts.part_name', 'parts.customer_code as part_cust')
            ->orderBy('produksi_batches.id', 'asc')
            ->get();

        if ($batchesRaw->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada Material Request untuk mesin ini.');
        }

        $allBatches = collect();

        foreach ($batchesRaw as $batch) {
            $isPair = strpos($batch->keterangan, 'PAIR_MAIN:') === 0 || str_ends_with($batch->no_produksi, '-M');
            $subPartNo = null;
            $subPartName = null;

            if ($isPair) {
                if (strpos($batch->keterangan, 'PAIR_MAIN:') === 0) {
                    $subPartNo = str_replace('PAIR_MAIN:', '', $batch->keterangan);
                } else {
                    $timePrefix = substr($batch->no_produksi, 0, strrpos($batch->no_produksi, '-'));
                    $subBatch = DB::table('produksi_batches')->where('no_produksi', $timePrefix . '-S')->first();
                    $subPartNo = $subBatch ? $subBatch->material_code : null;
                }
                if ($subPartNo) {
                    $subPartName = DB::table('parts')->where('part_no', $subPartNo)->value('part_name');
                }
            }

            $displayPartNo = $subPartNo ? "{$batch->material_code} / {$subPartNo}" : $batch->material_code;
            $displayPartName = $subPartName ? ($batch->part_name . " / " . $subPartName) : ($batch->part_name ?? $batch->material_code);

            // Ambil rincian setiap coil fisik yang terikat ke no_produksi ini
            $coilsUsed = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->where('rm_production_logs.no_produksi', $batch->no_produksi)
                ->select(
                    'rm_production_logs.pcs_used',
                    'rm_production_logs.rm_stock_id',
                    'rm_stocks.coil_id',
                    'rm_stocks.spec',
                    'rm_stocks.size',
                    'rm_stocks.material_name',
                    'rm_stocks.customer as stock_cust',
                    'rm_stocks.no_surat_jalan as stock_sj'
                )
                ->orderBy('rm_production_logs.id', 'asc')
                ->get();

            if ($coilsUsed->isNotEmpty()) {
                foreach ($coilsUsed as $c) {
                    $item = clone $batch;
                    $item->qty_ambil_pcs = $c->pcs_used;
                    $item->coil_id = $c->coil_id;
                    $item->spec = $c->spec;
                    $item->size = $c->size;
                    $item->material_name = $c->material_name;
                    $item->customer_code = $batch->part_cust ?? $c->stock_cust ?? 'SAI';
                    $item->display_part_no = $displayPartNo;
                    $item->display_part_name = $displayPartName;

                    if (!empty($c->stock_sj) && $c->stock_sj !== '-') {
                        $item->no_surat_jalan = $c->stock_sj;
                    } else {
                        $incomingSj = DB::table('rm_incoming_logs')
                            ->where('rm_stock_id', $c->rm_stock_id)
                            ->where('source', 'supplier')
                            ->orderBy('id', 'desc')
                            ->value('no_produksi');
                        $item->no_surat_jalan = $incomingSj ?? '-';
                    }

                    $allBatches->push($item);
                }
            } else {
                $stockData = DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->first();
                $item = clone $batch;
                $item->coil_id = $stockData->coil_id ?? '-';
                $item->spec = $stockData->spec ?? '-';
                $item->size = $stockData->size ?? '-';
                $item->customer_code = $batch->part_cust ?? $stockData->customer ?? 'SAI';
                $item->display_part_no = $displayPartNo;
                $item->display_part_name = $displayPartName;
                $item->no_surat_jalan = $stockData->no_surat_jalan ?? '-';
                $allBatches->push($item);
            }
        }

        $batchesS1 = $allBatches->filter(fn($b) => in_array($b->shift, ['Pagi', 'S1', 'Shift 1']))->values();
        $batchesS2 = $allBatches->filter(fn($b) => in_array($b->shift, ['Malam', 'S2', 'Shift 2']))->values();
        $batches = $allBatches;

        return view('PPIC.print_serah_terima', compact('batches', 'batchesS1', 'batchesS2', 'date', 'shift', 'line_code'));
    }

    /**
     * 7. QUALITY HUB KHUSUS STAMPING
     */
    public function qualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $sumStamping = DB::table('production_actuals')->whereDate('created_at', $date)->where('line_code', 'NOT LIKE', 'W-%')->where('line_code', '!=', 'WELDING AREA')->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))->first();
        $ngStamping = DB::table('production_ng_logs')->select('ng_type', DB::raw('SUM(qty) as total'))->whereDate('created_at', $date)->whereNotIn('ng_type', function($q) { $q->select('ng_name')->from('master_ngs')->where('category', 'WELDING'); })->groupBy('ng_type')->orderBy('total', 'DESC')->get();
        $detailStamping = DB::table('production_actuals')->whereDate('created_at', $date)->where('line_code', 'NOT LIKE', 'W-%')->where('line_code', '!=', 'WELDING AREA')->get();
        
        foreach($detailStamping as $d) {
            $d->batches = DB::table('produksi_batches')->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')->where('material_code', $d->part_no)->where('shift', $d->shift)->whereDate('produksi_batches.created_at', $date)->select('no_produksi', 'qty_ambil_pcs', 'qty_hasil_ok', 'qty_hasil_ng', 'kode_Line')->get();
        }
        return view('PPIC.quality_hub', compact('date', 'sumStamping', 'ngStamping', 'detailStamping'));
    }

    public function getBatchNGDetails($no_produksi)
    {
        $details = DB::table('production_ng_logs')->where('no_produksi', $no_produksi)->select('ng_type', 'qty')->get();
        if($details->isEmpty()){ $details = DB::table('welding_ng_logs')->where('no_produksi', $no_produksi)->select('ng_type', 'qty')->get(); }
        return response()->json($details);
    }

    /**
     * 8. WELDING INTELLIGENCE DASHBOARD
     */
    public function weldingIndex(Request $request)
    {
        $start_date = $request->start_date ?? date('Y-m-d');
        $end_date = $request->end_date ?? date('Y-m-d');

        $alerts = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.status', 'PROBLEM')
            ->select('welding_batches.*', 'line_welding.kode_line', 'welding_batches.updated_at as jam_lapor')
            ->get();

        $plans = DB::table('welding_plans')->whereBetween('plan_date', [$start_date, $end_date])->get();
        
        $chartLabels = []; $chartTargets = []; $chartActuals = [];
        foreach($plans as $p) {
            $actual = DB::table('welding_actuals')
                ->where('part_no', $p->part_no)
                ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])
                ->sum('qty_ok');
            
            $target = (int)($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            
            $chartLabels[] = $p->part_no;
            $chartTargets[] = $target;
            $chartActuals[] = (int)$actual;
        }

        $totalPlan = array_sum($chartTargets);
        $totalActual = array_sum($chartActuals);
        $totalNg = DB::table('welding_actuals')
                    ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])
                    ->sum('qty_ng');
        
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $dailyLabels = []; $dailyOk = []; $dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("$end_date -$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = (int)DB::table('welding_actuals')->whereDate('created_at', $d)->sum('qty_ok');
            $dailyNg[] = (int)DB::table('welding_actuals')->whereDate('created_at', $d)->sum('qty_ng');
        }

        $monthlyOk = []; $monthlyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("$end_date -$i days"));
            $monthlyOk[] = (int)DB::table('welding_actuals')->whereDate('created_at', $d)->sum('qty_ok');
        }

        return view('PPIC.welding_planning', compact(
            'plans', 'achievementRate', 'start_date', 'end_date', 'totalPlan', 'totalActual', 'totalNg', 
            'alerts', 'chartLabels', 'chartTargets', 'chartActuals', 'dailyLabels', 'dailyOk', 'dailyNg', 'monthlyOk'
        ));
    }

    /**
     * 9. WELDING MPS
     */
    public function weldingMps(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $shiftParam = $request->shift ?? 'S1'; 
        $dbShiftName = ($shiftParam == 'S1') ? 'Pagi' : 'Malam'; 

        $query = DB::table('welding_plans')->where('plan_date', $date);
        
        if ($shiftParam == 'S1') {
            $query->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0);
        } else {
            $query->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0);
        }

        $plans = $query->leftJoin('parts', 'welding_plans.part_no', '=', 'parts.part_no')
                       ->select('welding_plans.*', 'parts.part_name')
                       ->orderBy('line_code', 'asc')
                       ->orderBy('welding_plans.id', 'asc')
                       ->get();

        $totalPlanQty = 0;
        $totalWorkingHours = 0;
        $totalDandory = 0;
        $lineFinishTime = []; 
        $defaultStart = ($shiftParam == 'S1') ? "07:30" : "19:30";

        foreach($plans as $plan) {
            $actual = DB::table('welding_actuals')
                ->where('part_no', $plan->part_no)
                ->where('shift', $dbShiftName) 
                ->whereDate('created_at', $date)
                ->sum('qty_ok');

            $plan->total_actual = (int)$actual;
            $plan->total_target = ($shiftParam == 'S1') ? ($plan->s1_plan_reg + $plan->s1_plan_ot) : ($plan->s2_plan_reg + $plan->s2_plan_ot);
            $plan->balance = $plan->total_target - $plan->total_actual;

            $totalPlanQty += $plan->total_target;
            $totalDandory += ($plan->dandory_time ?? 15);

            $dandoryH = ($plan->dandory_time ?? 15) / 60;
            $duration = ($plan->cap_per_hour > 0 && $plan->total_target > 0) ? ($plan->total_target / $plan->cap_per_hour) + $dandoryH : 0;
            $totalWorkingHours += $duration;

            $startTime = $lineFinishTime[$plan->line_code] ?? $defaultStart;
            $plan->start_time = $startTime;
            $plan->ahir_time = date('H:i', strtotime($startTime . " + " . round($duration * 60) . " minutes"));
            $lineFinishTime[$plan->line_code] = $plan->ahir_time; 
        }

        $availableLines = DB::table('line_welding')->get();
        $availableParts = DB::table('parts')->where('next_process', 'WELDING')->get();

        return view('PPIC.welding_mps', compact(
            'plans', 'date', 'availableLines', 'availableParts', 
            'totalPlanQty', 'totalWorkingHours', 'totalDandory'
        ))->with('shift', $shiftParam);
    }

    /**
     * 9B. STORE WELDING MPS (SMART SHIFT ACCUMULATION & REVISI)
     */
    public function weldingMpsStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $input_s1 = (int)($request->s1_plan_reg ?? 0) + (int)($request->s1_plan_ot ?? 0);
            $input_s2 = (int)($request->s2_plan_reg ?? 0) + (int)($request->s2_plan_ot ?? 0);

            $existingPlan = DB::table('welding_plans')
                ->where('plan_date', $request->plan_date)
                ->where('part_no', $request->part_no)
                ->first();

            $partData = DB::table('parts')->where('part_no', $request->part_no)->first();
            $line = DB::table('line_welding')->where('kode_line', $request->line_code)->first();
            $line_id = $line ? $line->id : null;

            if ($existingPlan) {
                if ($input_s1 === 0 && (int)$existingPlan->s1_plan_reg > 0 && $input_s2 > 0 && (int)$existingPlan->s2_plan_reg === 0) {
                    $target_s1 = (int)$existingPlan->s1_plan_reg;
                    $target_s2 = $input_s2;
                } elseif ($input_s2 === 0 && (int)$existingPlan->s2_plan_reg > 0 && $input_s1 > 0 && (int)$existingPlan->s1_plan_reg === 0) {
                    $target_s1 = $input_s1;
                    $target_s2 = (int)$existingPlan->s2_plan_reg;
                } else {
                    $target_s1 = $input_s1;
                    $target_s2 = $input_s2;
                }

                $new_total = $target_s1 + $target_s2;
                $old_total = (int)$existingPlan->s1_plan_reg + (int)$existingPlan->s2_plan_reg;
                $selisih = $new_total - $old_total;

                if ($selisih > 0) {
                    $fg = DB::table('finished_goods')->where('part_no', $request->part_no)->first();
                    $wip_stock = $fg ? $fg->welding_stock : 0;
                    
                    if ($wip_stock < $selisih) {
                        throw new \Exception("❌ REVISI DITOLAK: STOK WIP WELDING KURANG! Butuh tambahan {$selisih} Pcs, sisa WIP cuma {$wip_stock} Pcs.");
                    }
                    
                    DB::table('finished_goods')->where('part_no', $request->part_no)->decrement('welding_stock', $selisih);
                    DB::table('production_logs')->insert([
                        'part_no' => $request->part_no, 
                        'qty' => $selisih, 
                        'process_type' => 'WELDING_OUT', 
                        'created_at' => now(), 
                        'updated_at' => now()
                    ]);
                } elseif ($selisih < 0) {
                    $returnQty = abs($selisih);
                    DB::table('finished_goods')->where('part_no', $request->part_no)->increment('welding_stock', $returnQty);
                    DB::table('production_logs')->insert([
                        'part_no' => $request->part_no, 
                        'qty' => $returnQty, 
                        'process_type' => 'WELDING_IN_RET', 
                        'created_at' => now(), 
                        'updated_at' => now()
                    ]);
                }

                if ($new_total == 0) {
                    DB::table('welding_plans')->where('id', $existingPlan->id)->delete();
                    DB::table('welding_batches')
                        ->where('part_no', $request->part_no)
                        ->whereDate('created_at', $request->plan_date)
                        ->where('status', '!=', 'COMPLETED')
                        ->delete();
                    $msg = 'Jadwal Welding DIBATALKAN (0). Stok WIP utuh dikembalikan ke Gudang.';
                } else {
                    DB::table('welding_plans')->where('id', $existingPlan->id)->update([
                        'line_code' => $request->line_code, 
                        'manpower' => $request->manpower ?? $existingPlan->manpower, 
                        'cap_per_hour' => $request->cap_per_hour ?? $existingPlan->cap_per_hour,
                        's1_plan_reg' => $target_s1, 
                        's1_plan_ot' => 0,
                        's2_plan_reg' => $target_s2, 
                        's2_plan_ot' => 0, 
                        'updated_at' => now()
                    ]);

                    if ($target_s1 > 0 && $line_id) {
                        $bS1 = DB::table('welding_batches')->where('part_no', $request->part_no)->where('shift', 'Pagi')->whereDate('created_at', $request->plan_date)->first();
                        if ($bS1) {
                            DB::table('welding_batches')->where('id', $bS1->id)->update(['qty_masuk' => $target_s1, 'line_id' => $line_id, 'updated_at' => now()]);
                        } else {
                            DB::table('welding_batches')->insert([
                                'no_produksi_stamping' => 'W-S1-' . date('His'), 'part_no' => $request->part_no,
                                'line_id' => $line_id, 'qty_masuk' => $target_s1, 'shift' => 'Pagi', 
                                'status' => 'PROSES', 'keterangan' => 'AUTO-DEPLOY', 
                                'created_at' => date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s'))), 'updated_at' => now()
                            ]);
                        }
                    } else {
                        DB::table('welding_batches')->where('part_no', $request->part_no)->where('shift', 'Pagi')->whereDate('created_at', $request->plan_date)->where('status', '!=', 'COMPLETED')->delete();
                    }

                    if ($target_s2 > 0 && $line_id) {
                        $bS2 = DB::table('welding_batches')->where('part_no', $request->part_no)->where('shift', 'Malam')->whereDate('created_at', $request->plan_date)->first();
                        if ($bS2) {
                            DB::table('welding_batches')->where('id', $bS2->id)->update(['qty_masuk' => $target_s2, 'line_id' => $line_id, 'updated_at' => now()]);
                        } else {
                            DB::table('welding_batches')->insert([
                                'no_produksi_stamping' => 'W-S2-' . date('His'), 'part_no' => $request->part_no,
                                'line_id' => $line_id, 'qty_masuk' => $target_s2, 'shift' => 'Malam', 
                                'status' => 'PROSES', 'keterangan' => 'AUTO-DEPLOY', 
                                'created_at' => date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s'))), 'updated_at' => now()
                            ]);
                        }
                    } else {
                        DB::table('welding_batches')->where('part_no', $request->part_no)->where('shift', 'Malam')->whereDate('created_at', $request->plan_date)->where('status', '!=', 'COMPLETED')->delete();
                    }

                    $msg = "Jadwal Shift berhasil disinkronkan! (Shift 1: {$target_s1} Pcs | Shift 2: {$target_s2} Pcs)";
                }
            } else {
                $total_target = $input_s1 + $input_s2;
                if ($total_target <= 0) {
                    throw new \Exception("Target produksi tidak boleh kosong / 0 untuk pembuatan jadwal baru.");
                }

                $fg = DB::table('finished_goods')->where('part_no', $request->part_no)->first();
                $wip_stock = $fg ? $fg->welding_stock : 0;

                if ($wip_stock < $total_target) {
                    throw new \Exception("❌ PLAN DITOLAK: STOK WIP WELDING KURANG! Sisa WIP: {$wip_stock} Pcs, Butuh: {$total_target} Pcs.");
                }

                DB::table('finished_goods')->where('part_no', $request->part_no)->decrement('welding_stock', $total_target);
                DB::table('production_logs')->insert([
                    'part_no' => $request->part_no, 
                    'qty' => $total_target, 
                    'process_type' => 'WELDING_OUT', 
                    'created_at' => date('Y-m-d H:i:s', strtotime($request->plan_date . ' 08:00:00')),
                    'updated_at' => now()
                ]);

                DB::table('welding_plans')->insert([
                    'plan_date' => $request->plan_date, 'part_no' => $request->part_no, 'customer_code' => $partData->customer_code ?? 'UNK',
                    'line_code' => $request->line_code, 'manpower' => $request->manpower ?? 1, 'cap_per_hour' => $request->cap_per_hour ?? 0,
                    's1_plan_reg' => $input_s1, 's1_plan_ot' => 0, 's2_plan_reg' => $input_s2, 's2_plan_ot' => 0, 
                    'dandory_time' => 15, 'process_qty' => 1, 'qty_lot' => 1, 'created_at' => now(), 'updated_at' => now()
                ]);

                if ($input_s1 > 0 && $line_id) {
                    DB::table('welding_batches')->insert([
                        'no_produksi_stamping' => 'W-S1-' . date('His'),
                        'part_no' => $request->part_no,
                        'line_id' => $line_id, 
                        'qty_masuk' => $input_s1, 
                        'shift' => 'Pagi', 
                        'status' => 'PROSES',
                        'keterangan' => 'AUTO-DEPLOY',
                        'created_at' => date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s'))), 
                        'updated_at' => now()
                    ]);
                }

                if ($input_s2 > 0 && $line_id) {
                    DB::table('welding_batches')->insert([
                        'no_produksi_stamping' => 'W-S2-' . date('His'),
                        'part_no' => $request->part_no,
                        'line_id' => $line_id, 
                        'qty_masuk' => $input_s2, 
                        'shift' => 'Malam', 
                        'status' => 'PROSES',
                        'keterangan' => 'AUTO-DEPLOY',
                        'created_at' => date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s'))), 
                        'updated_at' => now()
                    ]);
                }

                $msg = 'Welding Plan Deployed! Barang tercatat di Terminal sebagai OUT.';
            }

            DB::commit();
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 10. RESUME & CLOSE BATCH STAMPING (LAMA)
     */
    public function resumeBatch($id) 
    { 
        DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROSES', 'updated_at' => now()]); 
        return redirect()->back()->with('success', 'Batch resumed.'); 
    }

    public function closeBatch($id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('produksi_batches')->where('id', $id)->first();
            if (!$batch) return redirect()->back()->with('error', 'Batch tidak ditemukan.');

            if ($batch->status === 'COMPLETED') {
                throw new \Exception("❌ Batch ini sudah di-Close! Sistem menolak proses ganda agar data tidak terduplikasi.");
            }

            $is_separating_sub = str_ends_with($batch->no_produksi, '-S');

            if (!$is_separating_sub) {
                if ((int)$batch->qty_return_warehouse > 0) {
                    $sisa = (int)$batch->qty_return_warehouse;
                } else {
                    $cavity = $batch->cavity > 0 ? $batch->cavity : 1;
                    $totalPcsProduced = (int)$batch->qty_hasil_ok + (int)$batch->qty_hasil_ng;
                    $lembarTerpakai = ceil($totalPcsProduced / $cavity);
                    $sisa = max(0, (int)$batch->qty_ambil_pcs - $lembarTerpakai);
                }

                if ($sisa > 0 && $batch->rm_stock_id > 0) {
                    $stockRef = DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->first();
                    if ($stockRef) {
                        DB::table('rm_stocks')->where('coil_id', trim($stockRef->coil_id))->increment('stock_pcs', $sisa);
                    } else {
                        DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->increment('stock_pcs', $sisa);
                    }
                    
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id' => $batch->rm_stock_id, 'material_code' => $batch->material_code,
                        'pcs_in' => $sisa, 'source' => 'return', 'no_produksi' => $batch->no_produksi, 'created_at' => now()
                    ]);
                }
            } else {
                $sisa = 0; 
            }

            if ((int)$batch->qty_hasil_ng > 0) {
                $hasNgLog = DB::table('production_ng_logs')->where('no_produksi', $batch->no_produksi)->exists();
                if (!$hasNgLog) {
                    $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
                    DB::table('production_ng_logs')->insert([
                        'no_produksi' => $batch->no_produksi, 'part_no' => $batch->material_code, 'line_code' => $lineCode,
                        'shift' => $batch->shift, 'ng_type' => 'PROD_DEFECT', 'qty' => (int)$batch->qty_hasil_ng,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }

            DB::table('produksi_batches')->where('id', $id)->update(['status' => 'COMPLETED', 'qty_return_warehouse' => $sisa, 'updated_at' => now()]);

            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();
            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('welding_stock', (int)$batch->qty_hasil_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert(['part_no' => $batch->material_code, 'qty' => (int)$batch->qty_hasil_ok, 'process_type' => 'WELDING', 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('finished_goods')->where('part_no', $batch->material_code)->increment('actual_stock', (int)$batch->qty_hasil_ok, ['updated_at' => now()]);
                DB::table('production_logs')->insert(['part_no' => $batch->material_code, 'qty' => (int)$batch->qty_hasil_ok, 'process_type' => 'STAMPING', 'created_at' => now(), 'updated_at' => now()]);
            }

            $this->syncToActual($id);
            DB::commit();
            
            $msg = $is_separating_sub ? "Batch Separating Closed! Barang jadi ditambahkan." : "Batch Closed! {$sisa} Lembar dicatat return ke Gudang RM.";
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses penutupan: ' . $e->getMessage());
        }
    }

    private function syncToActual($batchId) 
    {
        $batch = DB::table('produksi_batches')->where('id', $batchId)->first(); 
        if (!$batch) return;
        $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
        $actual = DB::table('production_actuals')->where('part_no', $batch->material_code)->where('shift', $batch->shift)->whereDate('created_at', date('Y-m-d', strtotime($batch->created_at)))->first();
        if ($actual) { 
            DB::table('production_actuals')->where('id', $actual->id)->update(['qty_ok' => $actual->qty_ok + $batch->qty_hasil_ok, 'qty_ng' => $actual->qty_ng + $batch->qty_hasil_ng, 'updated_at' => now()]); 
        } else { 
            DB::table('production_actuals')->insert(['part_no' => $batch->material_code, 'line_code' => $lineCode, 'shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'created_at' => $batch->created_at, 'updated_at' => now()]); 
        }
    }

    /**
     * 10B. RESUME & CLOSE BATCH WELDING DARI TERMINAL
     */
    public function resumeBatchWelding($id) 
    { 
        DB::table('welding_batches')->where('id', $id)->update(['status' => 'PROSES', 'updated_at' => now()]); 
        return redirect()->back()->with('success', 'Batch Welding resumed.'); 
    }

    public function closeBatchWelding(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('welding_batches')->where('id', $id)->first();
            if (!$batch) throw new \Exception("Batch tidak ditemukan.");
            if ($batch->status === 'COMPLETED') throw new \Exception("❌ Batch ini sudah ditutup!");

            $qty_ok = $request->has('qty_ok') ? (int) $request->qty_ok : (int) $batch->qty_ok;
            $qty_ng = $request->has('qty_ng') ? (int) $request->qty_ng : (int) $batch->qty_ng;
            $qty_return = $request->has('qty_return') ? (int) $request->qty_return : (int) $batch->qty_return;
            $notes = $request->has('notes') ? $request->notes : $batch->keterangan;

            $total_input = $qty_ok + $qty_ng + $qty_return;
            if ($request->has('qty_ok') && $total_input > (int)$batch->qty_masuk) {
                throw new \Exception("Total (OK + NG + RETURN) melebihi jumlah material yang di-Take! ($total_input > $batch->qty_masuk)");
            }

            DB::table('welding_batches')->where('id', $id)->update([
                'qty_ok' => $qty_ok,
                'qty_ng' => $qty_ng,
                'qty_return' => $qty_return,
                'keterangan' => $notes,
                'status' => 'COMPLETED',
                'updated_at' => now()
            ]);

            if ($qty_return > 0) {
                DB::table('finished_goods')->where('part_no', $batch->part_no)->increment('welding_stock', $qty_return);
                DB::table('production_logs')->insert([
                    'part_no' => $batch->part_no, 
                    'qty' => $qty_return, 
                    'process_type' => 'WELDING_IN_RET', 
                    'created_at' => now(), 
                    'updated_at' => now()
                ]);
            }

            if ($qty_ok > 0) {
                DB::table('finished_goods')->where('part_no', $batch->part_no)->increment('actual_stock', $qty_ok);
                DB::table('production_logs')->insert([
                    'part_no' => $batch->part_no, 
                    'qty' => $qty_ok, 
                    'process_type' => 'WELDING_IN', 
                    'created_at' => now(), 
                    'updated_at' => now()
                ]);
            }

            $actual = DB::table('welding_actuals')->where('part_no', $batch->part_no)->where('shift', $batch->shift)->whereDate('created_at', date('Y-m-d', strtotime($batch->created_at)))->first();
            if ($actual) { 
                DB::table('welding_actuals')->where('id', $actual->id)->update([
                    'qty_ok' => $actual->qty_ok + $qty_ok, 'qty_ng' => $actual->qty_ng + $qty_ng, 'updated_at' => now()
                ]); 
            } else { 
                $lineCode = DB::table('line_welding')->where('id', $batch->line_id)->value('kode_line') ?? 'UNKNOWN';
                DB::table('welding_actuals')->insert([
                    'part_no' => $batch->part_no, 'line_code' => $lineCode, 'shift' => $batch->shift, 
                    'qty_ok' => $qty_ok, 'qty_ng' => $qty_ng, 'created_at' => $batch->created_at, 'updated_at' => now()
                ]); 
            }

            if ($qty_ng > 0) {
                $lineCode = DB::table('line_welding')->where('id', $batch->line_id)->value('kode_line') ?? 'UNKNOWN';
                if ($request->has('ng_type') && $request->has('ng_qty')) {
                    foreach($request->ng_type as $index => $type) {
                        $qty_defect = (int) $request->ng_qty[$index];
                        if($qty_defect > 0) {
                            DB::table('welding_ng_logs')->insert([
                                'no_produksi' => $batch->no_produksi_stamping, 'part_no' => $batch->part_no, 'line_code' => $lineCode, 
                                'shift' => $batch->shift, 'ng_type' => $type, 'qty' => $qty_defect, 'created_at' => now(), 'updated_at' => now()
                            ]);
                        }
                    }
                } elseif ($request->has('qty_ok')) {
                    DB::table('welding_ng_logs')->insert([
                        'no_produksi' => $batch->no_produksi_stamping, 'part_no' => $batch->part_no, 'line_code' => $lineCode, 
                        'shift' => $batch->shift, 'ng_type' => 'UNCATEGORIZED_DEFECT', 'qty' => $qty_ng, 'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "✅ Batch Welding berhasil ditutup! (Return $qty_return Pcs dikembalikan ke Gudang WIP)");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses penutupan: ' . $e->getMessage());
        }
    }

    /**
     * 11. PRINT WOS WELDING (GABUNG SHIFT 1 & 2)
     */
    public function printWosWelding($date, $line_code)
    {
        $plansS1 = DB::table('welding_plans')
            ->leftJoin('parts', 'welding_plans.part_no', '=', 'parts.part_no')
            ->where('welding_plans.plan_date', $date)
            ->where('welding_plans.line_code', $line_code)
            ->where(DB::raw('welding_plans.s1_plan_reg + welding_plans.s1_plan_ot'), '>', 0)
            ->select('welding_plans.*', 'parts.part_name')
            ->orderBy('welding_plans.id', 'asc')
            ->get();

        $plansS2 = DB::table('welding_plans')
            ->leftJoin('parts', 'welding_plans.part_no', '=', 'parts.part_no')
            ->where('welding_plans.plan_date', $date)
            ->where('welding_plans.line_code', $line_code)
            ->where(DB::raw('welding_plans.s2_plan_reg + welding_plans.s2_plan_ot'), '>', 0)
            ->select('welding_plans.*', 'parts.part_name')
            ->orderBy('welding_plans.id', 'asc')
            ->get();

        if ($plansS1->isEmpty() && $plansS2->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal Welding di mesin ini untuk dicetak.');
        }

        $parts = collect()->merge($plansS1)->merge($plansS2)->pluck('part_no')->unique()->toArray();
        $batches = DB::table('welding_batches')
            ->whereIn('part_no', $parts)
            ->whereDate('created_at', $date)
            ->get();

        return view('PPIC.print_wos_welding', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * 12. PRINT SERAH TERIMA WELDING (WIP)
     */
    public function printSerahTerimaWelding($date, $line_code)
    {
        $plans = DB::table('welding_plans')->where('plan_date', $date)->where('line_code', $line_code)->get();
        if ($plans->isEmpty()) return redirect()->back()->with('error', 'Belum ada Material Request (WIP) untuk mesin ini.');

        $parts = $plans->pluck('part_no')->toArray();

        $batches = DB::table('welding_batches')
            ->leftJoin('parts', 'welding_batches.part_no', '=', 'parts.part_no')
            ->whereIn('welding_batches.part_no', $parts)
            ->whereDate('welding_batches.created_at', $date)
            ->select('welding_batches.*', 'parts.part_name')
            ->orderBy('welding_batches.shift', 'desc')->get();

        return view('PPIC.print_serah_terima_welding', compact('batches', 'date', 'line_code'));
    }
}