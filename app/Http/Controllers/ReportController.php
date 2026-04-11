<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $day = $request->day ?? date('Y-m-d');
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        // 1. REKAPAN HARIAN (Tiles Atas) - Disambung ke Master RM
        $dailySummary = DB::table('rm_stocks')
            ->select(
                DB::raw("(SELECT SUM(pcs_in) FROM rm_incoming_logs WHERE source = 'supplier' AND DATE(created_at) = '$day') as total_supplier"),
                DB::raw("(SELECT SUM(pcs_in) FROM rm_incoming_logs WHERE source = 'return' AND DATE(created_at) = '$day') as total_return"),
                DB::raw("(SELECT SUM(qty_hasil_ok) FROM produksi_batches WHERE DATE(created_at) = '$day') as prod_ok"),
                DB::raw("(SELECT SUM(qty_hasil_ng) FROM produksi_batches WHERE DATE(created_at) = '$day') as prod_ng")
            )->first();

        // 2. TABEL REKAPAN DISAMBUNG KE MASTER RM (Per Spec)
        // Ini kuncinya: Kita narik dari Master RM dulu, baru cari mutasinya
        $rmMovement = DB::table('rm_stocks')
            ->select(
                'material_code',
                'spec',
                'size',
                'stock_pcs as current_stock', // Stok LIVE detik ini
                // Mutasi Masuk Bulan Ini (Supplier)
                DB::raw("(SELECT SUM(pcs_in) FROM rm_incoming_logs 
                          WHERE rm_incoming_logs.material_code = rm_stocks.material_code 
                          AND source = 'supplier' 
                          AND MONTH(created_at) = $month AND YEAR(created_at) = $year) as in_s"),
                // Mutasi Masuk Bulan Ini (Return)
                DB::raw("(SELECT SUM(pcs_in) FROM rm_incoming_logs 
                          WHERE rm_incoming_logs.material_code = rm_stocks.material_code 
                          AND source = 'return' 
                          AND MONTH(created_at) = $month AND YEAR(created_at) = $year) as in_r"),
                // Pemakaian Produksi Bulan Ini
                DB::raw("(SELECT SUM(pcs_used) FROM rm_production_logs 
                          WHERE rm_production_logs.material_code = rm_stocks.material_code 
                          AND MONTH(created_at) = $month AND YEAR(created_at) = $year) as out_total")
            )
            ->get();

        return view('Reports.index', compact('dailySummary', 'rmMovement', 'day', 'month', 'year'));
    }
}