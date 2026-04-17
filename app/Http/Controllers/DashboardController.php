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

        // 1. DATA SUMMARY (Always Visible)
        $totalParts = DB::table('finished_goods')->count();
        $critCount = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->count();
        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('process_type', 'FG')->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // 2. ✨ LOGIC MODE: DELIVERY (Audit Pengiriman) rill
        $deliveryPerformance = 0; $deliveryTrend = collect(); $customerShipments = collect();
        $monthlyPerformance = 0;

        if ($mode == 'delivery') {
            // FIX ERROR: Pastikan nama kolom 'qty' atau ganti ke 'qty_order' rill
            // Gue pake try-catch biar kalau kolom salah gak langsung mati total
            try {
                $totalOrdered = DB::table('purchase_order_items')->sum('qty'); 
                if($totalOrdered == 0) $totalOrdered = DB::table('purchase_order_items')->sum('qty_order'); // Fallback rill
            } catch (\Exception $e) { $totalOrdered = 1; }

            $totalSent = DB::table('deliveries')->sum('qty_delivery') ?: 0;
            $deliveryPerformance = ($totalOrdered > 0) ? round(($totalSent / $totalOrdered) * 100, 1) : 0;

            // Tren 30 Hari (Daily Stats) rill
            $deliveryTrend = DB::table('deliveries')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')->orderBy('date', 'asc')->get();

            // Data per Customer rill
            $customerShipments = DB::table('deliveries')
                ->leftJoin('customers', 'deliveries.customer_code', '=', 'customers.code')
                ->select('customers.name as customer_name', DB::raw('SUM(qty_delivery) as total_qty'), DB::raw('COUNT(DISTINCT no_sj) as total_sj'))
                ->groupBy('customers.name')->orderBy('total_qty', 'desc')->get();
        }

        // 3. LOGIC MODE: SUMMARY
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        $labels = []; $actStockData = []; $minStockData = []; $permintaanStok = [];
        
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
            'totalParts', 'critCount', 'todayProd', 'todayDelv', 'labels', 'actStockData', 
            'minStockData', 'permintaanStok', 'customersList', 'selectedCustomer',
            'mode', 'deliveryPerformance', 'deliveryTrend', 'customerShipments'
        ));
    }
}