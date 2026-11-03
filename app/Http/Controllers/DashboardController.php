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
        
        // ✨ Ambil data part FG yang stoknya kritis (Shortage)
        $shortageParts = DB::table('finished_goods')
            ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
            ->select('finished_goods.*', 'parts.part_name', 'parts.customer_code')
            ->whereRaw('finished_goods.actual_stock <= finished_goods.min_stock_pcs')
            ->get();
        $critCount = $shortageParts->count(); 
        
        // ✨ Cek RM (Bahan Baku) Kritis (Stok 0 atau mendekati habis)
        $shortageRm = DB::table('rm_stocks')
            ->where('stock_pcs', '<=', 100) // Ambang batas kritis RM (Bisa disesuaikan)
            ->select('coil_id', 'material_code', 'stock_pcs')
            ->get();

        // ✨ Cek WIP (Barang Setengah Jadi / Welding)
        $wipStock = DB::table('finished_goods')
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
        
        if ($mode == 'delivery') {
            $totalOrdered = DB::table('purchase_order_items')->sum('qty') ?: 1;
            $totalSent = DB::table('deliveries')->sum('qty_delivery') ?: 0;
            $deliveryPerformance = round(($totalSent / $totalOrdered) * 100, 1);

            $deliveryTrend = DB::table('deliveries')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_delivery) as total'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')->orderBy('date', 'asc')->get();

            // ✨ Ambil Detail Sisa PO Customer
            $poSisa = DB::table('purchase_order_items')
                ->leftJoin('purchase_orders', 'purchase_order_items.po_id', '=', 'purchase_orders.id')
                ->select(
                    'purchase_orders.po_number',
                    'purchase_orders.customer_code',
                    'purchase_order_items.part_no',
                    'purchase_order_items.qty as target_qty',
                    'purchase_order_items.sent_qty',
                    DB::raw('(purchase_order_items.qty - purchase_order_items.sent_qty) as sisa_qty')
                )
                ->whereRaw('purchase_order_items.qty > purchase_order_items.sent_qty')
                ->get();
        }

        // ==========================================
        // 3. LOGIC MODE: SUMMARY (INVENTORY MONITOR)
        // ==========================================
        $labels = []; 
        $actStockData = []; 
        $minStockData = []; 
        
        $customersList = DB::table('parts')->distinct()->pluck('customer_code');
        
        if ($mode == 'summary') {
            $queryChart = DB::table('finished_goods')
                ->leftJoin('parts', 'finished_goods.part_no', '=', 'parts.part_no')
                ->select('finished_goods.part_no', 'finished_goods.actual_stock', 'finished_goods.min_stock_pcs');
            
            if ($selectedCustomer) {
                $queryChart->where('parts.customer_code', $selectedCustomer);
            } else {
                $queryChart->limit(8); 
            }
            $chartData = $queryChart->get();
            $labels = $chartData->map(fn($item) => $item->part_no)->toArray();
            $actStockData = $chartData->pluck('actual_stock')->toArray();
            $minStockData = $chartData->pluck('min_stock_pcs')->toArray();
        }

        // ==========================================
        // 4. KEMBALIKAN KE VIEW
        // ==========================================
        return view('dashboard', compact(
            'totalParts', 
            'critCount', 
            'todayProd', 
            'todayDelv', 
            'mode', 
            'deliveryPerformance', 
            'deliveryTrend', 
            'labels', 
            'actStockData', 
            'minStockData', 
            'customersList', 
            'selectedCustomer',
            'shortageParts',
            'shortageRm', // Data RM Kritis
            'wipStock',   // Data WIP
            'poSisa'      // Data Sisa PO Customer
        ));
    }
}