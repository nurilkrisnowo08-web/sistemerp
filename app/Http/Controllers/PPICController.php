<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PPICController extends Controller
{
    public function index()
{
    // 1. Ambil Semua Data Planning
    $plans = DB::table('production_plans')->get();

    // 2. Summary Status (Donut Chart Logic)
    $statusCount = [
        'waiting'   => DB::table('production_plans')->where('status', 'WAITING')->count(),
        'running'   => DB::table('production_plans')->where('status', 'RUNNING')->count(),
        'completed' => DB::table('production_plans')->where('status', 'COMPLETED')->count(),
    ];

    // 3. Overall Achievement (Actual vs Target)
    $totalPlan = DB::table('production_plans')->sum('plan_qty') ?: 1;
    $totalActual = DB::table('production_plans')->sum('actual_qty');
    $achievementRate = round(($totalActual / $totalPlan) * 100, 1);

    // 4. Stock Risk Analysis (RM yang di bawah Min Stock)
    $stockRisks = [
        'critical' => DB::table('rm_stocks')->whereColumn('stock_pcs', '<=', 'min_stock')->count(),
        'warning'  => DB::table('rm_stocks')->whereRaw('stock_pcs > min_stock AND stock_pcs <= (min_stock * 1.5)')->count(),
        'safe'     => DB::table('rm_stocks')->whereColumn('stock_pcs', '>', DB::raw('min_stock * 1.5'))->count(),
    ];

    // 5. Data Grafik Bulanan (Output Produksi 6 Bulan Terakhir)
    $monthlyData = [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        'target' => [10000, 12000, 15000, 14000, 16000, 18000],
        'actual' => [9500, 11800, 14200, 14500, 15800, 17500]
    ];

    return view('Gudang.ppic_planning', compact('plans', 'statusCount', 'achievementRate', 'stockRisks', 'monthlyData'));
}

public function apiData()
{
    // Hitung status order terbaru
    $statusCount = [
        'waiting'   => DB::table('production_plans')->where('status', 'WAITING')->count(),
        'running'   => DB::table('production_plans')->where('status', 'RUNNING')->count(),
        'completed' => DB::table('production_plans')->where('status', 'COMPLETED')->count(),
    ];

    // Hitung Achievement Rill
    $totalPlan = DB::table('production_plans')->sum('plan_qty') ?: 1;
    $totalActual = DB::table('production_plans')->sum('actual_qty');
    $achievement = round(($totalActual / $totalPlan) * 100, 1);

    // Hitung Resiko Stok dari Warehouse RM
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