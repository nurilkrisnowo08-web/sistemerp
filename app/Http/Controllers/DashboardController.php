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
        
        $shortageRm = DB::table('rm_stocks')
            ->where('stock_pcs', '<=', 100) 
            ->select('coil_id', 'material_code', 'stock_pcs')
            ->get();

        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('qty', '>', 0)->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // ==========================================
        // 3. GRAFIK PRODUKSI: WOS PLAN VS ACTUAL PER PART
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
        if ($selectedCustomer) $prodPlansQuery->where('customer_code', $selectedCustomer);
        $dailyPlans = $prodPlansQuery->get();
        
        $groupedProd = [];
        foreach ($dailyPlans as $p) {
            $target = $p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot;
            if(!isset($groupedProd[$p->part_no])) $groupedProd[$p->part_no] = 0;
            $groupedProd[$p->part_no] += $target;
        }

        $prodLabels = []; $prodTargets = []; $prodActuals = []; $prodNgs = [];
        foreach ($groupedProd as $part => $tgt) {
            $actOk = DB::table('production_actuals')
                        ->where('part_no', $part)->whereDate('created_at', $today)
                        ->where('line_code', '!=', 'WELDING AREA')->sum('qty_ok');
            $actNg = DB::table('production_actuals')
                        ->where('part_no', $part)->whereDate('created_at', $today)
                        ->where('line_code', '!=', 'WELDING AREA')->sum('qty_ng');

            $prodLabels[] = $part;
            $prodTargets[] = (int)$tgt;
            $prodActuals[] = (int)$actOk;
            $prodNgs[] = (int)$actNg; 
        }

        // ==========================================
        // 4. WOS DELIVERY LOGISTICS (JADWAL PENGIRIMAN)
        // ==========================================
        $wosDeliveryData = [];
        if (Schema::hasTable('delivery_plans')) {
            try {
                $delvPlansQuery = DB::table('delivery_plans')
                    ->leftJoin('parts', 'delivery_plans.part_no', '=', 'parts.part_no')
                    ->leftJoin('finished_goods', 'delivery_plans.part_no', '=', 'finished_goods.part_no')
                    ->select(
                        'delivery_plans.*', 'parts.part_name', 'parts.customer_code',
                        'finished_goods.actual_stock as fg_rak'
                    )
                    ->where('delivery_plans.plan_date', $today);
                
                if ($selectedCustomer) $delvPlansQuery->where('parts.customer_code', $selectedCustomer);
                
                $rawWosDelv = $delvPlansQuery->orderBy('delivery_plans.id', 'asc')->get();

                foreach ($rawWosDelv as $wd) {
                    $actShift1 = DB::table('deliveries')
                        ->where('part_no', $wd->part_no)->whereDate('created_at', $today)
                        ->whereTime('created_at', '<', '16:00:00')->sum('qty_delivery');
                    $actShift2 = DB::table('deliveries')
                        ->where('part_no', $wd->part_no)->whereDate('created_at', $today)
                        ->whereTime('created_at', '>=', '16:00:00')->sum('qty_delivery');

                    $totalActual = $actShift1 + $actShift2;
                    $targetQty = ($wd->blnc_delv ?? 0) + ($wd->plan_delv ?? 0); 
                    
                    $wd->actual_s1 = (int)$actShift1;
                    $wd->actual_s2 = (int)$actShift2;
                    $wd->total_target = (int)$targetQty;
                    $wd->status_judge = ($totalActual >= $targetQty && $targetQty > 0) ? 'OK' : 'SHORT';
                    
                    $wosDeliveryData[] = $wd;
                }
            } catch (\Exception $e) {
                $wosDeliveryData = [];
            }
        }

        // ==========================================
        // 5. CHART DELIVERY HARI INI (TARGET WOS VS ACTUAL SJ PER PART)
        // ==========================================
        $deliveryChartLabels = []; $deliveryChartActual = []; $deliveryChartTarget = [];
        try {
            $delvDataQuery = DB::table('deliveries')
                ->leftJoin('parts', 'deliveries.part_no', '=', 'parts.part_no')
                ->select('deliveries.part_no', DB::raw('SUM(deliveries.qty_delivery) as total'))
                ->whereDate('deliveries.created_at', $today)
                ->groupBy('deliveries.part_no');
            if ($selectedCustomer) $delvDataQuery->where('parts.customer_code', $selectedCustomer);
            $delvData = $delvDataQuery->pluck('total', 'part_no')->toArray();

            $targetDelvData = [];
            if (Schema::hasTable('delivery_plans')) { 
                try {
                    $tgtQuery = DB::table('delivery_plans')
                        ->leftJoin('parts', 'delivery_plans.part_no', '=', 'parts.part_no')
                        ->select('delivery_plans.part_no', DB::raw('SUM(COALESCE(delivery_plans.blnc_delv, 0) + COALESCE(delivery_plans.plan_delv, 0)) as total'))
                        ->where('delivery_plans.plan_date', $today)
                        ->groupBy('delivery_plans.part_no');
                    if ($selectedCustomer) $tgtQuery->where('parts.customer_code', $selectedCustomer);
                    $targetDelvData = $tgtQuery->pluck('total', 'part_no')->toArray();
                } catch (\Exception $e) { }
            }
            
            if (empty($targetDelvData)) {
                $poQuery = DB::table('purchase_orders')
                    ->select('part_no', DB::raw('SUM(quantity) as total'))
                    ->whereDate('due_date', $today)
                    ->groupBy('part_no');
                if ($selectedCustomer) $poQuery->where('customer_code', $selectedCustomer);
                $targetDelvData = $poQuery->pluck('total', 'part_no')->toArray();
            }

            $allDelvParts = array_unique(array_merge(array_keys($delvData), array_keys($targetDelvData)));
            
            foreach ($allDelvParts as $part) {
                $deliveryChartLabels[] = $part;
                $deliveryChartActual[] = (int)($delvData[$part] ?? 0);
                $deliveryChartTarget[] = (int)($targetDelvData[$part] ?? 0);
            }
        } catch (\Exception $e) { }

        // ==========================================
        // 6. CHART INVENTORY (FG, RM, WIP)
        // ==========================================
        $labels = []; $actStockData = []; $minStockData = []; 
        $rmLabels = []; $rmStockData = []; $rmMinData = [];
        $wipLabels = []; $wipData = []; $wipMinData = [];
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        
        $queryChart = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
        if ($selectedCustomer) $queryChart->where('parts.customer_code', $selectedCustomer);
        else $queryChart->limit(10); 
        $chartData = $queryChart->get();
        $labels = $chartData->pluck('part_no')->toArray();
        $actStockData = $chartData->pluck('actual_stock')->toArray();
        $minStockData = $chartData->pluck('min_stock_pcs')->toArray();

        $queryRm = DB::table('rm_stocks')->select('material_code', DB::raw('SUM(stock_pcs) as total_stock'))->where('stock_pcs', '>', 0);
        if ($selectedCustomer) $queryRm->where('customer', $selectedCustomer);
        else $queryRm->limit(10);
        $rmChart = $queryRm->groupBy('material_code')->get();
        $rmLabels = $rmChart->pluck('material_code')->toArray();
        $rmStockData = $rmChart->pluck('total_stock')->toArray();
        $rmMinData = array_fill(0, count($rmLabels), 100);

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
            'prodLabels', 'prodTargets', 'prodActuals', 'prodNgs', 
            'wosDeliveryData', 'deliveryChartLabels', 'deliveryChartActual', 'deliveryChartTarget',
            'labels', 'actStockData', 'minStockData', 
            'rmLabels', 'rmStockData', 'rmMinData', 
            'wipLabels', 'wipData', 'wipMinData', 
            'customersList', 'selectedCustomer',
            'shortageParts', 'shortageRm'
        ));
    }
}