<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PPICController extends Controller
{
    /**
     * 1. DASHBOARD UTAMA
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $today = date('Y-m-d');
        
        $plans = DB::table('production_plans')->where('plan_date', $date)->get();

        $statusCount = ['waiting' => 0, 'running' => 0, 'completed' => 0, 'shortage' => 0];
        $chartLabels = []; $chartTargets = []; $chartActuals = [];

        foreach($plans as $p) {
            $targetPerPart = ($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            
            $actualPerPart = DB::table('produksi_batches')
                ->where('material_code', $p->part_no)
                ->whereDate('created_at', $date)
                ->where('mesin_id', function($query) use ($p) {
                    $query->select('id')->from('line')->where('kode_Line', $p->line_code);
                })
                ->sum('qty_hasil_ok');
            
            $p->actual_qty = (int)$actualPerPart;
            $p->plan_qty = (int)$targetPerPart;

            $chartLabels[] = $p->part_no;
            $chartTargets[] = (int)$targetPerPart;
            $chartActuals[] = (int)$actualPerPart;

            if($targetPerPart > 0 && $actualPerPart >= $targetPerPart) { 
                $statusCount['completed']++; 
            } elseif ($date < $today && $actualPerPart < $targetPerPart) { 
                $statusCount['shortage']++; 
            } elseif ($actualPerPart > 0) { 
                $statusCount['running']++; 
            } else { 
                $statusCount['waiting']++; 
            }
        }

        $totalPlan = $plans->sum('plan_qty') ?: 0;
        $totalActual = $plans->sum('actual_qty') ?: 0;
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $monthlyLabels = []; $monthlyActuals = [];
        for ($i = 5; $i >= 0; $i--) {
            $mDate = date('Y-m', strtotime("-$i months"));
            $monthlyLabels[] = date('M', strtotime("-$i months"));
            $totalMonth = DB::table('produksi_batches')->where('created_at', 'LIKE', "$mDate%")->sum('qty_hasil_ok');
            $monthlyActuals[] = (int)$totalMonth;
        }

        return view('PPIC.ppic_planning', compact(
            'plans', 'statusCount', 'achievementRate', 'date', 'totalPlan', 
            'totalActual', 'chartLabels', 'chartTargets', 'chartActuals', 
            'monthlyLabels', 'monthlyActuals'
        ));
    }

    /**
     * 2. DAILY MPS (Penerima Data dari Monthly Matrix)
     */
    public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        
        $plans = DB::table('production_plans')
            ->where('plan_date', $date)
            ->where(DB::raw('s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot'), '>', 0)
            ->orderBy('id', 'asc')
            ->get();

        $totalDandory = 0; $totalPlanQty = 0; $totalWorkingHours = 0;
        $lineFinishTime = []; 

        foreach($plans as $plan) {
            $actualData = DB::table('produksi_batches')
                ->where('material_code', $plan->part_no) 
                ->where('mesin_id', function($query) use ($plan) {
                    $query->select('id')->from('line')->where('kode_Line', $plan->line_code);
                })
                ->whereDate('created_at', $date)
                ->select(
                    DB::raw("SUM(CASE WHEN shift = 'Pagi' THEN qty_hasil_ok ELSE 0 END) as s1_act"),
                    DB::raw("SUM(CASE WHEN shift != 'Pagi' THEN qty_hasil_ok ELSE 0 END) as s2_act")
                )->first();

            $plan->s1_actual = $actualData->s1_act ?? 0;
            $plan->s2_actual = $actualData->s2_act ?? 0;
            
            $plan->total_target = ($plan->s1_plan_reg + $plan->s1_plan_ot + $plan->s2_plan_reg + $plan->s2_plan_ot);
            $plan->total_actual = ($plan->s1_actual + $plan->s2_actual);
            
            $dandoryH = ($plan->dandory_time ?? 0) / 60;
            $duration = ($plan->cap_per_hour > 0 && $plan->total_target > 0) ? ($plan->total_target / $plan->cap_per_hour) + $dandoryH : 0;
            
            $startTime = $lineFinishTime[$plan->line_code] ?? "07:30";
            $plan->start_time = $startTime;
            $plan->ahir_time = date('H:i', strtotime($startTime . " + " . round($duration * 60) . " minutes"));
            $lineFinishTime[$plan->line_code] = $plan->ahir_time; 

            $plan->balance = $plan->total_target - $plan->total_actual;

            $totalDandory += $plan->dandory_time;
            $totalPlanQty += $plan->total_target;
            $totalWorkingHours += $duration;
        }

        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        return view('PPIC.mps_index', compact(
            'plans', 'date', 'availableLines', 'availableCustomers', 
            'totalDandory', 'totalPlanQty', 'totalWorkingHours'
        ));
    }

    /**
     * 3. EMERGENCY STORE (Manual Entry)
     */
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
     * 5. MONTHLY MASTER MATRIX (KAMAR UTAMA INPUT)
     */
    public function monthlyMatrix(Request $request)
    {
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // ✅ FIX: Ambil kolom yang benar-benar ada di tabel 'parts' Bapak
        $parts = DB::table('parts')
            ->select('part_no', 'customer_code', 'part_name')
            ->get();

        $planData = DB::table('production_plans')
            ->whereMonth('plan_date', $month)
            ->whereYear('plan_date', $year)
            ->get()
            ->groupBy('part_no');

        return view('PPIC.monthly_matrix', compact('parts', 'planData', 'month', 'year', 'daysInMonth'));
    }

    /**
     * 6. ✨ AJAX AUTO-SAVE (Mesin Registrasi)
     * Saat Bapak ngetik di Matrix, data harian otomatis terbuat.
     */
    public function saveMatrixAjax(Request $request)
    {
        $date = $request->year .'-'. str_pad($request->month, 2, '0', STR_PAD_LEFT) .'-'. str_pad($request->day, 2, '0', STR_PAD_LEFT);
        $shift = $request->shift; 
        $column = ($shift == 's2') ? 's2_plan_reg' : 's1_plan_reg';

        // Update database Planning Harian berdasar input Matrix Bulanan
        DB::table('production_plans')->updateOrInsert(
            [
                'plan_date' => $date, 
                'part_no'   => $request->part_no
            ],
            [
                'customer_code' => $request->customer_code,
                'line_code'     => $request->line_code ?? 'LINE A',
                $column         => $request->qty, 
                
                // Standar data (Karena di tabel parts Bapak kolom ini tidak ada)
                'cap_per_hour'  => 320, 
                'dandory_time'  => 15, 
                'manpower'      => 8, 
                'process_qty'   => 4,
                'qty_lot'       => 200,
                
                'updated_at'    => now()
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function apiData()
    {
        $today = date('Y-m-d');
        $totalPlan = DB::table('production_plans')->where('plan_date', $today)->sum(DB::raw('s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot')) ?: 1;
        $totalActual = DB::table('produksi_batches')->whereDate('created_at', $today)->sum('qty_hasil_ok') ?: 0;
        return response()->json(['achievement' => round(($totalActual / $totalPlan) * 100, 1), 'totalPlan' => $totalPlan, 'totalActual' => $totalActual]);
    }
}