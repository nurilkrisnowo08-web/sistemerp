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
    public function index()
    {
        $plans = DB::table('production_plans')->get();

        $statusCount = [
            'waiting'   => DB::table('production_plans')->whereRaw('(s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot) > 0')->count(),
            'running'   => 0, 
            'completed' => 0, 
        ];

        // Hitung Total Plan (S1 + S2)
        $totalPlan = DB::table('production_plans')->select(DB::raw('SUM(s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot) as total'))->first()->total ?: 1;
        
        // FIX: Ganti 'qty_ok' jadi 'qty_hasil_ok' sesuai database Bapak
        $totalActual = DB::table('produksi_batches')->sum('qty_hasil_ok'); 
        $achievementRate = round(($totalActual / $totalPlan) * 100, 1);

        $stockRisks = [
            'critical' => DB::table('rm_stocks')->whereColumn('stock_pcs', '<=', 'min_stock')->count(),
            'warning'  => DB::table('rm_stocks')->whereRaw('stock_pcs > min_stock AND stock_pcs <= (min_stock * 1.5)')->count(),
            'safe'     => DB::table('rm_stocks')->whereColumn('stock_pcs', '>', DB::raw('min_stock * 1.5'))->count(),
        ];

        $monthlyData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'target' => [10000, 12000, 15000, 14000, 16000, 18000],
            'actual' => [9500, 11800, 14200, 14500, 15800, 17500]
        ];

        return view('Gudang.ppic_planning', compact('plans', 'statusCount', 'achievementRate', 'stockRisks', 'monthlyData'));
    }

    /**
     * 2. JADWAL PRODUKSI (MPS)
     */
    public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $plans = DB::table('production_plans')->where('plan_date', $date)->get();

        foreach($plans as $plan) {
            // FIX: Sesuaikan nama kolom 'qty_hasil_ok' dan value shift 'Pagi'/'Malam'
            $actualData = DB::table('produksi_batches')
                ->where('part_no', $plan->part_no)
                ->where('line_code', $plan->line_code)
                ->whereDate('created_at', $date)
                ->select(
                    DB::raw("SUM(CASE WHEN shift = 'Pagi' THEN qty_hasil_ok ELSE 0 END) as s1_act"),
                    DB::raw("SUM(CASE WHEN shift != 'Pagi' THEN qty_hasil_ok ELSE 0 END) as s2_act")
                )->first();

            $plan->s1_actual = $actualData->s1_act ?? 0;
            $plan->s2_actual = $actualData->s2_act ?? 0;
            
            $plan->s1_total_target = $plan->s1_plan_reg + $plan->s1_plan_ot;
            $plan->s2_total_target = $plan->s2_plan_reg + $plan->s2_plan_ot;
            
            $plan->s1_hour = ($plan->cap_per_hour > 0) ? round($plan->s1_total_target / $plan->cap_per_hour, 1) : 0;
            $plan->s2_hour = ($plan->cap_per_hour > 0) ? round($plan->s2_total_target / $plan->cap_per_hour, 1) : 0;

            // Hitung sisa (Balance)
            $plan->s1_balance = $plan->s1_total_target - $plan->s1_actual;
            $plan->s2_balance = $plan->s2_total_target - $plan->s2_actual;
        }

        // Gunakan nama kolom 'kode_Line' sesuai foto DB sebelumnya
        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();

        return view('PPIC.mps_index', compact('plans', 'date', 'availableLines', 'availableCustomers'));
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
        $statusCount = [
            'waiting'   => DB::table('production_plans')->count(),
            'running'   => 0,
            'completed' => 0,
        ];

        $totalPlan = DB::table('production_plans')->select(DB::raw('SUM(s1_plan_reg + s1_plan_ot + s2_plan_reg + s2_plan_ot) as total'))->first()->total ?: 1;
        
        // FIX: Ganti 'qty_ok' jadi 'qty_hasil_ok'
        $totalActual = DB::table('produksi_batches')->sum('qty_hasil_ok');
        $achievement = round(($totalActual / $totalPlan) * 100, 1);

        $stockRisks = [
            'critical' => DB::table('rm_stocks')->whereColumn('stock_pcs', '<=', 'min_stock')->count(),
            'warning'  => DB::table('rm_stocks')->whereRaw('stock_pcs > min_stock AND stock_pcs <= (min_stock * 1.5)')->count(),
            'safe'     => DB::table('rm_stocks')->whereColumn('stock_pcs', '>', DB::raw('min_stock * 1.5'))->count(),
        ];

        return response()->json([
            'statusCount' => $statusCount,
            'achievement' => $achievement,
            'stockRisks'  => $stockRisks,
            'totalPlan'   => $totalPlan,
            'totalActual' => $totalActual
        ]);
    }
}