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

        // 1. Summary Cards (Standard Assets)
        $totalParts = DB::table('finished_goods')->count();
        $critCount = DB::table('finished_goods')->whereRaw('actual_stock <= min_stock_pcs')->count();
        $todayProd = DB::table('production_logs')->whereDate('created_at', $today)->where('qty', '>', 0)->sum('qty') ?? 0;
        $todayDelv = DB::table('deliveries')->whereDate('created_at', $today)->sum('qty_delivery') ?? 0;

        // 2. ✨ FIX REAL-TIME UNIQUE COUNTS (Anti-Crash)
        // Menghitung jumlah Nomor PO unik saja. Dijamin muncul 1 (bukan 14).
        $totalPO = DB::table('purchase_orders')->distinct('po_number')->count('po_number');

        // Menghitung jumlah Surat Jalan unik yang statusnya masih 'DRAFT'
        $pendingDelvCount = DB::table('deliveries')->where('status', 'DRAFT')->distinct('no_sj')->count('no_sj');

        // 3. ✨ CHART LOGIC (FILTER PER CUSTOMER)
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');

        $queryChart = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs', 'parts.customer_code');

        if ($selectedCustomer) {
            $queryChart->where('parts.customer_code', $selectedCustomer);
        } else {
            $queryChart->limit(10); // Default preview biar gak numpuk
        }

        $chartData = $queryChart->get();
        $labels = $chartData->map(fn($item) => ($item->customer_code ?? 'AMK') . ' | ' . $item->part_no)->toArray();
        $actStockData = $chartData->pluck('actual_stock')->toArray();
        $minStockData = $chartData->pluck('min_stock_pcs')->toArray();

        // 4. Data Shortage
        $permintaanStok = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.*', 'parts.customer_code')
            ->whereRaw('finished_goods.actual_stock <= finished_goods.min_stock_pcs')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalParts', 'critCount', 'todayProd', 'todayDelv', 
            'totalPO', 'pendingDelvCount', 'labels', 'actStockData', 
            'minStockData', 'permintaanStok', 'customersList', 'selectedCustomer'
        ));
    }
}