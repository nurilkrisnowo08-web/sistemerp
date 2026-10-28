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
        $date =$request->date ?? date('Y-m-d');
        $today = date('Y-m-d');$alerts = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->where('produksi_batches.status', 'PROBLEM')
            ->select('produksi_batches.id', 'produksi_batches.no_produksi', 'produksi_batches.material_code', 'line.kode_Line', 'produksi_batches.keterangan', 'produksi_batches.updated_at')
            ->get();

        $plans = DB::table('production_plans')->where('plan_date',$date)->get();
        $statusCount = ['waiting' => 0, 'running' => 0, 'completed' => 0, 'shortage' => 0];$chartLabels = []; $chartTargets = [];$chartActuals = [];

        foreach($plans as$p) {
            $targetPerPart = ($p->s1_plan_reg + $p->s1_plan_ot +$p->s2_plan_reg + $p->s2_plan_ot);$actualPerPart = DB::table('production_actuals')
                ->where('part_no', $p->part_no)
                ->whereDate('created_at', $date)
                ->where('line_code', '!=', 'WELDING AREA')
                ->sum('qty_ok');
            
            $p->actual_qty = (int)$actualPerPart;
            $p->plan_qty = (int)$targetPerPart;
            $chartLabels[] =$p->part_no;
            $chartTargets[] = (int)$targetPerPart;
            $chartActuals[] = (int)$actualPerPart;

            $batchStatuses = DB::table('produksi_batches')->where('plan_id', $p->id)->pluck('status');$isAllBatchCompleted = $batchStatuses->isNotEmpty() &&$batchStatuses->every(fn($st) =>$st === 'COMPLETED');

            if ($isAllBatchCompleted || ($targetPerPart > 0 &&$actualPerPart >= $targetPerPart)) {$statusCount['completed']++; 
            }
            elseif ($date < $today &&$actualPerPart < $targetPerPart) {$statusCount['shortage']++; 
            }
            elseif ($actualPerPart > 0) {$statusCount['running']++; 
            }
            else { 
                $statusCount['waiting']++; 
            }
        }

        $totalPlan =$plans->sum('plan_qty') ?: 0;
        $totalActual =$plans->sum('actual_qty') ?: 0;
        $achievementRate =$totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $dailyLabels = []; $dailyOk = [];$dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = DB::table('production_actuals')->whereDate('created_at',$d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok');
            $dailyNg[] = DB::table('production_actuals')->whereDate('created_at',$d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ng');
        }

        $monthlyLabels = []; $monthlyOk = [];$monthlyNg = [];
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
     * 2. DAILY MPS KHUSUS BIG PRESS (LINE A, LINE B)
     */
    public function mpsIndex(Request $request)
    {
        $date =$request->date ?? date('Y-m-d');

        $availableLines = DB::table('line')
            ->where('kode_Line', 'NOT LIKE', '%C%')
            ->where('kode_Line', 'NOT LIKE', '%SMALL%')
            ->get();
        $bigLineCodes =$availableLines->pluck('kode_Line')->toArray();

        $allPlans = DB::table('production_plans')
            ->where('plan_date', $date)
            ->whereIn('line_code', $bigLineCodes)
            ->orderBy('line_code', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalPlanQty = 0;
        $totalWorkingHoursS1 = 0; 
        $totalWorkingHoursS2 = 0; 
        $totalDandory = 0;

        $processedPlans = collect();
        $lineFinishTimeS1 = [];$lineFinishTimeS2 = [];

        foreach($allPlans as$p) {
            $cekSub = DB::table('produksi_batches')->where('plan_id',$p->id)->where('no_produksi', 'LIKE', '%-S')->exists();
            if ($cekSub) continue;

            $t_s1 = $p->s1_plan_reg +$p->s1_plan_ot;
            $t_s2 = $p->s2_plan_reg +$p->s2_plan_ot;
            $dandory =$p->dandory_time ?? 15;

            if ($t_s1 > 0) {$actualS1 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Pagi')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s1 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS1[$p->line_code] ?? "07:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS1[$p->line_code] =$finish;

                $item = clone$p; 
                $item->display_shift = 'S1';
                
                $bS1 = DB::table('produksi_batches')->where('plan_id',$p->id)->where('shift', 'Pagi')->first();
                $item->no_produksi =$bS1->no_produksi ?? '-';

                $item->total_target =$t_s1;
                $item->total_actual = (int)$actualS1;
                $item->balance = $t_s1 -$actualS1;
                $item->start_time =$start;
                $item->ahir_time =$finish;
                $processedPlans->push($item);

                $totalPlanQty +=$t_s1;
                $totalWorkingHoursS1 +=$dur; 
                $totalDandory +=$dandory;
            }

            if ($t_s2 > 0) {$actualS2 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Malam')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s2 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS2[$p->line_code] ?? "19:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS2[$p->line_code] =$finish;

                $item = clone$p; 
                $item->display_shift = 'S2';

                $bS2 = DB::table('produksi_batches')->where('plan_id',$p->id)->where('shift', 'Malam')->first();
                $item->no_produksi =$bS2->no_produksi ?? '-';

                $item->total_target =$t_s2;
                $item->total_actual = (int)$actualS2;
                $item->balance = $t_s2 -$actualS2;
                $item->start_time =$start;
                $item->ahir_time =$finish;
                $processedPlans->push($item);

                $totalPlanQty +=$t_s2;
                $totalWorkingHoursS2 +=$dur; 
                $totalDandory +=$dandory;
            }
        }

        $groupedPlans = $processedPlans->groupBy('line_code');$availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();$availableParts = DB::table('parts')->select('part_no', 'part_name', 'customer_code')->orderBy('part_no', 'asc')->get();

        return view('PPIC.mps_index', compact(
            'groupedPlans', 'date', 'availableLines', 'availableCustomers', 'availableParts',
            'totalPlanQty', 'totalWorkingHoursS1', 'totalWorkingHoursS2', 'totalDandory'
        ));
    }

    /**
     * 2B. DAILY MPS KHUSUS SMALL PRESS (LINE C, SMALL PRESS)
     */
    public function mpsSmallIndex(Request $request)
    {
        $date =$request->date ?? date('Y-m-d');

        $availableLines = DB::table('line')
            ->where(function($q) {$q->where('kode_Line', 'LIKE', '%C%')
                  ->orWhere('kode_Line', 'LIKE', '%SMALL%')
                  ->orWhere('nama_Line', 'LIKE', '%SMALL%');
            })
            ->get();
        $smallLineCodes =$availableLines->pluck('kode_Line')->toArray();

        $allPlans = DB::table('production_plans')
            ->where('plan_date', $date)
            ->whereIn('line_code', $smallLineCodes)
            ->orderBy('line_code', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalPlanQty = 0;
        $totalWorkingHoursS1 = 0; 
        $totalWorkingHoursS2 = 0; 
        $totalDandory = 0;

        $processedPlans = collect();
        $lineFinishTimeS1 = [];$lineFinishTimeS2 = [];

        foreach($allPlans as$p) {
            $cekSub = DB::table('produksi_batches')->where('plan_id',$p->id)->where('no_produksi', 'LIKE', '%-S')->exists();
            if ($cekSub) continue;

            $t_s1 = $p->s1_plan_reg +$p->s1_plan_ot;
            $t_s2 = $p->s2_plan_reg +$p->s2_plan_ot;
            $dandory =$p->dandory_time ?? 15;

            if ($t_s1 > 0) {$actualS1 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Pagi')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s1 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS1[$p->line_code] ?? "07:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS1[$p->line_code] =$finish;

                $item = clone$p; 
                $item->display_shift = 'S1';

                $bS1 = DB::table('produksi_batches')->where('plan_id',$p->id)->where('shift', 'Pagi')->first();
                $item->no_produksi =$bS1->no_produksi ?? '-';

                $item->total_target =$t_s1;
                $item->total_actual = (int)$actualS1;
                $item->balance = $t_s1 -$actualS1;
                $item->start_time =$start;
                $item->ahir_time =$finish;
                $processedPlans->push($item);

                $totalPlanQty +=$t_s1;
                $totalWorkingHoursS1 +=$dur; 
                $totalDandory +=$dandory;
            }

            if ($t_s2 > 0) {$actualS2 = DB::table('production_actuals')
                    ->where('part_no', $p->part_no)->where('shift', 'Malam')
                    ->whereDate('created_at', $date)->where('line_code', '!=', 'WELDING AREA')
                    ->sum('qty_ok');

                $dur = ($p->cap_per_hour > 0) ? ($t_s2 / $p->cap_per_hour) + ($dandory / 60) : 0;
                $start = $lineFinishTimeS2[$p->line_code] ?? "19:30";
                $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes"));
                $lineFinishTimeS2[$p->line_code] =$finish;

                $item = clone$p; 
                $item->display_shift = 'S2';

                $bS2 = DB::table('produksi_batches')->where('plan_id',$p->id)->where('shift', 'Malam')->first();
                $item->no_produksi =$bS2->no_produksi ?? '-';

                $item->total_target =$t_s2;
                $item->total_actual = (int)$actualS2;
                $item->balance = $t_s2 -$actualS2;
                $item->start_time =$start;
                $item->ahir_time =$finish;
                $processedPlans->push($item);

                $totalPlanQty +=$t_s2;
                $totalWorkingHoursS2 +=$dur; 
                $totalDandory +=$dandory;
            }
        }

        $groupedPlans =$processedPlans->groupBy('line_code');
        $availableCustomers = DB::table('customers')->get();$availableParts = DB::table('parts')->select('part_no', 'part_name', 'customer_code')->orderBy('part_no', 'asc')->get();

        return view('PPIC.small_mps', compact(
            'groupedPlans', 'date', 'availableLines', 'availableCustomers', 'availableParts',
            'totalPlanQty', 'totalWorkingHoursS1', 'totalWorkingHoursS2', 'totalDandory'
        ));
    }

    /**
     * 2C. AJAX HELPER: GET PARTS BY CUSTOMER
     */
    public function getPartsByCustomer(Request $request,$customer = null)
    {
        $cust =$customer ?? $request->customer_code ?? $request->customer;
        $parts = DB::table('parts')
            ->where('customer_code', trim($cust))
            ->select('part_no', 'part_name', 'customer_code')
            ->orderBy('part_no', 'asc')
            ->get();

        return response()->json($parts);
    }

    /**
     * 3. STORE MPS STAMPING
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

            $cleanPart = trim(urldecode($request->part_no));
            if (strpos($cleanPart, ' - ') !== false) {
                $cleanPart = trim(explode(' - ', $cleanPart)[0]);
            }

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
                throw new \Exception("Material untuk Part No [{$cleanPart}] kosong atau tidak terdaftar!");
            }

            $firstCoil = $availableCoils->first();
            $cavity = $firstCoil->cavity > 0 ? (int)$firstCoil->cavity : 1;
            
            $kebutuhan_lembar = (int) ceil($total_target / $cavity);
            $total_stok_tersedia = (int) $availableCoils->sum('stock_pcs');

            if ($total_stok_tersedia < $kebutuhan_lembar) {
                throw new \Exception("❌ PLAN DITOLAK: TOTAL STOK KURANG! Butuh {$kebutuhan_lembar} Lembar, total dari seluruh bundle hanya {$total_stok_tersedia} Lembar.");
            }

            $logTimestamp = date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s')));

            $planIdMain = DB::table('production_plans')->insertGetId([
                'plan_date'     => $request->plan_date, 
                'part_no'       => $cleanPart, 
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
                'created_at'    => $logTimestamp, 
                'updated_at'    => now()
            ]);

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
                    'created_at'    => $logTimestamp, 
                    'updated_at'    => now()
                ]);
            }

            $mesin = DB::table('line')->where('kode_Line', $request->line_code)->first();
            $mesin_id = $mesin ? $mesin->id : null;

            $qty_lembar_s1 = $target_s1 > 0 ? (int) ceil($target_s1 / $cavity) : 0;
            $qty_lembar_s2 = max(0, $kebutuhan_lembar - $qty_lembar_s1);

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

            if ($qty_lembar_s1 > 0 && $mesin_id) {
                $base_prod_s1 = 'WOS-S1-' . date('YmdHis') . $main_suffix;
                $remS1 = $qty_lembar_s1;

                foreach ($coilPool as $cKey => &$cData) {
                    if ($remS1 <= 0) break;
                    if ($cData['stock'] <= 0) continue;

                    $take = min($cData['stock'], $remS1);
                    $cData['stock'] -= $take;
                    $remS1 -= $take;

                    DB::table('rm_stocks')->where('coil_id', trim($cData['coil_id']))->decrement('stock_pcs', $take);
                    DB::table('rm_production_logs')->insert([
                        'rm_stock_id'   => $cData['id'],
                        'material_code' => $cleanPart,
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
                    'material_code' => $cleanPart,
                    'qty_ambil_pcs' => $qty_lembar_s1,
                    'cavity'        => $cavity,
                    'status'        => 'PROSES',
                    'keterangan'    => $ket,
                    'created_at'    => $logTimestamp,
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
                        'created_at'    => $logTimestamp,
                        'updated_at'    => now()
                    ]);
                }
            }

            if ($qty_lembar_s2 > 0 && $mesin_id) {
                $base_prod_s2 = 'WOS-S2-' . date('YmdHis', strtotime('+1 second')) . $main_suffix;
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
                        'material_code' => $cleanPart,
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
                    'material_code' => $cleanPart,
                    'qty_ambil_pcs' => $qty_lembar_s2,
                    'cavity'        => $cavity,
                    'status'        => 'PROSES',
                    'keterangan'    => $ket,
                    'created_at'    => $logTimestamp,
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
                        'created_at'    => $logTimestamp,
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

            $sampleBatch = $existingBatches->first();
            $suffix = '';
            
            $baseNoProdS1 = 'WOS-S1-REV-' . date('YmdHis');
            $baseNoProdS2 = 'WOS-S2-REV-' . date('YmdHis', strtotime('+1 sec'));

            if ($sampleBatch) {
                if (str_ends_with($sampleBatch->no_produksi, '-M')) $suffix = '-M';
                if (str_ends_with($sampleBatch->no_produksi, '-S')) $suffix = '-S';
                
                $b1 = $existingBatches->where('shift', 'Pagi')->first();
                if ($b1) $baseNoProdS1 = $b1->no_produksi;
                
                $b2 = $existingBatches->where('shift', 'Malam')->first();
                if ($b2) $baseNoProdS2 = $b2->no_produksi;
            }
            $is_separating_sub = ($suffix === '-S');

            $revCount = 1;
            if ($sampleBatch && preg_match('/\(R(\d+)\)/', $sampleBatch->keterangan, $matches)) {
                $revCount = (int)$matches[1] + 1;
            }
            $keteranganRev = "DIREVISI PPIC (R{$revCount})";

            $old_target_s1 = $plan->s1_plan_reg + $plan->s1_plan_ot;
            $old_target_s2 = $plan->s2_plan_reg + $plan->s2_plan_ot;
            $old_total = $old_target_s1 + $old_target_s2;

            $new_target_s1 = (int)($request->s1_plan_reg ?? 0) + (int)($request->s1_plan_ot ?? 0);
            $new_target_s2 = (int)($request->s2_plan_reg ?? 0) + (int)($request->s2_plan_ot ?? 0);
            $new_total = $new_target_s1 + $new_target_s2;

            $logTimestamp = date('Y-m-d H:i:s', strtotime($plan->plan_date . ' ' . date('H:i:s')));
            $cleanPart = trim($plan->part_no);

            $availableStocks = DB::table('rm_stocks')
                ->where(function($q) use ($cleanPart) {
                    $q->where('material_code', $cleanPart)
                      ->orWhere('material_name', $cleanPart)
                      ->orWhereRaw("REPLACE(material_code, ' ', '') = ?", [str_replace(' ', '', $cleanPart)]);
                })
                ->get()
                ->unique('coil_id');

            if ($availableStocks->isEmpty()) {
                $refStock = DB::table('rm_stocks')->where('material_code', $cleanPart)->first();
                if ($refStock) {
                    $availableStocks = DB::table('rm_stocks')
                        ->where('customer', trim($refStock->customer))
                        ->where(DB::raw('TRIM(spec)'), trim($refStock->spec))
                        ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $refStock->size))
                        ->get()
                        ->unique('coil_id');
                }
            }
            $firstStock = $availableStocks->first();

            // 1. REVISI JADI 0
            if ($new_total <= 0) {
                if (!$is_separating_sub) {
                    $batchNumbers = $existingBatches->pluck('no_produksi')->toArray();
                    $usedLogs = DB::table('rm_production_logs')->whereIn('no_produksi', $batchNumbers)->get();

                    if ($usedLogs->isNotEmpty()) {
                        foreach ($usedLogs as $log) {
                            $refCoil = DB::table('rm_stocks')->where('id', $log->rm_stock_id)->first();
                            if ($refCoil) {
                                DB::table('rm_stocks')->whereRaw("REPLACE(coil_id, ' ', '') = ?", [str_replace(' ', '', $refCoil->coil_id)])->increment('stock_pcs', $log->pcs_used);
                                DB::table('rm_incoming_logs')->insert([
                                    'rm_stock_id'   => $refCoil->id, 'material_code' => $plan->part_no, 'pcs_in' => $log->pcs_used,
                                    'source'        => 'return', 'no_produksi' => 'REV-CANCEL-' . date('YmdHis'), 'created_at' => $logTimestamp
                                ]);
                            }
                        }
                        DB::table('rm_production_logs')->whereIn('no_produksi', $batchNumbers)->delete();
                    } else {
                        foreach ($existingBatches as $b) {
                            if ($b->qty_ambil_pcs > 0 && $b->rm_stock_id > 0) {
                                $refCoil = DB::table('rm_stocks')->where('id', $b->rm_stock_id)->first();
                                if ($refCoil) {
                                    DB::table('rm_stocks')->whereRaw("REPLACE(coil_id, ' ', '') = ?", [str_replace(' ', '', $refCoil->coil_id)])->increment('stock_pcs', $b->qty_ambil_pcs);
                                    DB::table('rm_incoming_logs')->insert([
                                        'rm_stock_id'   => $refCoil->id, 'material_code' => $plan->part_no, 'pcs_in' => $b->qty_ambil_pcs,
                                        'source'        => 'return', 'no_produksi' => 'REV-CANCEL-' . date('YmdHis'), 'created_at' => $logTimestamp
                                    ]);
                                }
                            }
                        }
                    }
                }

                foreach ($existingBatches as $eb) {
                    if (str_ends_with($eb->no_produksi, '-M')) {
                        $timePrefix = substr($eb->no_produksi, 0, strrpos($eb->no_produksi, '-'));
                        $subBatch = DB::table('produksi_batches')->where('no_produksi', $timePrefix . '-S')->first();
                        if ($subBatch) {
                            DB::table('production_plans')->where('id', $subBatch->plan_id)->delete();
                            DB::table('produksi_batches')->where('id', $subBatch->id)->delete();
                        }
                    }
                }

                DB::table('produksi_batches')->where('plan_id', $id)->delete();
                DB::table('production_plans')->where('id', $id)->delete();

                DB::commit();
                return redirect()->back()->with('success', "✅ Jadwal Berhasil Dibatalkan (Target 0). Seluruh lembar material dikembalikan ke Gudang RM!");
            }

            // 2. REVISI BERUBAH JUMLAH
            if (!$is_separating_sub) {
                if (!$firstStock) throw new \Exception("Database Material untuk part [{$plan->part_no}] tidak terdeteksi!");

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
                        DB::table('rm_production_logs')->insert(['rm_stock_id' => $stk->id, 'material_code' => $plan->part_no, 'pcs_used' => $take, 'no_produksi' => $baseNoProdS1, 'created_at' => $logTimestamp]);
                        $remAdd -= $take;
                    }
                } elseif ($selisih_lembar < 0) {
                    $raw_return = abs($selisih_lembar);
                    $remReturn = $raw_return;
                    $usedLogs = DB::table('rm_production_logs')->whereIn('no_produksi', $existingBatches->pluck('no_produksi'))->orderBy('id', 'desc')->get();

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

                    if ($remReturn > 0 && $firstStock) {
                        DB::table('rm_stocks')->where('coil_id', trim($firstStock->coil_id))->increment('stock_pcs', $remReturn);
                        DB::table('rm_incoming_logs')->insert(['rm_stock_id' => $firstStock->id, 'material_code' => $plan->part_no, 'pcs_in' => $remReturn, 'source' => 'return', 'no_produksi' => 'REV-RTN-' . date('YmdHis'), 'created_at' => $logTimestamp]);
                    }
                }
                $rm_stock_id = $firstStock->id;
            } else {
                $cavity = 1; $rm_stock_id = 0; $selisih_lembar = 0;
            }

            DB::table('production_plans')->where('id', $id)->update([
                's1_plan_reg'  => $request->s1_plan_reg ?? 0, 's1_plan_ot'   => $request->s1_plan_ot ?? 0,
                's2_plan_reg'  => $request->s2_plan_reg ?? 0, 's2_plan_ot'   => $request->s2_plan_ot ?? 0,
                'cap_per_hour' => $request->cap_per_hour ?? $plan->cap_per_hour, 'dandory_time' => $request->dandory_time ?? $plan->dandory_time, 
                'updated_at'   => now()
            ]);

            $qty_lembar_s1 = $is_separating_sub ? 0 : ceil($new_target_s1 / $cavity);
            $qty_lembar_s2 = $is_separating_sub ? 0 : max(0, ceil($new_total / $cavity) - $qty_lembar_s1);
            
            $mesin = DB::table('line')->where('kode_Line', $request->line_code ?? $plan->line_code)->first();
            $mesin_id = $mesin ? $mesin->id : ($sampleBatch ? $sampleBatch->mesin_id : null);

            $bS1 = $existingBatches->where('shift', 'Pagi')->first();
            if ($new_target_s1 > 0) {
                if ($bS1) {
                    DB::table('produksi_batches')->where('id', $bS1->id)->update([
                        'qty_ambil_pcs' => $qty_lembar_s1, 'keterangan' => $keteranganRev, 'mesin_id' => $mesin_id, 'updated_at' => now()
                    ]);
                } else {
                    DB::table('produksi_batches')->insert([
                        'no_produksi' => $baseNoProdS1, 'plan_id' => $id, 'shift' => 'Pagi', 'mesin_id' => $mesin_id,
                        'rm_stock_id' => $rm_stock_id, 'material_code' => $plan->part_no, 'qty_ambil_pcs' => $qty_lembar_s1, 'cavity' => $cavity,
                        'status' => 'PROSES', 'keterangan' => $keteranganRev, 'created_at' => $logTimestamp, 'updated_at' => now()
                    ]);
                }
            } else {
                if ($bS1) DB::table('produksi_batches')->where('id', $bS1->id)->delete();
            }

            $bS2 = $existingBatches->where('shift', 'Malam')->first();
            if ($new_target_s2 > 0) {
                if ($bS2) {
                    DB::table('produksi_batches')->where('id', $bS2->id)->update([
                        'qty_ambil_pcs' => $qty_lembar_s2, 'keterangan' => $keteranganRev, 'mesin_id' => $mesin_id, 'updated_at' => now()
                    ]);
                } else {
                    DB::table('produksi_batches')->insert([
                        'no_produksi' => $baseNoProdS2, 'plan_id' => $id, 'shift' => 'Malam', 'mesin_id' => $mesin_id,
                        'rm_stock_id' => $rm_stock_id, 'material_code' => $plan->part_no, 'qty_ambil_pcs' => $qty_lembar_s2, 'cavity' => $cavity,
                        'status' => 'PROSES', 'keterangan' => $keteranganRev, 'created_at' => $logTimestamp, 'updated_at' => now()
                    ]);
                }
            } else {
                if ($bS2) DB::table('produksi_batches')->where('id', $bS2->id)->delete();
            }

            DB::commit();
            return redirect()->back()->with('success', "Revisi Ke-{$revCount} Sukses! Target produksi WOS berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack(); return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5. PRINT WOS STAMPING
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
            if ($batchData) {
                if (str_ends_with($batchData->no_produksi, '-S')) return false;
                $plan->no_produksi = $batchData->no_produksi;
                $plan->keterangan = $batchData->keterangan;
                $plan->actual_ok = $batchData->qty_hasil_ok ?? 0;
                $plan->actual_ng = $batchData->qty_hasil_ng ?? 0;
                $plan->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $plan->actual_end = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $plan->no_produksi = '-';
                $plan->actual_ok = 0; $plan->actual_ng = 0; $plan->actual_start = ''; $plan->actual_end = '';
            }
            return true;
        })->values();

        $plansS2 = $plansS2->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Malam')->first();
            if ($batchData) {
                if (str_ends_with($batchData->no_produksi, '-S')) return false;
                $plan->no_produksi = $batchData->no_produksi;
                $plan->keterangan = $batchData->keterangan;
                $plan->actual_ok = $batchData->qty_hasil_ok ?? 0;
                $plan->actual_ng = $batchData->qty_hasil_ng ?? 0;
                $plan->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $plan->actual_end = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $plan->no_produksi = '-';
                $plan->actual_ok = 0; $plan->actual_ng = 0; $plan->actual_start = ''; $plan->actual_end = '';
            }
            return true;
        })->values();

        return view('PPIC.print_wos', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * 5B. PRINT WOS BUNDLE
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
            $batchDataPagi = $batches->where('plan_id', $plan->id)->where('shift', 'Pagi')->first();
            $batchDataMalam = $batches->where('plan_id', $plan->id)->where('shift', 'Malam')->first();
            
            if (($batchDataPagi && str_ends_with($batchDataPagi->no_produksi, '-S')) || 
                ($batchDataMalam && str_ends_with($batchDataMalam->no_produksi, '-S'))) {
                return false;
            }

            $plan->no_produksi_s1 = $batchDataPagi->no_produksi ?? '-';
            $plan->no_produksi_s2 = $batchDataMalam->no_produksi ?? '-';
            return true;
        });

        $bigPlans = $allPlansFiltered->filter(function($p) { $code = strtoupper($p->line_code); return !str_contains($code, 'C') && !str_contains($code, 'SMALL'); })->values();
        $smallPlans = $allPlansFiltered->filter(function($p) { $code = strtoupper($p->line_code); return str_contains($code, 'C') || str_contains($code, 'SMALL'); })->values();

        return view('PPIC.print_wos_bundle', compact('bigPlans', 'smallPlans', 'batches', 'date'));
    }

    /**
     * 6. PRINT SURAT SERAH TERIMA MATERIAL (BPRM) - BIG PRESS
     */
    public function printSerahTerima($date, $shift, $line_code)
    {
        $planIds = DB::table('production_plans')->where('plan_date', $date)->where('line_code', $line_code)->pluck('id');

        $batchesRaw = DB::table('produksi_batches')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->where('produksi_batches.no_produksi', 'NOT LIKE', '%-S')
            ->select('produksi_batches.*', 'parts.part_name', 'parts.customer_code as part_cust')
            ->orderBy('produksi_batches.id', 'asc')
            ->get();

        if ($batchesRaw->isEmpty()) return redirect()->back()->with('error', 'Belum ada Material Request untuk mesin ini.');

        $allBatches = collect();

        foreach ($batchesRaw as $batch) {
            $subPartNo = null;
            $subPartName = null;

            if (str_ends_with($batch->no_produksi, '-M')) {
                $timePrefix = substr($batch->no_produksi, 0, strrpos($batch->no_produksi, '-'));
                $subBatch = DB::table('produksi_batches')->where('no_produksi', $timePrefix . '-S')->first();
                if ($subBatch) {
                    $subPartNo = $subBatch->material_code;
                    $subPartName = DB::table('parts')->where('part_no', $subPartNo)->value('part_name');
                }
            } elseif (strpos($batch->keterangan, 'PAIR_MAIN:') === 0) {
                $subPartNo = str_replace('PAIR_MAIN:', '', $batch->keterangan);
                $subPartName = DB::table('parts')->where('part_no', $subPartNo)->value('part_name');
            }

            $displayPartNo = $subPartNo ? "{$batch->material_code} / {$subPartNo}" : $batch->material_code;
            $displayPartName = $subPartName ? ($batch->part_name . " / " . $subPartName) : ($batch->part_name ?? $batch->material_code);

            // ✨ LOGIC BARU: GROUP BY NO_PRODUKSI AGAR MUTLAK JADI 1 BARIS (GABUNG COIL JIKA LEBIH DARI 1) ✨
            $coilsUsed = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->where('rm_production_logs.no_produksi', $batch->no_produksi)
                ->select(
                    DB::raw('GROUP_CONCAT(DISTINCT rm_stocks.coil_id SEPARATOR ", ") as coil_id'),
                    DB::raw('MAX(rm_stocks.spec) as spec'),
                    DB::raw('MAX(rm_stocks.size) as size'),
                    DB::raw('MAX(rm_stocks.material_name) as material_name'),
                    DB::raw('MAX(rm_stocks.customer) as stock_cust'),
                    DB::raw('GROUP_CONCAT(DISTINCT rm_stocks.no_surat_jalan SEPARATOR ", ") as stock_sj'),
                    DB::raw('SUM(rm_production_logs.pcs_used) as total_used')
                )
                ->groupBy('rm_production_logs.no_produksi')
                ->first();

            if ($coilsUsed && $coilsUsed->total_used > 0) {
                $item = clone $batch;
                $item->qty_ambil_pcs = $coilsUsed->total_used;
                $item->coil_id = $coilsUsed->coil_id;
                $item->spec = $coilsUsed->spec;
                $item->size = $coilsUsed->size;
                $item->material_name = $coilsUsed->material_name;
                $item->customer_code = $batch->part_cust ?? $coilsUsed->stock_cust ?? 'SAI';
                $item->display_part_no = $displayPartNo;
                $item->display_part_name = $displayPartName;

                if (!empty($coilsUsed->stock_sj) && $coilsUsed->stock_sj !== '-') {
                    $item->no_surat_jalan = $coilsUsed->stock_sj;
                } else {
                    $item->no_surat_jalan = '-';
                }
                $allBatches->push($item);
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
     * 7. QUALITY HUB
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
     * 8. WELDING DASHBOARD
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
     * 11. PRINT WOS WELDING
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

        foreach ($plansS1 as $p) {
            $batchData = $batches->where('part_no', $p->part_no)->where('shift', 'Pagi')->first();
            
            if ($batchData) {
                $p->no_produksi = $batchData->no_produksi_stamping ?? '-';
                $p->actual_ok   = $batchData->qty_ok ?? 0;
                $p->actual_ng   = $batchData->qty_ng ?? 0;
                $p->actual_ret  = $batchData->qty_return ?? 0;
                $p->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $p->actual_end   = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $p->no_produksi = '-';
                $p->actual_ok = 0; $p->actual_ng = 0; $p->actual_ret = 0;
                $p->actual_start = ''; $p->actual_end = '';
            }
        }

        foreach ($plansS2 as $p) {
            $batchData = $batches->where('part_no', $p->part_no)->where('shift', 'Malam')->first();
            
            if ($batchData) {
                $p->no_produksi = $batchData->no_produksi_stamping ?? '-';
                $p->actual_ok   = $batchData->qty_ok ?? 0;
                $p->actual_ng   = $batchData->qty_ng ?? 0;
                $p->actual_ret  = $batchData->qty_return ?? 0;
                $p->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $p->actual_end   = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $p->no_produksi = '-';
                $p->actual_ok = 0; $p->actual_ng = 0; $p->actual_ret = 0;
                $p->actual_start = ''; $p->actual_end = '';
            }
        }

        return view('PPIC.print_wos_welding', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * 12. PRINT SERAH TERIMA WELDING (WIP)
     */
    public function printSerahTerimaWelding($date, $line_code)
    {
        $clean_line = trim(urldecode($line_code));

        $plans = DB::table('welding_plans')
            ->where('plan_date', $date)
            ->where('line_code', 'LIKE', "%{$clean_line}%")
            ->get();
            
        if ($plans->isEmpty()) return redirect()->back()->with('error', 'Belum ada Material Request (WIP) untuk mesin ini.');

        $parts = $plans->pluck('part_no')->toArray();

        $batchesRaw = DB::table('welding_batches')
            ->leftJoin('parts', 'welding_batches.part_no', '=', 'parts.part_no')
            ->whereIn('welding_batches.part_no', $parts)
            ->whereDate('welding_batches.created_at', $date)
            ->select('welding_batches.*', 'parts.part_name', 'parts.customer_code')
            ->orderBy('welding_batches.id', 'asc')
            ->get();

        $allBatches = collect();

        foreach ($batchesRaw as $batch) {
            $item = clone $batch;
            
            $item->display_part_no = $batch->part_no;
            $item->display_part_name = $batch->part_name;
            $item->customer_code = $batch->customer_code ?? 'SAI';
            
            $item->no_surat_jalan = 'WIP-STAMPING';
            $item->coil_id = '-';
            $item->spec = '-';
            $item->size = '-';
            
            $item->qty_ambil_pcs = $batch->qty_masuk;
            $item->qty_hasil_ok = $batch->qty_ok;
            $item->qty_hasil_ng = $batch->qty_ng;
            $item->qty_return_warehouse = $batch->qty_return;

            $allBatches->push($item);
        }

        $batchesS1 = $allBatches->filter(fn($b) => in_array($b->shift, ['Pagi', 'S1', 'Shift 1']))->values();
        $batchesS2 = $allBatches->filter(fn($b) => in_array($b->shift, ['Malam', 'S2', 'Shift 2']))->values();
        $batches = $allBatches;

        return view('PPIC.print_serah_terima_welding', compact('batches', 'batchesS1', 'batchesS2', 'date', 'line_code'));
    }

    /**
     * 5C. PRINT WOS KHUSUS SMALL PRESS
     */
    public function printWosSmall($date, $shift, $line_code)
    {
        $plansS1 = DB::table('production_plans')->where('plan_date', $date)->where('line_code', $line_code)->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0)->orderBy('id', 'asc')->get();
        $plansS2 = DB::table('production_plans')->where('plan_date', $date)->where('line_code', $line_code)->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0)->orderBy('id', 'asc')->get();

        if ($plansS1->isEmpty() && $plansS2->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal Small Press di mesin ini untuk dicetak.');
        }

        $planIds = collect()->merge($plansS1)->merge($plansS2)->pluck('id')->unique()->toArray();
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        $plansS1 = $plansS1->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Pagi')->first();
            if ($batchData) {
                if (str_ends_with($batchData->no_produksi, '-S')) return false;
                $plan->no_produksi = $batchData->no_produksi;
                $plan->keterangan = $batchData->keterangan;
                $plan->actual_ok = $batchData->qty_hasil_ok ?? 0;
                $plan->actual_ng = $batchData->qty_hasil_ng ?? 0;
                $plan->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $plan->actual_end = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $plan->no_produksi = '-';
                $plan->actual_ok = 0; $plan->actual_ng = 0; $plan->actual_start = ''; $plan->actual_end = '';
            }
            return true;
        })->values();

        $plansS2 = $plansS2->filter(function($plan) use ($batches) {
            $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Malam')->first();
            if ($batchData) {
                if (str_ends_with($batchData->no_produksi, '-S')) return false;
                $plan->no_produksi = $batchData->no_produksi;
                $plan->keterangan = $batchData->keterangan;
                $plan->actual_ok = $batchData->qty_hasil_ok ?? 0;
                $plan->actual_ng = $batchData->qty_hasil_ng ?? 0;
                $plan->actual_start = $batchData->created_at ? date('H:i', strtotime($batchData->created_at)) : '';
                $plan->actual_end = ($batchData->status == 'COMPLETED') ? date('H:i', strtotime($batchData->updated_at)) : '';
            } else {
                $plan->no_produksi = '-';
                $plan->actual_ok = 0; $plan->actual_ng = 0; $plan->actual_start = ''; $plan->actual_end = '';
            }
            return true;
        })->values();

        return view('PPIC.print_wos_small', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * 6B. PRINT SURAT SERAH TERIMA MATERIAL (BPRM) KHUSUS SMALL PRESS
     */
    public function printSerahTerimaSmall($date, $shift, $line_code)
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
            return redirect()->back()->with('error', 'Belum ada Material Request untuk mesin Small Press ini.');
        }

        $allBatches = collect();

        foreach ($batchesRaw as $batch) {
            $subPartNo = null;
            $subPartName = null;

            if (str_ends_with($batch->no_produksi, '-M')) {
                $timePrefix = substr($batch->no_produksi, 0, strrpos($batch->no_produksi, '-'));
                $subBatch = DB::table('produksi_batches')->where('no_produksi', $timePrefix . '-S')->first();
                if ($subBatch) {
                    $subPartNo = $subBatch->material_code;
                    $subPartName = DB::table('parts')->where('part_no', $subPartNo)->value('part_name');
                }
            } elseif (strpos($batch->keterangan, 'PAIR_MAIN:') === 0) {
                $subPartNo = str_replace('PAIR_MAIN:', '', $batch->keterangan);
                $subPartName = DB::table('parts')->where('part_no', $subPartNo)->value('part_name');
            }

            // ✨ Menggabungkan Nama Part Utama / Part Pasangan dengan rapi
            $displayPartNo = $subPartNo ? "{$batch->material_code} / {$subPartNo}" : $batch->material_code;
            $displayPartName = $subPartName ? ($batch->part_name . " / " . $subPartName) : ($batch->part_name ?? $batch->material_code);

            // ✨ LOGIC BARU: GROUP BY NO_PRODUKSI AGAR MUTLAK JADI 1 BARIS (GABUNG COIL JIKA LEBIH DARI 1) ✨
            $coilsUsed = DB::table('rm_production_logs')
                ->leftJoin('rm_stocks', 'rm_production_logs.rm_stock_id', '=', 'rm_stocks.id')
                ->where('rm_production_logs.no_produksi', $batch->no_produksi)
                ->select(
                    DB::raw('GROUP_CONCAT(DISTINCT rm_stocks.coil_id SEPARATOR ", ") as coil_id'),
                    DB::raw('MAX(rm_stocks.spec) as spec'),
                    DB::raw('MAX(rm_stocks.size) as size'),
                    DB::raw('MAX(rm_stocks.material_name) as material_name'),
                    DB::raw('MAX(rm_stocks.customer) as stock_cust'),
                    DB::raw('GROUP_CONCAT(DISTINCT rm_stocks.no_surat_jalan SEPARATOR ", ") as stock_sj'),
                    DB::raw('SUM(rm_production_logs.pcs_used) as total_used')
                )
                ->groupBy('rm_production_logs.no_produksi')
                ->first();

            if ($coilsUsed && $coilsUsed->total_used > 0) {
                $item = clone $batch;
                $item->qty_ambil_pcs = $coilsUsed->total_used; // Menggunakan SUM yang sudah ditotal
                $item->coil_id = $coilsUsed->coil_id;
                $item->spec = $coilsUsed->spec;
                $item->size = $coilsUsed->size;
                $item->material_name = $coilsUsed->material_name;
                $item->customer_code = $batch->part_cust ?? $coilsUsed->stock_cust ?? 'SAI';
                $item->display_part_no = $displayPartNo;
                $item->display_part_name = $displayPartName;

                if (!empty($coilsUsed->stock_sj) && $coilsUsed->stock_sj !== '-') {
                    $item->no_surat_jalan = $coilsUsed->stock_sj;
                } else {
                    $item->no_surat_jalan = '-';
                }
                $allBatches->push($item);
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

        return view('PPIC.print_serahterima_small', compact('batches', 'batchesS1', 'batchesS2', 'date', 'shift', 'line_code'));
    }
    
    /**
     * AJAX REALTIME STOCK CHECKER (UNTUK VALIDASI MODAL REGISTER PLAN)
     */
    public function checkRmStockAjax(Request $request, $part_no = null)
    {
        try {
            $rawPart = $part_no ?? $request->part_no ?? $request->part;
            $cleanPart = trim(urldecode($rawPart));
            
            if (strpos($cleanPart, ' - ') !== false) {
                $cleanPart = trim(explode(' - ', $cleanPart)[0]);
            }

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
                ->get()
                ->unique('coil_id');

            if ($availableCoils->isEmpty()) {
                $refStock = DB::table('rm_stocks')->where('material_code', $cleanPart)->first();
                if ($refStock) {
                    $availableCoils = DB::table('rm_stocks')
                        ->where('customer', trim($refStock->customer))
                        ->where(DB::raw('TRIM(spec)'), trim($refStock->spec))
                        ->where(DB::raw("REPLACE(size, ' ', '')"), str_replace(' ', '', $refStock->size))
                        ->where('stock_pcs', '>', 0)
                        ->get()
                        ->unique('coil_id');
                }
            }

            $totalStock = (int) $availableCoils->sum('stock_pcs');
            $firstCoil = $availableCoils->first();
            $cavity = $firstCoil && $firstCoil->cavity > 0 ? (int)$firstCoil->cavity : 1;

            return response()->json([
                'status' => 'success',
                'total_stock' => $totalStock,
                'cavity' => $cavity
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * AJAX REALTIME STOCK CHECKER (UNTUK VALIDASI MODAL REGISTER WELDING PLAN)
     */
    public function checkWipStockAjax(Request $request, $part_no = null)
    {
        try {
            $rawPart = $part_no ?? $request->part_no ?? $request->part;
            $cleanPart = trim(urldecode($rawPart));
            
            if (strpos($cleanPart, ' - ') !== false) {
                $cleanPart = trim(explode(' - ', $cleanPart)[0]);
            }

            $fg = DB::table('finished_goods')->where('part_no', $cleanPart)->first();
            $wipStock = $fg ? (int)$fg->welding_stock : 0;

            return response()->json([
                'status' => 'success',
                'wip_stock' => $wipStock
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}