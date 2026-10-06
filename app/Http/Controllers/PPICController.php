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
        
        // 1. Ambil Alerts (Problem Batches)
        $alerts = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->where('produksi_batches.status', 'PROBLEM')
            ->select('produksi_batches.id', 'produksi_batches.no_produksi', 'produksi_batches.material_code', 'line.kode_Line', 'produksi_batches.keterangan', 'produksi_batches.updated_at')
            ->get();

        // 2. Ambil Plans & Hitung Progress Stamping
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

            if($targetPerPart > 0 && $actualPerPart >= $targetPerPart) { $statusCount['completed']++; }
            elseif ($date < $today && $actualPerPart < $targetPerPart) { $statusCount['shortage']++; }
            elseif ($actualPerPart > 0) { $statusCount['running']++; }
            else { $statusCount['waiting']++; }
        }

        $totalPlan = $plans->sum('plan_qty') ?: 0;
        $totalActual = $plans->sum('actual_qty') ?: 0;
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        // 3. Data Daily (7 Hari)
        $dailyLabels = []; $dailyOk = []; $dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok');
            $dailyNg[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ng');
        }

        // 4. Data Monthly (6 Bulan)
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
     * 2. DAILY MPS (STAMPING)
     */
    public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $shiftParam = $request->shift ?? 'S1'; 

        $query = DB::table('production_plans')->where('plan_date', $date);
        
        if ($shiftParam == 'S1') {
            $query->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0);
        } else {
            $query->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0);
        }

        $plans = $query->orderBy('line_code', 'asc')->orderBy('id', 'asc')->get();

        $totalPlanQty = 0;
        $totalWorkingHours = 0;
        $totalDandory = 0;

        $lineFinishTime = []; 
        $defaultStart = ($shiftParam == 'S1') ? "07:30" : "19:30";

        foreach($plans as $plan) {
            $dbShiftName = ($shiftParam == 'S1') ? 'Pagi' : 'Malam';
            
            $actualData = DB::table('production_actuals')
                ->where('part_no', $plan->part_no) 
                ->where('shift', $dbShiftName) 
                ->whereDate('created_at', $date)
                ->where('line_code', '!=', 'WELDING AREA')
                ->sum('qty_ok');

            $plan->total_actual = (int)$actualData;
            $plan->total_target = ($shiftParam == 'S1') ? ($plan->s1_plan_reg + $plan->s1_plan_ot) : ($plan->s2_plan_reg + $plan->s2_plan_ot);
            
            $totalPlanQty += $plan->total_target;
            $totalDandory += ($plan->dandory_time ?? 0);

            $dandoryH = ($plan->dandory_time ?? 0) / 60;
            $duration = ($plan->cap_per_hour > 0 && $plan->total_target > 0) ? ($plan->total_target / $plan->cap_per_hour) + $dandoryH : 0;
            
            $totalWorkingHours += $duration;

            $startTime = $lineFinishTime[$plan->line_code] ?? $defaultStart;
            $plan->start_time = $startTime;
            $plan->ahir_time = date('H:i', strtotime($startTime . " + " . round($duration * 60) . " minutes"));
            $lineFinishTime[$plan->line_code] = $plan->ahir_time; 
            $plan->balance = $plan->total_target - $plan->total_actual;
        }

        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        // 🌟 DI SINI KITA KELOMPOKIN DATA BERDASARKAN MESIN BIAR GAMPANG NGE-PRINTNYA
        $groupedPlans = $plans->groupBy('line_code');

        return view('PPIC.mps_index', compact(
            'groupedPlans', 'date', 'availableLines', 'availableCustomers', 
            'totalPlanQty', 'totalWorkingHours', 'totalDandory'
        ))->with('shift', $shiftParam);
    }

    /**
     * ✨ UPDATED: STORE MPS (AUTO-PILOT WOS DEPLOYMENT) - LANGSUNG STATUS "PROSES"
     */
    public function mpsStore(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan ke Master Schedule (PPIC)
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

            $target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);

            $mesin = DB::table('line')->where('kode_Line', $request->line_code)->first();
            $mesin_id = $mesin ? $mesin->id : null;

            $rm_stock = DB::table('rm_stocks')->where('material_code', $request->part_no)->where('stock_pcs', '>', 0)->first();
            $rm_stock_id = $rm_stock ? $rm_stock->id : null;
            $cavity = $rm_stock ? ($rm_stock->cavity > 0 ? $rm_stock->cavity : 1) : 1;

            // 5. Deploy Auto-WOS ke Terminal Produksi (Shift 1) -> STATUS LANGSUNG 'PROSES'
            if ($target_s1 > 0 && $mesin_id) {
                $qty_lembar_s1 = ceil($target_s1 / $cavity);
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S1-' . date('Ymd-His'),
                    'plan_id' => $planId,
                    'shift' => 'Pagi',
                    'mesin_id' => $mesin_id,
                    'rm_stock_id' => $rm_stock_id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s1,
                    'cavity' => $cavity,
                    'status' => 'PROSES', // ✨ STATUS LANGSUNG JALAN DI TERMINAL PRODUKSI
                    'keterangan' => 'AUTO-DEPLOY FROM PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 6. Deploy Auto-WOS ke Terminal Produksi (Shift 2) -> STATUS LANGSUNG 'PROSES'
            if ($target_s2 > 0 && $mesin_id) {
                $qty_lembar_s2 = ceil($target_s2 / $cavity);
                DB::table('produksi_batches')->insert([
                    'no_produksi' => 'WOS-S2-' . date('Ymd-His'),
                    'plan_id' => $planId,
                    'shift' => 'Malam',
                    'mesin_id' => $mesin_id,
                    'rm_stock_id' => $rm_stock_id,
                    'material_code' => $request->part_no,
                    'qty_ambil_pcs' => $qty_lembar_s2,
                    'cavity' => $cavity,
                    'status' => 'PROSES', // ✨ STATUS LANGSUNG JALAN DI TERMINAL PRODUKSI
                    'keterangan' => 'AUTO-DEPLOY FROM PPIC',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Master Schedule Updated & WOS Langsung Masuk Terminal Produksi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal Deploy WOS: ' . $e->getMessage());
        }
    }

    /**
     * ✨ FITUR BARU: UPDATE & REVISI WOS
     */
    public function updateWos(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $plan = DB::table('production_plans')->where('id', $id)->first();
            if (!$plan) throw new \Exception("Schedule tidak ditemukan.");

            DB::table('production_plans')->where('id', $id)->update([
                's1_plan_reg' => $request->s1_plan_reg ?? 0,
                's1_plan_ot' => $request->s1_plan_ot ?? 0,
                's2_plan_reg' => $request->s2_plan_reg ?? 0,
                's2_plan_ot' => $request->s2_plan_ot ?? 0,
                'cap_per_hour' => $request->cap_per_hour ?? $plan->cap_per_hour,
                'dandory_time' => $request->dandory_time ?? $plan->dandory_time,
                'updated_at' => now()
            ]);

            $target_s1 = ($request->s1_plan_reg ?? 0) + ($request->s1_plan_ot ?? 0);
            $target_s2 = ($request->s2_plan_reg ?? 0) + ($request->s2_plan_ot ?? 0);

            $sampleBatch = DB::table('produksi_batches')->where('plan_id', $id)->first();
            $cavity = $sampleBatch ? ($sampleBatch->cavity > 0 ? $sampleBatch->cavity : 1) : 1;

            if ($target_s1 > 0) {
                DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Pagi')->update([
                    'qty_ambil_pcs' => ceil($target_s1 / $cavity),
                    'keterangan' => 'DIREVISI OLEH PPIC',
                    'updated_at' => now()
                ]);
            } else {
                DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Pagi')->delete();
            }

            if ($target_s2 > 0) {
                DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Malam')->update([
                    'qty_ambil_pcs' => ceil($target_s2 / $cavity),
                    'keterangan' => 'DIREVISI OLEH PPIC',
                    'updated_at' => now()
                ]);
            } else {
                DB::table('produksi_batches')->where('plan_id', $id)->where('shift', 'Malam')->delete();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Revisi Jadwal Sukses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✨ FITUR BARU: PRINT WOS (SATU KERTAS UNTUK SEMUA PART DI MESIN & SHIFT YG SAMA)
     */
    public function printWos($date, $shift, $line_code)
    {
        // 1. Ambil semua jadwal (Plans) di mesin ini pada hari dan shift tersebut
        $query = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code);
            
        if ($shift == 'S1') {
            $query->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0);
            $dbShiftName = 'Pagi';
        } else {
            $query->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0);
            $dbShiftName = 'Malam';
        }

        $plans = $query->orderBy('id', 'asc')->get();

        if ($plans->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal di mesin ini untuk dicetak.');
        }

        // 2. Ambil data Batches & Info Material (Buat Surat Serah Terima)
        $planIds = $plans->pluck('id')->toArray();
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->where('produksi_batches.shift', $dbShiftName)
            ->select('produksi_batches.*', 'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name')
            ->get();

        // Nanti lu tinggal siapin file view blade-nya namanya 'print_wos.blade.php' di folder PPIC
        return view('PPIC.print_wos', compact('plans', 'batches', 'date', 'shift', 'line_code'));
    }

    /**
     * 3. QUALITY HUB KHUSUS STAMPING
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
     * 4. WELDING INTELLIGENCE DASHBOARD
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
     * 5. WELDING MPS (PISAH ACTUAL PER SHIFT )
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
     * 6. QUALITY HUB KHUSUS WELDING
     */
    public function weldingQualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        $summary = DB::table('welding_actuals')
            ->whereDate('created_at', $date)
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))
            ->first();

        $ngRanking = DB::table('welding_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->groupBy('ng_type')
            ->orderBy('total', 'DESC')
            ->get();

        $details = DB::table('welding_actuals')->whereDate('created_at', $date)->get();

        foreach($details as $d) {
            $d->batches = DB::table('welding_batches')
                ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
                ->where('part_no', $d->part_no)
                ->whereDate('welding_batches.created_at', $date)
                ->select('no_produksi_stamping as no_produksi', 'qty_masuk', 'qty_ok', 'qty_ng', 'kode_line')
                ->get();
        }

        return view('PPIC.welding_quality_hub', compact('date', 'summary', 'ngRanking', 'details'));
    }

    /**
     * 7. BATCH RECOVERY FUNCTIONS
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

            $sisa = (int)$batch->qty_ambil_pcs - ((int)$batch->qty_hasil_ok + (int)$batch->qty_hasil_ng);
            if ($sisa > 0) {
                DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->increment('stock_pcs', $sisa);
            }

            DB::table('produksi_batches')->where('id', $id)->update([
                'status' => 'COMPLETED',
                'qty_return_warehouse' => $sisa,
                'updated_at' => now()
            ]);

            $part = DB::table('parts')->where('part_no', $batch->material_code)->first();

            if ($part && $part->next_process == 'WELDING') {
                DB::table('finished_goods')
                    ->where('part_no', $batch->material_code)
                    ->increment('welding_stock', $batch->qty_hasil_ok, ['updated_at' => now()]);

                DB::table('production_logs')->insert([
                    'part_no'      => $batch->material_code,
                    'qty'          => $batch->qty_hasil_ok,
                    'process_type' => 'WELDING', 
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            } else {
                DB::table('finished_goods')
                    ->where('part_no', $batch->material_code)
                    ->increment('stock', $batch->qty_hasil_ok, ['updated_at' => now()]);
            }

            $this->syncToActual($id);

            DB::commit();
            return redirect()->back()->with('success', "Batch Closed & Output Transferred.");

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

    /**
     * ✨ FITUR BARU: PRINT SURAT SERAH TERIMA MATERIAL (GUDANG RM -> PRODUKSI)
     */
    public function printSerahTerima($date, $shift, $line_code)
    {
        $dbShiftName = ($shift == 'S1') ? 'Pagi' : 'Malam';
        
        // Cari ID Plan untuk ditarik batch-nya
        $planIds = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where('line_code', $line_code)
            ->pluck('id');

        // Tarik data Batches lengkap sama Part & Materialnya
        $batches = DB::table('produksi_batches')
            ->leftJoin('rm_stocks', 'produksi_batches.rm_stock_id', '=', 'rm_stocks.id')
            ->leftJoin('parts', 'produksi_batches.material_code', '=', 'parts.part_no')
            ->whereIn('produksi_batches.plan_id', $planIds)
            ->where('produksi_batches.shift', $dbShiftName)
            ->select(
                'produksi_batches.*', 
                'rm_stocks.coil_id', 'rm_stocks.spec', 'rm_stocks.size', 'rm_stocks.material_name',
                'parts.part_name'
            )->get();

        if ($batches->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada Material Request untuk mesin ini.');
        }

        return view('PPIC.print_serah_terima', compact('batches', 'date', 'shift', 'line_code'));
    }
}