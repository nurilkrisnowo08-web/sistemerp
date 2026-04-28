<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PPICController extends Controller
{
    /**
     * 1. DASHBOARD UTAMA (Intelligence Command Center - STAMPING)
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $today = date('Y-m-d');
        
        // Ambil daftar kendala produksi (Status PROBLEM) untuk notifikasi PPIC
        $alerts = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->where('produksi_batches.status', 'PROBLEM')
            ->select(
                'produksi_batches.id',
                'produksi_batches.no_produksi', 
                'produksi_batches.material_code', 
                'line.kode_Line', 
                'produksi_batches.keterangan', 
                'produksi_batches.updated_at'
            )
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

            if($targetPerPart > 0 && $actualPerPart >= $targetPerPart) { $statusCount['completed']++; }
            elseif ($date < $today && $actualPerPart < $targetPerPart) { $statusCount['shortage']++; }
            elseif ($actualPerPart > 0) { $statusCount['running']++; }
            else { $statusCount['waiting']++; }
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

        $plans = $query->orderBy('id', 'asc')->get();
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
            
            $dandoryH = ($plan->dandory_time ?? 0) / 60;
            $duration = ($plan->cap_per_hour > 0 && $plan->total_target > 0) ? ($plan->total_target / $plan->cap_per_hour) + $dandoryH : 0;
            
            $startTime = $lineFinishTime[$plan->line_code] ?? $defaultStart;
            $plan->start_time = $startTime;
            $plan->ahir_time = date('H:i', strtotime($startTime . " + " . round($duration * 60) . " minutes"));
            $lineFinishTime[$plan->line_code] = $plan->ahir_time; 
            $plan->balance = $plan->total_target - $plan->total_actual;
        }

        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        return view('PPIC.mps_index', compact('plans', 'date', 'availableLines', 'availableCustomers'))->with('shift', $shiftParam);
    }

    public function mpsStore(Request $request)
    {
        DB::table('production_plans')->updateOrInsert(
            ['plan_date' => $request->plan_date, 'part_no' => $request->part_no],
            [
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
                'updated_at'    => now()
            ]
        );
        return redirect()->back()->with('success', 'Master Schedule Updated!');
    }

    /**
     * 4. QUALITY CONTROL HUB (STAMPING)
     */
    public function qualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $summary = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', '!=', 'WELDING AREA')
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))
            ->first();

        $ngRanking = DB::table('production_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->groupBy('ng_type')->orderBy('total', 'DESC')->get();

        $details = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', '!=', 'WELDING AREA')
            ->orderBy('created_at', 'DESC')->get();

        foreach($details as $d) {
            $d->batches = DB::table('produksi_batches')
                ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
                ->where('material_code', $d->part_no)
                ->where('shift', $d->shift)
                ->whereDate('produksi_batches.created_at', $date)
                ->select('no_produksi', 'qty_ambil_pcs', 'qty_hasil_ok', 'qty_hasil_ng', 'kode_Line')
                ->get();
        }

        return view('PPIC.quality_hub', compact('summary', 'ngRanking', 'details', 'date'));
    }

    /**
     * 5. MONTHLY MASTER MATRIX (STAMPING)
     */
    public function monthlyMatrix(Request $request)
    {
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $parts = DB::table('parts')->select('part_no', 'customer_code', 'part_name')->get();

        $planData = DB::table('production_plans')->whereMonth('plan_date', $month)->whereYear('plan_date', $year)->get()->groupBy('part_no');
        $actualData = DB::table('production_actuals')
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->where('line_code', '!=', 'WELDING AREA')
            ->select('part_no', DB::raw('DAY(created_at) as day'), DB::raw('SUM(qty_ok) as total_ok'))
            ->groupBy('part_no', 'day')->get()->groupBy('part_no');

        return view('PPIC.monthly_matrix', compact('parts', 'planData', 'actualData', 'month', 'year', 'daysInMonth'));
    }

    public function saveMatrixAjax(Request $request)
    {
        $date = $request->year .'-'. str_pad($request->month, 2, '0', STR_PAD_LEFT) .'-'. str_pad($request->day, 2, '0', STR_PAD_LEFT);
        $shift = $request->shift; 
        $column = ($shift == 's2') ? 's2_plan_reg' : 's1_plan_reg';

        DB::table('production_plans')->updateOrInsert(
            ['plan_date' => $date, 'part_no' => $request->part_no],
            ['customer_code' => $request->customer_code, 'line_code' => $request->line_code ?? 'LINE A', $column => $request->qty, 'updated_at' => now()]
        );
        return response()->json(['status' => 'success']);
    }

    public function apiData()
    {
        $today = date('Y-m-d');
        $totalPlan = DB::table('production_plans')->where('plan_date', $today)->sum(DB::raw('s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot')) ?: 1;
        $totalActual = DB::table('production_actuals')->whereDate('created_at', $today)->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok') ?: 0;
        return response()->json(['achievement' => round(($totalActual / $totalPlan) * 100, 1), 'totalPlan' => $totalPlan, 'totalActual' => $totalActual]);
    }

    public function resumeBatch($id)
    {
        DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROSES', 'updated_at' => now()]);
        return redirect()->back()->with('success', 'Batch diaktifkan kembali.');
    }

    public function closeBatch($id)
    {
        DB::beginTransaction();
        try {
            $batch = DB::table('produksi_batches')->where('id', $id)->first();
            $sisa = (int)$batch->qty_ambil_pcs - ((int)$batch->qty_hasil_ok + (int)$batch->qty_hasil_ng);
            if ($sisa > 0) { DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->increment('stock_pcs', $sisa); }
            DB::table('produksi_batches')->where('id', $id)->update(['status' => 'COMPLETED', 'qty_return_warehouse' => $sisa, 'updated_at' => now()]);
            $this->syncToActual($id);
            DB::commit();
            return redirect()->back()->with('success', "Batch ditutup! $sisa Pcs balik ke stok.");
        } catch (\Exception $e) { DB::rollBack(); return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }

    public function syncToActual($batchId)
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

    public function quickReschedule(Request $request, $id)
    {
        $oldPlan = DB::table('production_plans')->where('id', $id)->first();
        $actualSoFar = DB::table('production_actuals')->where('part_no', $oldPlan->part_no)->whereDate('created_at', $oldPlan->plan_date)->sum('qty_ok');
        DB::table('production_plans')->where('id', $id)->update(['s1_plan_reg' => $actualSoFar, 'remark' => '⚠️ STOPPED']);
        return redirect()->back()->with('success', 'Jadwal dihentikan.');
    }

    /**
     * ============================================================
     * ✨ BAGIAN BARU: WELDING INTELLIGENCE SYSTEM (PISAH TABEL)
     * ============================================================
     */

    public function weldingIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $alerts = DB::table('welding_batches')->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.status', 'PROBLEM')->select('welding_batches.*', 'line_welding.kode_line')->get();

        // Target dari welding_plans
        $plans = DB::table('welding_plans')->where('plan_date', $date)->get();
        foreach($plans as $p) {
            // Hasil dari welding_actuals
            $actual = DB::table('welding_actuals')->where('part_no', $p->part_no)->whereDate('created_at', $date)->sum('qty_ok');
            $p->actual_qty = (int)$actual;
            $p->plan_qty = (int)($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
        }

        $totalPlan = $plans->sum('plan_qty') ?: 0;
        $totalActual = $plans->sum('actual_qty') ?: 0;
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        return view('PPIC.welding_planning', compact('plans', 'achievementRate', 'date', 'totalPlan', 'totalActual', 'alerts'));
    }

    public function weldingMps(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $plans = DB::table('welding_plans')->where('plan_date', $date)->orderBy('id', 'asc')->get();

        foreach($plans as $plan) {
            $actualData = DB::table('welding_actuals')->where('part_no', $plan->part_no)->whereDate('created_at', $date)->sum('qty_ok');
            $plan->total_actual = (int)$actualData;
            $plan->total_target = ($request->shift == 'S2') ? ($plan->s2_plan_reg + $plan->s2_plan_ot) : ($plan->s1_plan_reg + $plan->s1_plan_ot);
            $plan->balance = $plan->total_target - $plan->total_actual;
        }

        $availableLines = DB::table('line_welding')->get();
        $availableCustomers = DB::table('customers')->get();
        return view('PPIC.welding_mps', compact('plans', 'date', 'availableLines', 'availableCustomers'))->with('shift', $request->shift ?? 'S1');
    }

    public function weldingMpsStore(Request $request)
    {
        DB::table('welding_plans')->updateOrInsert(
            ['plan_date' => $request->plan_date, 'part_no' => $request->part_no],
            [
                'customer_code' => $request->customer_code,
                'line_code'     => $request->line_code,
                's1_plan_reg'   => $request->s1_plan_reg ?? 0,
                's2_plan_reg'   => $request->s2_plan_reg ?? 0,
                'updated_at'    => now()
            ]
        );
        return redirect()->back()->with('success', 'Welding Schedule Updated!');
    }

    public function weldingQualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $summary = DB::table('welding_actuals')
            ->whereDate('created_at', $date)
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))
            ->first();

        $ngRanking = DB::table('production_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->whereIn('ng_type', function($q) { $q->select('ng_name')->from('master_ngs')->where('category', 'WELDING'); })
            ->groupBy('ng_type')->orderBy('total', 'DESC')->get();

        $details = DB::table('welding_actuals')->whereDate('created_at', $date)->get();
        return view('PPIC.welding_quality_hub', compact('summary', 'ngRanking', 'details', 'date'));
    }
}