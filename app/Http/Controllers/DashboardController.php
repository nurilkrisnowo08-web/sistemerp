<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $selectedCustomer = $request->query('customer');

        // 1. ✨ SUMMARY CARDS (Logic Anti-Bocor rill!)
        $totalParts = DB::table('finished_goods')->count();
        $critCount = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->count();
        
        // Cuma hitung produksi yang masuk ke FG (Biar angka gak dobel sama Welding)
        $todayProd = DB::table('production_logs')
            ->whereDate('created_at', $today)
            ->where('process_type', 'FG') 
            ->sum('qty') ?? 0;

        $todayDelv = DB::table('deliveries')
            ->whereDate('created_at', $today)
            ->sum('qty_delivery') ?? 0;

        // 2. UNIQUE COUNTS
        $totalPO = DB::table('purchase_orders')->distinct('po_number')->count('po_number');
        $pendingDelvCount = DB::table('deliveries')->where('status', 'DRAFT')->distinct('no_sj')->count('no_sj');

        // 3. ✨ DATA UNTUK ANALYSIS PANEL (Fix Error $deliveryToday rill!)
        $deliveryToday = DB::table('deliveries')
            ->leftJoin('customers', 'deliveries.customer_code', '=', 'customers.code')
            ->whereDate('deliveries.created_at', $today)
            ->select('deliveries.*', 'customers.name as customer_name')
            ->get();

        // TREN DELIVERY (7 Hari Terakhir) buat Line Chart
        $delvDates = [];
        $delvQtys = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $delvDates[] = date('d/m', strtotime($date));
            $delvQtys[] = DB::table('deliveries')->whereDate('created_at', $date)->sum('qty_delivery') ?? 0;
        }

        // 4. ✨ CHART LOGIC (BAR CHART & FILTER)
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');

        $queryChart = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs', 'parts.customer_code');

        if ($selectedCustomer) {
            $queryChart->where('parts.customer_code', $selectedCustomer);
        } else {
            $queryChart->limit(15); // Biar chart gak kepenuhan rill
        }

        $chartData = $queryChart->get();
        $labels = $chartData->map(fn($item) => $item->part_no)->toArray();
        $actStockData = $chartData->pluck('actual_stock')->toArray();
        $minStockData = $chartData->pluck('min_stock_pcs')->toArray();

        // 5. DATA SHORTAGE (TABEL MERAH)
        $permintaanStok = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.*', 'parts.customer_code')
            ->whereRaw('finished_goods.actual_stock <= finished_goods.min_stock_pcs')
            ->orderBy('finished_goods.actual_stock', 'asc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalParts', 'critCount', 'todayProd', 'todayDelv', 
            'totalPO', 'pendingDelvCount', 'labels', 'actStockData', 
            'minStockData', 'permintaanStok', 'customersList', 'selectedCustomer',
            'deliveryToday', 'delvDates', 'delvQtys'
        ));
    }
}