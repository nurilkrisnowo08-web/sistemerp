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

    // 1. DATA SUMMARY (Muncul di semua mode)
    $totalParts = DB::table('finished_goods')->count();
    $critCount = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->count();
    $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('process_type', 'FG')->sum('qty') ?? 0;
    $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;
    $totalPO = DB::table('purchase_orders')->distinct('po_number')->count('po_number');
    $pendingDelvCount = DB::table('deliveries')->where('status', 'DRAFT')->distinct('no_sj')->count('no_sj');

    // 2. ✨ LOGIC MODE: DELIVERY (Pusat Pantauan Pengiriman) rill
    $deliveryPerformance = 0; $deliveryTrend = collect(); $customerShipments = collect();
    $monthlyPerformance = 0;

    if ($mode == 'delivery') {
        // Performance Harian rill
        $totalOrdered = DB::table('purchase_order_items')->sum('qty') ?: 1;
        $totalSent = DB::table('deliveries')->sum('qty_delivery') ?: 0;
        $deliveryPerformance = round(($totalSent / $totalOrdered) * 100, 1);

        // Tren 30 Hari (Daily)
        $deliveryTrend = DB::table('deliveries')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        // Rincian Per Customer
        $customerShipments = DB::table('deliveries')
            ->leftJoin('customers', 'deliveries.customer_code', '=', 'customers.code')
            ->select('customers.name as customer_name', DB::raw('SUM(qty_delivery) as total_qty'), DB::raw('COUNT(DISTINCT no_sj) as total_sj'))
            ->groupBy('customers.name')->orderBy('total_qty', 'desc')->get();
    }

    // 3. LOGIC MODE: SUMMARY
    $labels = []; $actStockData = []; $minStockData = []; $permintaanStok = [];
    $customersList = DB::table('parts')->distinct()->pluck('customer_code');
    if ($mode == 'summary') {
        $queryChart = DB::table('finished_goods')->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
        if ($selectedCustomer) { $queryChart->where('parts.customer_code', $selectedCustomer); } else { $queryChart->limit(12); }
        $chartData = $queryChart->get();
        $labels = $chartData->pluck('part_no')->toArray();
        $actStockData = $chartData->pluck('actual_stock')->toArray();
        $minStockData = $chartData->pluck('min_stock_pcs')->toArray();
        $permintaanStok = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->limit(8)->get();
    }

    return view('dashboard', compact(
        'totalParts', 'critCount', 'todayProd', 'todayDelv', 'totalPO', 'pendingDelvCount',
        'labels', 'actStockData', 'minStockData', 'permintaanStok', 'customersList', 'selectedCustomer',
        'mode', 'deliveryPerformance', 'deliveryTrend', 'customerShipments'
    ));
}
}