<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. TANGKAP SEMUA FILTER DARI BLADE
        $today = $request->date ?? date('Y-m-d');
        $selectedCustomer = $request->query('customer');
        $areaFilter = $request->query('area', 'all');

        // ==========================================
        // 2. DATA SUMMARY UMUM (KPI HEADER & TICKER)
        // ==========================================
        $totalParts = DB::table('finished_goods')->count();
        
        $shortageParts = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.*', 'parts.part_name', 'parts.customer_code')
            ->whereRaw('finished_goods.actual_stock <= finished_goods.min_stock_pcs')
            ->get();
        $critCount = $shortageParts->count(); 
        
        // RM Threshold disetel 100 lembar sebagai batas kritis default
        $shortageRm = DB::table('rm_stocks')
            ->where('stock_pcs', '<=', 100) 
            ->select('coil_id', 'material_code', 'stock_pcs')
            ->get();

        $wipStockList = DB::table('finished_goods')
            ->where('welding_stock', '>', 0)
            ->select('part_no', 'welding_stock')
            ->get();
        
        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('qty', '>', 0)->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // ==========================================
        // 3. MENGGANTIKAN SISA PO -> GRAFIK PRODUKSI WOS VS ACTUAL
        // ==========================================
        $prodPlansQuery = DB::table('production_plans')->where('plan_date', $today);
        if ($areaFilter === 'small') {
            $prodPlansQuery->where(function($q) {
                $q->where('line_code', 'LIKE', '%C%')->orWhere('line_code', 'LIKE', '%SMALL%');
            });
        } elseif ($areaFilter === 'big') {
            $prodPlansQuery->where(function($q) {
                $q->where('line_code', 'NOT LIKE', '%C%')->where('line_code', 'NOT LIKE', '%SMALL%');
            });
        }
        if ($selectedCustomer) {
            $prodPlansQuery->where('customer_code', $selectedCustomer);
        }
        $dailyPlans = $prodPlansQuery->get();
        
        $prodLabels = []; $prodTargets = []; $prodActuals = [];
        foreach ($dailyPlans as $p) {
            $target = $p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot;
            $actual = DB::table('production_actuals')
                        ->where('part_no', $p->part_no)
                        ->whereDate('created_at', $today)
                        ->where('line_code', '!=', 'WELDING AREA')
                        ->sum('qty_ok');
            
            $idx = array_search($p->part_no, $prodLabels);
            if ($idx === false) {
                $prodLabels[] = $p->part_no;
                $prodTargets[] = (int)$target;
                $prodActuals[] = (int)$actual;
            } else {
                $prodTargets[$idx] += (int)$target;
            }
        }

        // ==========================================
        // 4. DELIVERY: WOS DELIVERY (TARGET) VS ACTUAL PENGIRIMAN
        // ==========================================
        $deliveryTrend = collect();
        try {
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days', strtotime($today)));
            
            // Tarik Actual Pengiriman
            $delvData = DB::table('deliveries')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->where('created_at', '<=', $today . ' 23:59:59')
                ->groupBy('date')->pluck('total', 'date')->toArray();

            // Tarik WOS Delivery (Target Plan)
            $targetDelvData = [];
            if (Schema::hasTable('delivery_plans')) { // Cek jika punya tabel khusus delivery_plans
                $targetDelvData = DB::table('delivery_plans')
                    ->select('plan_date as date', DB::raw('SUM(target_qty) as total'))
                    ->where('plan_date', '>=', $thirtyDaysAgo)
                    ->where('plan_date', '<=', $today)
                    ->groupBy('plan_date')->pluck('total', 'date')->toArray();
            } else { // Jika tidak ada, pakai fallback due_date dari purchase_orders
                $targetDelvData = DB::table('purchase_orders')
                    ->select(DB::raw('DATE(due_date) as date'), DB::raw('SUM(quantity) as total'))
                    ->where('due_date', '>=', $thirtyDaysAgo)
                    ->where('due_date', '<=', $today)
                    ->groupBy(DB::raw('DATE(due_date)'))->pluck('total', 'date')->toArray();
            }

            $currentDate = strtotime($thirtyDaysAgo);
            $endDate = strtotime($today);
            
            while ($currentDate <= $endDate) {
                $dateStr = date('Y-m-d', $currentDate);
                $deliveryTrend->push([
                    'date' => $dateStr,
                    'actual' => $delvData[$dateStr] ?? 0,
                    'target' => $targetDelvData[$dateStr] ?? 0,
                ]);
                $currentDate = strtotime('+1 day', $currentDate);
            }
        } catch (\Exception $e) { }

        // ==========================================
        // 5. CHART INVENTORY (FG, RM, WIP)
        // ==========================================
        $labels = []; $actStockData = []; $minStockData = []; 
        $rmLabels = []; $rmStockData = []; $rmMinData = [];
        $wipLabels = []; $wipData = []; $wipMinData = [];
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        
        // A. Chart FG
        $queryChart = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
        if ($selectedCustomer) $queryChart->where('parts.customer_code', $selectedCustomer);
        else $queryChart->limit(10); 
        $chartData = $queryChart->get();
        $labels = $chartData->pluck('part_no')->toArray();
        $actStockData = $chartData->pluck('actual_stock')->toArray();
        $minStockData = $chartData->pluck('min_stock_pcs')->toArray();

        // B. Chart RM
        $queryRm = DB::table('rm_stocks')->select('material_code', DB::raw('SUM(stock_pcs) as total_stock'))->where('stock_pcs', '>', 0);
        if ($selectedCustomer) $queryRm->where('customer', $selectedCustomer);
        else $queryRm->limit(10);
        $rmChart = $queryRm->groupBy('material_code')->get();
        $rmLabels = $rmChart->pluck('material_code')->toArray();
        $rmStockData = $rmChart->pluck('total_stock')->toArray();
        $rmMinData = array_fill(0, count($rmLabels), 100);

        // C. Chart WIP Welding
        $queryWip = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.part_no', 'finished_goods.welding_stock')
            ->where('finished_goods.welding_stock', '>', 0);
        if ($selectedCustomer) $queryWip->where('parts.customer_code', $selectedCustomer);
        else $queryWip->limit(10);
        $wipChart = $queryWip->get();
        $wipLabels = $wipChart->pluck('part_no')->toArray();
        $wipData = $wipChart->pluck('welding_stock')->toArray();
        $wipMinData = array_fill(0, count($wipLabels), 50);

        return view('dashboard', compact(
            'today', 'areaFilter',
            'totalParts', 'critCount', 'todayProd', 'todayDelv',
            'deliveryTrend', 
            'prodLabels', 'prodTargets', 'prodActuals', // Data Produksi WOS Harian
            'labels', 'actStockData', 'minStockData', 
            'rmLabels', 'rmStockData', 'rmMinData', 
            'wipLabels', 'wipData', 'wipMinData', 
            'customersList', 'selectedCustomer',
            'shortageParts', 'shortageRm', 'wipStockList'
        ));
    }
}