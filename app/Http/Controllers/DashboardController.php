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

        // 1. DATA SUMMARY (Tetap enteng rill)
        $totalParts = DB::table('finished_goods')->count();
        $critCount = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->count();
        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('qty', '>', 0)->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // 2. LOGIC MODE: DELIVERY rill
        $deliveryPerformance = 0; $deliveryTrend = collect(); $customerShipments = collect();
        if ($mode == 'delivery') {
            $totalOrdered = DB::table('purchase_order_items')->sum('qty') ?: 1;
            $totalSent = DB::table('deliveries')->sum('qty_delivery') ?: 0;
            // Rumus: $$ \text{Performance} = \left( \frac{\text{Total Sent}}{\text{Total Ordered}} \right) \times 100 $$
            $deliveryPerformance = round(($totalSent / $totalOrdered) * 100, 1);

            $deliveryTrend = DB::table('deliveries')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')->orderBy('date', 'asc')->get();
        }

        // 3. LOGIC MODE: SUMMARY (Inventory Monitor)
        $labels = []; $actStockData = []; $minStockData = []; $permintaanStok = [];
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        if ($mode == 'summary') {
            $queryChart = DB::table('finished_goods')->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
            if ($selectedCustomer) {
                $queryChart->where('parts.customer_code', $selectedCustomer);
            } else {
                $queryChart->limit(8); // Di HP jangan kebanyakan bar rill biar gak pusing
            }
            $chartData = $queryChart->get();
            $labels = $chartData->map(fn($item) => $item->part_no)->toArray();
            $actStockData = $chartData->pluck('actual_stock')->toArray();
            $minStockData = $chartData->pluck('min_stock_pcs')->toArray();
        }

        return view('dashboard', compact('totalParts', 'critCount', 'todayProd', 'todayDelv', 'mode', 'deliveryPerformance', 'deliveryTrend', 'labels', 'actStockData', 'minStockData', 'customersList', 'selectedCustomer'));
    }
}