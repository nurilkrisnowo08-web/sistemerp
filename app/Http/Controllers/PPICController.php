<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // ✨ CEK APAKAH BATCH PRODUKSI SUDAH SELESAI / CLOSE SEMUA
            $batchStatuses = DB::table('produksi_batches')->where('plan_id', $p->id)->pluck('status');
            $isAllBatchCompleted = $batchStatuses->isNotEmpty() && $batchStatuses->every(fn($st) => $st === 'COMPLETED');

            // Jika produksi sudah input semua dan close, otomatis masuk status COMPLETED (walaupun target belum tercapai)
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
     * 3. STORE MPS (SMART INVENTORY GUARD + AUTO LOG OUT KE GUDANG RM)
     */
    public function mpsStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);
            $total_target = $target_s1 + $target_s2;

            if ($total_target <= 0) {
                throw new \Exception("Target produksi tidak boleh kosong / 0.");
            }

            $rm_stock = DB::table('rm_stocks')->where('material_code', $request->part_no)->where('stock_pcs', '>', 0)->first();
            if (!$rm_stock) {
                throw new \Exception("Material untuk Part No [{$request->part_no}] kosong atau tidak terdaftar di Master RM!");
            }

            $cavity = $rm_stock->cavity > 0 ? $rm_stock->cavity : 1;
            $kebutuhan_lembar = ceil($total_target / $cavity);

            if ($rm_stock->stock_pcs < $kebutuhan_lembar) {
                throw new \Exception("❌ PLAN DITOLAK: STOK MATERIAL KURANG! Anda butuh {$kebutuhan_lembar} Lembar, tapi Sisa di Gudang cuma {$rm_stock->stock_pcs} Lembar.");
            }

            $planId = DB::table('production_plans')->insertGetId([
                'plan_date' => $request->plan_date,
                'part_no' => $request->part_no,
                'customer_code' => $request->customer_code,
                'line_code' => $request->line_code,
                'manpower' => $request->manpower ?? 8,
                'process_qty' => $request->process_qty ?? 4,
                'qty_lot' => $request->qty_lot ?? 200,
                'cap_per_hour' => $request->cap_per_hour ?? 320,
                's1_plan_reg' => $request->s1_plan_reg ?? 0,
                's1_plan_ot' => $request->s1_plan_ot ?? 0,
                's2_plan_reg' => $request->s2_plan_reg ?? 0,
                's2_plan_ot' => $request->s2_plan_ot ?? 0,
                'dandory_time' => $request->dandory_time ?? 15,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('rm_stocks')->where('id', $rm_stock->id)->decrement('stock_pcs', $kebutuhan_lembar);

            $logTimestamp = date('Y-m-d H:i:s', strtotime($request->plan_date . ' ' . date('H:i:s')));
            DB::table('rm_production_logs')->insert([
                'rm_stock_id'   => $rm_stock->id,
                'material_code' => $request->part_no,
                'pcs_used'      => $kebutuhan_lembar,
                'no_produksi'   => 'WOS-PLAN-' . date('YmdHis'),
                'created_at'    => $logTimestamp
            ]);

            $mesin = DB::table('line')->where('kode_Line', $request->line_code)->first();
            $mesin_id = $mesin ? $mesin->id : null;

            $qty_lembar_s1 = ceil($target_s1 / $cavity);
            if ($target_s1 == 0) $qty_lembar_s1 = 0;
            $qty_lembar_s2 = $kebutuhan_lembar - $qty_lembar_s1;

            if ($target_s1 > 0 && $mesin_id) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S1-' . date('YmdHis'),
                    'plan_id' => $planId,
                    'shift' => 'Pagi',
                    'mesin_id' => $mesin_id,
                    'rm_stock_id' => $rm_stock->id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s1,
                    'cavity' => $cavity,
                    'status' => 'PROSES',
                    'keterangan' => 'AUTO-DEPLOY FROM PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($target_s2 > 0 && $mesin_id) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S2-' . date('YmdHis'),
                    'plan_id' => $planId,
                    'shift' => 'Malam',
                    'mesin_id' => $mesin_id,
                    'rm_stock_id' => $rm_stock->id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s2,
                    'cavity' => $cavity,
                    'status' => 'PROSES',
                    'keterangan' => 'AUTO-DEPLOY FROM PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', "Sukses! Jadwal WOS Terkirim & {$kebutuhan_lembar} Lembar Material Otomatis Tercatat di Dashboard RM!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✨ 4. UPDATE & REVISI WOS (DENGAN PROTEKSI PENGAMAN + ANTI-DOUBLE RETURN)
     */
    public function updateWos(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $plan = DB::table('production_plans')->where('id', $id)->first();
            if (!$plan) throw new \Exception("Schedule tidak ditemukan.");

            $existingBatches = DB::table('produksi_batches')->where('plan_id', $id)->get();
            
            // ✨ PENGAMAN 1: Tolak Revisi jika Produksi sudah nyetor aktual atau sudah di-close
            $isStarted = $existingBatches->contains(function($b) {
                return $b->qty_hasil_ok > 0 || $b->qty_hasil_ng > 0 || $b->status === 'COMPLETED';
            });

            if ($isStarted) {
                throw new \Exception("❌ REVISI DITOLAK! WOS ini sudah mulai dikerjakan (Aktual > 0) atau sudah ditutup oleh tim Produksi.");
            }

            $alreadyReturnedByProd = $existingBatches->sum('qty_return_warehouse');

            $old_target_s1 = $plan->s1_plan_reg + $plan->s1_plan_ot;
            $old_target_s2 = $plan->s2_plan_reg + $plan->s2_plan_ot;
            $old_total = $old_target_s1 + $old_target_s2;

            $new_target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $new_target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);
            $new_total = $new_target_s1 + $new_target_s2;

            $sampleBatch = $existingBatches->first();
            $rm_stock = $sampleBatch 
                ? DB::table('rm_stocks')->where('id', $sampleBatch->rm_stock_id)->first()
                : DB::table('rm_stocks')->where('material_code', $plan->part_no)->first();

            if (!$rm_stock) throw new \Exception("Database Material tidak terdeteksi untuk proses Revisi/Return.");

            $cavity = $rm_stock->cavity > 0 ? $rm_stock->cavity : 1;
            
            $old_lembar = ceil($old_total / $cavity);
            $new_lembar = ceil($new_total / $cavity);
            $selisih_lembar = $new_lembar - $old_lembar;

            $logTimestamp = date('Y-m-d H:i:s', strtotime($plan->plan_date . ' ' . date('H:i:s')));

            if ($selisih_lembar > 0) {
                if ($rm_stock->stock_pcs < $selisih_lembar) {
                    throw new \Exception("❌ REVISI DITOLAK! Butuh tambahan {$selisih_lembar} Lembar material. Sisa stok hanya {$rm_stock->stock_pcs}.");
                }
                DB::table('rm_stocks')->where('id', $rm_stock->id)->decrement('stock_pcs', $selisih_lembar);
                
                DB::table('rm_production_logs')->insert([
                    'rm_stock_id'   => $rm_stock->id,
                    'material_code' => $plan->part_no,
                    'pcs_used'      => $selisih_lembar,
                    'no_produksi'   => 'REV-OUT-' . date('YmdHis'),
                    'created_at'    => $logTimestamp
                ]);
            } 
            elseif ($selisih_lembar < 0) {
                $raw_return = abs($selisih_lembar);
                // ✨ PENGAMAN 2 (ANTI DOUBLE RETURN)
                $net_return = max(0, $raw_return - $alreadyReturnedByProd);

                if ($net_return > 0) {
                    DB::table('rm_stocks')->where('id', $rm_stock->id)->increment('stock_pcs', $net_return);
                    
                    DB::table('rm_incoming_logs')->insert([
                        'rm_stock_id'   => $rm_stock->id,
                        'material_code' => $plan->part_no,
                        'pcs_in'        => $net_return,
                        'source'        => 'return',
                        'no_produksi'   => 'REV-RTN-' . date('YmdHis'),
                        'created_at'    => $logTimestamp
                    ]);
                }
            }

            DB::table('production_plans')->where('id', $id)->update([
                's1_plan_reg' => $request->s1_plan_reg ?? 0,
                's1_plan_ot' => $request->s1_plan_ot ?? 0,
                's2_plan_reg' => $request->s2_plan_reg ?? 0,
                's2_plan_ot' => $request->s2_plan_ot ?? 0,
                'cap_per_hour' => $request->cap_per_hour ?? $plan->cap_per_hour,
                'dandory_time' => $request->dandory_time ?? $plan->dandory_time,
                'updated_at' => now()
            ]);

            DB::table('produksi_batches')->where('plan_id', $id)->where('status', '!=', 'COMPLETED')->delete();

            $qty_lembar_s1 = ceil($new_target_s1 / $cavity);
            if($new_target_s1 == 0) $qty_lembar_s1 = 0;
            $qty_lembar_s2 = max(0, $new_lembar - $qty_lembar_s1);

            $mesin = DB::table('line')->where('kode_Line', $plan->line_code)->first();

            $batchS1 = DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Pagi')->first();
            $batchS2 = DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Malam')->first();

            if ($new_target_s1 > 0 && (!$batchS1 || $batchS1->status !== 'COMPLETED')) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S1-REV-' . date('His'),
                    'plan_id' => $id,
                    'shift' => 'Pagi',
                    'mesin_id' => $mesin ? $mesin->id : null,
                    'rm_stock_id' => $rm_stock->id,
                    'material_code' => $plan->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s1,
                    'cavity' => $cavity,
                    'status' => 'PROSES',
                    'keterangan' => 'DIREVISI PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($new_target_s2 > 0 && (!$batchS2 || $batchS2->status !== 'COMPLETED')) {
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S2-REV-' . date('His'),
                    'plan_id' => $id,
                    'shift' => 'Malam',
                    'mesin_id' => $mesin ? $mesin->id : null,
                    'rm_stock_id' => $rm_stock->id,
                    'material_code' => $plan->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s2,
                    'cavity' => $cavity,
                    'status' => 'PROSES',
                    'keterangan' => 'DIREVISI PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $msg = ($new_total == 0) 
                ? "Plan DIBATALKAN! Selisih material otomatis disinkronkan ke Gudang RM."
                : "Revisi Sukses! Log Material (IN/OUT) tersinkronisasi tanpa duplikasi.";

            DB::commit();
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5. PRINT WOS 
     */
    public function printWos($date, $shift, $line_code)
    {
        $plansS1 = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code)
            ->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0)
            ->orderBy('id', 'asc')->get();

        $plansS2 = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code)
            ->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0)
            ->orderBy('id', 'asc')->get();

        if ($plansS1->isEmpty() && $plansS2->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal di mesin ini untuk dicetak.');
        }

        $planIds = collect()->merge($plansS1)->merge($plansS2)->pluck('id')->unique()->toArray();

        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        return view('PPIC.print_wos', compact('plansS1', 'plansS2', 'batches', 'date', 'line_code'));
    }

    /**
     * ✨ 5B. PRINT WOS BUNDLE (BIG & SMALL)
     */
    public function printWosBundle($date)
    {
        $allPlans = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where(DB::raw('s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot'), '>', 0)
            ->orderBy('id', 'asc')
            ->get();

        if ($allPlans->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada jadwal produksi pada tanggal ini untuk dicetak.');
        }

        $bigPlans = $allPlans->filter(function($p) {
            $code = strtoupper($p->line_code);
            return !str_contains($code, 'C') && !str_contains($code, 'SMALL');
        });

        $smallPlans = $allPlans->filter(function($p) {
            $code = strtoupper($p->line_code);
            return str_contains($code, 'C') || str_contains($code, 'SMALL');
        });

        $planIds = $allPlans->pluck('id')->toArray();
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        return view('PPIC.print_wos_bundle', compact('bigPlans', 'smallPlans', 'batches', 'date'));
    }

    /**
     * 6. PRINT SURAT SERAH TERIMA MATERIAL
     */
    public function printSerahTerima($date, $shift, $line_code)
    {
        $planIds = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code)
            ->pluck('id');

        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->select(
                'produksi_batches.*', 
                'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name',
                'parts.part_name'
            )
            ->orderBy('produksi_batches.shift', 'desc') 
            ->get();

        if ($batches->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada Material Request untuk mesin ini.');
        }

        return view('PPIC.print_serah_terima', compact('batches', 'date', 'line_code'));
    }

    /**
     * 7. QUALITY HUB KHUSUS STAMPING
     */
    public function qualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        $sumStamping = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', 'NOT LIKE', 'W-%')
            ->where('line_code', '!=', 'WELDING AREA')
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))->first();

        $ngStamping = DB::table('production_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->whereNotIn('ng_type', function($q) { $q->select('ng_name')->from('master_ngs')->where('category', 'WELDING'); })
            ->groupBy('ng_type')->orderBy('total', 'DESC')->get();

        $detailStamping = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', 'NOT LIKE', 'W-%')
            ->where('line_code', '!=', 'WELDING AREA')
            ->get();

        foreach($detailStamping as $d) {
            $d->batches = DB::table('produksi_batches')
                ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
                ->where('material_code', $d->part_no)
                ->where('shift', $d->shift)
                ->whereDate('produksi_batches.created_at', $date)
                ->select('no_produksi', 'qty_ambil_pcs', 'qty_hasil_ok', 'qty_hasil_ng', 'kode_Line')
                ->get();
        }

        return view('PPIC.quality_hub', compact('date', 'sumStamping', 'ngStamping', 'detailStamping'));
    }

    public function getBatchNGDetails($no_produksi)
    {
        $details = DB::table('production_ng_logs')
                    ->where('no_produksi', $no_produksi)
                    ->select('ng_type', 'qty')
                    ->get();
        
        if($details->isEmpty()){
            $details = DB::table('welding_ng_logs')
                        ->where('no_produksi', $no_produksi)
                        ->select('ng_type', 'qty')
                        ->get();
        }

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
                       ->orderBy('welding_plans.id', 'asc')->get();

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

    public function weldingMpsStore(Request $request)
    {
        $partData = DB::table('parts')->where('part_no', $request->part_no)->first();
        DB::table('welding_plans')->updateOrInsert(
            ['plan_date' => $request->plan_date, 'part_no' => $request->part_no],
            ['customer_code' => $partData->customer_code ?? 'UNK', 'line_code' => $request->line_code, 'manpower' => $request->manpower ?? 1, 'cap_per_hour' => $request->cap_per_hour ?? 0, 's1_plan_reg' => $request->s1_plan_reg ?? 0, 's1_plan_ot' => $request->s1_plan_ot ?? 0, 's2_plan_reg' => $request->s2_plan_reg ?? 0, 's2_plan_ot' => $request->s2_plan_ot ?? 0, 'dandory_time' => 15, 'process_qty' => 1, 'qty_lot' => 1, 'updated_at' => now()]
        );
        return redirect()->back()->with('success', 'Welding Plan Authorized!');
    }

    /**
     * 10. BATCH RECOVERY & CLOSE FUNCTIONS (CATAT RETURN & NG)
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

            $cavity = $batch->cavity > 0 ? $batch->cavity : 1;
            $totalPcsProduced = (int)$batch->qty_hasil_ok + (int)$batch->qty_hasil_ng;
            $lembarTerpakai = ceil($totalPcsProduced / $cavity);
            
            $sisa = max(0, (int)$batch->qty_ambil_pcs - $lembarTerpakai);

            if ($sisa > 0 && $batch->rm_stock_id) {
                DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->increment('stock_pcs', $sisa);

                DB::table('rm_incoming_logs')->insert([
                    'rm_stock_id'   => $batch->rm_stock_id,
                    'material_code' => $batch->material_code,
                    'pcs_in'        => $sisa,
                    'source'        => 'return',
                    'no_produksi'   => $batch->no_produksi,
                    'created_at'    => now()
                ]);
            }

            if ((int)$batch->qty_hasil_ng > 0) {
                $hasNgLog = DB::table('production_ng_logs')->where('no_produksi', $batch->no_produksi)->exists();
                if (!$hasNgLog) {
                    $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
                    DB::table('production_ng_logs')->insert([
                        'no_produksi' => $batch->no_produksi,
                        'part_no'     => $batch->material_code,
                        'line_code'   => $lineCode,
                        'shift'       => $batch->shift,
                        'ng_type'     => 'PROD_DEFECT',
                        'qty'         => (int)$batch->qty_hasil_ng,
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }
            }

            DB::table('produksi_batches')->where('id', $id)->update([
                'status'               => 'COMPLETED',
                'qty_return_warehouse' => $sisa,
                'updated_at'           => now()
            ]);

            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();

            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')
                    ->where('part_no', $batch->material_code)
                    ->increment('welding_stock', (int)$batch->qty_hasil_ok, ['updated_at' => now()]);

                DB::table('production_logs')->insert([
                    'part_no'      => $batch->material_code,
                    'qty'          => (int)$batch->qty_hasil_ok,
                    'process_type' => 'WELDING', 
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            } else {
                DB::table('finished_goods')
                    ->where('part_no', $batch->material_code)
                    ->increment('stock', (int)$batch->qty_hasil_ok, ['updated_at' => now()]);

                DB::table('production_logs')->insert([
                    'part_no'      => $batch->material_code,
                    'qty'          => (int)$batch->qty_hasil_ok,
                    'process_type' => 'STAMPING', 
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            }

            $this->syncToActual($id);

            DB::commit();
            return redirect()->back()->with('success', "Batch Closed! Selesai diinput, sisa {$sisa} Lembar otomatis dicatat return ke Gudang RM.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses penutupan: ' . $e->getMessage());
        }
    }

    private function syncToActual($batchId) 
    {
        $batch = DB::table('produksi_batches')->where('id', $batchId)->first(); if (!$batch) return;
        $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
        $actual = DB::table('production_actuals')->where('part_no', $batch->material_code)->where('shift', $batch->shift)->whereDate('created_at', date('Y-m-d', strtotime($batch->created_at)))->first();
        if ($actual) { 
            DB::table('production_actuals')->where('id', $actual->id)->update(['qty_ok' => $actual->qty_ok + $batch->qty_hasil_ok, 'qty_ng' => $actual->qty_ng + $batch->qty_hasil_ng, 'updated_at' => now()]); 
        } else { 
            DB::table('production_actuals')->insert(['part_no' => $batch->material_code, 'line_code' => $lineCode, 'shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'created_at' => $batch->created_at, 'updated_at' => now()]); 
        }
    }
}