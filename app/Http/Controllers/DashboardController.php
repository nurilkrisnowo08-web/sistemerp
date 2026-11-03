<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $mode = $request->query('mode', 'summary'); 
        $selectedCustomer = $request->query('customer');

        // ==========================================
        // 1. DATA SUMMARY UMUM (KPI HEADER)
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

        $wipStockList = DB::table('finished_goods')
            ->where('welding_stock', '>', 0)
            ->select('part_no', 'welding_stock')
            ->get();
        
        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('qty', '>', 0)->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // ==========================================
        // 2. LOGIC MODE: DELIVERY (PENGIRIMAN & PO)
        // ==========================================
        $deliveryPerformance = 0; 
        $deliveryTrend = collect(); 
        $poSisa = collect(); 
        
        if ($mode == 'delivery' || $mode == 'summary') {
            $totalOrdered = DB::table('purchase_order_items')->sum('qty') ?: 1;
            $totalSent = DB::table('deliveries')->sum('qty_delivery') ?: 0;
            $deliveryPerformance = round(($totalSent / $totalOrdered) * 100, 1);

            $deliveryTrend = DB::table('deliveries')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')->orderBy('date', 'asc')->get();

            $poSisaQuery = DB::table('purchase_order_items')
                ->leftJoin('purchase_orders', 'purchase_order_items.po_id', '=', 'purchase_orders.id')
                ->select(
                    'purchase_orders.po_number',
                    'purchase_orders.customer_code',
                    'purchase_order_items.part_no',
                    'purchase_order_items.qty as target_qty',
                    'purchase_order_items.sent_qty',
                    DB::raw('(purchase_order_items.qty - purchase_order_items.sent_qty) as sisa_qty')
                )
                ->whereRaw('purchase_order_items.qty > purchase_order_items.sent_qty');
                
            if ($selectedCustomer) {
                $poSisaQuery->where('purchase_orders.customer_code', $selectedCustomer);
            }
            $poSisa = $poSisaQuery->get();
        }

        // ==========================================
        // 3. LOGIC MODE: SUMMARY (INVENTORY MONITORING)
        // ==========================================
        $labels = []; $actStockData = []; $minStockData = []; 
        $rmLabels = []; $rmStockData = [];
        $wipLabels = []; $wipData = [];
        
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        
        if ($mode == 'summary') {
            // ✨ A. GRAFIK FINISHED GOODS (FG)
            $queryChart = DB::table('finished_goods')
                ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
                ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
            
            if ($selectedCustomer) {
                $queryChart->where('parts.customer_code', $selectedCustomer);
            } else {
                $queryChart->limit(10); 
            }
            $chartData = $queryChart->get();
            $labels = $chartData->pluck('part_no')->toArray();
            $actStockData = $chartData->pluck('actual_stock')->toArray();
            $minStockData = $chartData->pluck('min_stock_pcs')->toArray();

            // ✨ B. GRAFIK RAW MATERIAL (RM)
            $queryRm = DB::table('rm_stocks')
                ->select('material_code', DB::raw('SUM(stock_pcs) as total_stock'))
                ->where('stock_pcs', '>', 0);
                
            if ($selectedCustomer) {
                $queryRm->where('customer', $selectedCustomer);
            } else {
                $queryRm->limit(10);
            }
            $rmChart = $queryRm->groupBy('material_code')->get();
            $rmLabels = $rmChart->pluck('material_code')->toArray();
            $rmStockData = $rmChart->pluck('total_stock')->toArray();

            // ✨ C. GRAFIK WORK IN PROGRESS (WIP WELDING)
            $queryWip = DB::table('finished_goods')
                ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
                ->select('finished_goods.part_no', 'finished_goods.welding_stock')
                ->where('finished_goods.welding_stock', '>', 0);
                
            if ($selectedCustomer) {
                $queryWip->where('parts.customer_code', $selectedCustomer);
            } else {
                $queryWip->limit(10);
            }
            $wipChart = $queryWip->get();
            $wipLabels = $wipChart->pluck('part_no')->toArray();
            $wipData = $wipChart->pluck('welding_stock')->toArray();
        }

        // ==========================================
        // 4. KEMBALIKAN KE VIEW
        // ==========================================
        return view('dashboard', compact(
            'totalParts', 'critCount', 'todayProd', 'todayDelv', 'mode', 
            'deliveryPerformance', 'deliveryTrend', 
            'labels', 'actStockData', 'minStockData', // FG Chart
            'rmLabels', 'rmStockData', // RM Chart
            'wipLabels', 'wipData', // WIP Chart
            'customersList', 'selectedCustomer',
            'shortageParts', 'shortageRm', 'wipStockList', 'poSisa'
        ));
    }
}