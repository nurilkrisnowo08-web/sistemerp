<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionPlan; 

class PPICController extends Controller
{
    /**
     * 1. DASHBOARD UTAMA (Intelligence Command Center)
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $today = date('Y-m-d');
        
        // Ambil data planning sesuai tanggal filter
        $plans = DB::table('production_plans')->where('plan_date', $date)->get();

        $statusCount = ['waiting' => 0, 'running' => 0, 'completed' => 0, 'shortage' => 0];
        $chartLabels = [];
        $chartTargets = [];
        $chartActuals = [];

        foreach($plans as $p) {
            $targetPerPart = ($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            
            // AMBIL ACTUAL: Filter material_code (Part) dan mesin_id (Line)
            $actualPerPart = DB::table('produksi_batches')
                ->where('material_code', $p->part_no) // Mapping ke material_code
                ->whereDate('created_at', $date)
                ->where('mesin_id', function($query) use ($p) {
                    $query->select('id')->from('line')->where('kode_Line', $p->line_code);
                })
                ->sum('qty_hasil_ok'); // Menggunakan qty_hasil_ok
            
            $p->actual_qty = (int)$actualPerPart;
            $p->plan_qty = (int)$targetPerPart;

            $chartLabels[] = $p->part_no;
            $chartTargets[] = (int)$targetPerPart;
            $chartActuals[] = (int)$actualPerPart;

            // Logika Status Berdasarkan Tanggal dan Achievement
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

        // DATA TREND 6 BULAN (RILL DARI DATABASE)
        $monthlyLabels = [];
        $monthlyActuals = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = date('Y-m', strtotime("-$i months"));
            $monthName = date('M', strtotime("-$i months"));
            $totalMonth = DB::table('produksi_batches')->where('created_at', 'LIKE', "$monthDate%")->sum('qty_hasil_ok');
            $monthlyActuals[] = (int)$totalMonth;
            $monthlyLabels[] = $monthName;
        }

        return view('PPIC.ppic_planning', compact(
            'plans', 'statusCount', 'achievementRate', 'date', 'totalPlan', 
            'totalActual', 'chartLabels', 'chartTargets', 'chartActuals', 
            'monthlyLabels', 'monthlyActuals'
        ));
    }

    /**
     * 2. JADWAL PRODUKSI (MPS - Industrial Style)
     */
   public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $plans = DB::table('production_plans')->where('plan_date', $date)->get();

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
            
            // Rumus Excel: (Plan / Cap Hour) + (Dandory / 60)
            $plan->s1_target = $plan->s1_plan_reg + $plan->s1_plan_ot;
            $plan->s2_target = $plan->s2_plan_reg + $plan->s2_plan_ot;
            
            $dandoryHour = $plan->dandory_time / 60;
            $plan->s1_mc_hour = ($plan->cap_per_hour > 0) ? round(($plan->s1_target / $plan->cap_per_hour) + $dandoryHour, 1) : 0;
            $plan->s2_mc_hour = ($plan->cap_per_hour > 0) ? round(($plan->s2_target / $plan->cap_per_hour) + $dandoryHour, 1) : 0;

            $plan->s1_balance = $plan->s1_target - $plan->s1_actual;
            $plan->s2_balance = $plan->s2_target - $plan->s2_actual;
        }

        // Hitung Total Jam Per Line untuk Footer (Baris Hijau Excel)
        $totalHoursS1 = $plans->sum('s1_mc_hour');
        $totalHoursS2 = $plans->sum('s2_mc_hour');

        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        return view('PPIC.mps_index', compact('plans', 'date', 'availableLines', 'availableCustomers', 'totalHoursS1', 'totalHoursS2'));
    }

    /**
     * 3. SIMPAN RENCANA PRODUKSI
     */
    public function mpsStore(Request $request)
    {
        DB::table('production_plans')->updateOrInsert(
            [
                'plan_date' => $request->plan_date,
                'part_no'   => $request->part_no,
                'line_code' => $request->line_code,
            ],
            [
                'customer_code' => $request->customer_code,
                'manpower'      => $request->manpower ?? 1,
                'cap_per_hour'  => $request->cap_per_hour ?? 0,
                's1_plan_reg'   => $request->s1_plan_reg ?? 0,
                's1_plan_ot'    => $request->s1_plan_ot ?? 0,
                's2_plan_reg'   => $request->s2_plan_reg ?? 0,
                's2_plan_ot'    => $request->s2_plan_ot ?? 0,
                'dandory_time'  => $request->dandory_time ?? 0,
                'remark'        => $request->remark,
                'updated_at'    => now()
            ]
        );

        return redirect()->back()->with('success', 'Plan Managed Successfully!');
    }

    /**
     * 4. API DATA UNTUK DASHBOARD
     */
    public function apiData()
    {
        $today = date('Y-m-d');
        
        $totalPlan = DB::table('production_plans')
            ->where('plan_date', $today)
            ->select(DB::raw('SUM(s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot) as total'))
            ->first()->total ?: 1;
        
        $totalActual = DB::table('produksi_batches')->whereDate('created_at', $today)->sum('qty_hasil_ok') ?: 0;
        
        return response()->json([
            'achievement' => round(($totalActual / $totalPlan) * 100, 1),
            'totalPlan'   => $totalPlan,
            'totalActual' => $totalActual
        ]);
    }
}