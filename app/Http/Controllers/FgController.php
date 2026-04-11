<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FgController extends Controller
{
    public function index(Request $request)
    {
        $availableCustomers = DB::table('customers')->get();
        $customer = $request->customer;
        $date = $request->date ?? date('Y-m-d');

        // SAKTI: Ambil data master untuk modal Tambah Part
        $parts = DB::table('parts')->get(); 
        $masterMaterials = DB::table('master_materials')->get(); 

        if ($customer) {
            $allFG = DB::table('finished_goods')->where('customer', $customer)->get();
            
            foreach ($allFG as $fg) {
                // SAKTI: IN ke FG adalah barang hasil produksi (QTY POSITIF / > 0)
                // Gue ganti dari '< 0' jadi '> 0' biar angkanya kebaca, Guru!
                $fg->in_stp = DB::table('production_logs')
                    ->where('part_no', $fg->part_no)
                    ->whereDate('created_at', $date)
                    ->where('qty', '>', 0) 
                    ->sum('qty');

                $fg->out_delv = DB::table('deliveries')
                    ->where('part_no', $fg->part_no)
                    ->whereDate('created_at', $date)
                    ->sum('qty_delivery');

                // Logika Backtracking (Menghitung stok mundur ke tanggal yang dipilih)
                $total_in_setelah = DB::table('production_logs')
                    ->where('part_no', $fg->part_no)
                    ->whereDate('created_at', '>', $date)
                    ->where('qty', '>', 0)
                    ->sum('qty');

                $total_out_setelah = DB::table('deliveries')
                    ->where('part_no', $fg->part_no)
                    ->whereDate('created_at', '>', $date)
                    ->sum('qty_delivery');

                // Stok Akhir pada tanggal tersebut = Stok Sekarang - (Masuk Setelahnya) + (Keluar Setelahnya)
                $fg->stock_akhir = ($fg->actual_stock ?? 0) - $total_in_setelah + $total_out_setelah;
                $fg->stock_awal = $fg->stock_akhir - $fg->in_stp + $fg->out_delv;
                $fg->stock_day = ($fg->needs_per_day > 0) ? round($fg->stock_akhir / $fg->needs_per_day, 1) : 0;
            }

            $stockOut = DB::table('deliveries')->where('customer_code', $customer)->whereDate('created_at', $date)->orderBy('created_at', 'desc')->get();
            
            // LOG MASUK: Pastikan filter QTY > 0 agar muncul di tabel bawah
            $stockIn = DB::table('production_logs')
                ->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
                ->where('finished_goods.customer', $customer)
                ->where('production_logs.qty', '>', 0)
                ->whereDate('production_logs.created_at', $date)
                ->select('production_logs.*')
                ->orderBy('production_logs.created_at', 'desc')
                ->get();
        } else {
            $allFG = collect(); $stockOut = collect(); $stockIn = collect();
        }
        
        $labels = $allFG->pluck('part_no')->toArray();
        $actStockData = $allFG->pluck('stock_akhir')->toArray();
        $minStockData = $allFG->pluck('min_stock_pcs')->toArray();

        return view('finished_goods.index', compact(
            'allFG', 'labels', 'actStockData', 'minStockData', 'stockOut', 
            'stockIn', 'date', 'availableCustomers', 'parts', 'masterMaterials'
        ));
    }

    // --- FUNGSI LAIN DIBAWAH INI TETAP UTUH (TIDAK ADA PERUBAHAN) ---

    public function create()
    {
        $customers = DB::table('customers')->get();
        return view('finished_goods.create', compact('customers'));
    }

    public function store(Request $request)
    {
        DB::table('finished_goods')->insert([
            'customer'      => $request->customer,
            'part_no'       => $request->part_no,
            'part_name'     => $request->part_name,
            'needs_per_day' => $request->needs_per_day,
            'actual_stock'  => $request->actual_stock,
            'min_stock_pcs' => $request->min_stock_pcs,
            'max_stock_pcs' => $request->max_stock_pcs,
            'qty_per_pallet'=> $request->qty_per_pallet ?? 0,
            'created_at'    => now(), 
            'updated_at'    => now(),
        ]);
        
        return redirect()->route('fg.index', ['customer' => $request->customer])->with('success', 'Part Berhasil Ditambah!');
    }

    public function edit($id)
    {
        $fg = DB::table('finished_goods')->where('id', $id)->first();
        $customers = DB::table('customers')->get();
        return view('finished_goods.edit', compact('fg', 'customers'));
    }

    public function monthlyRecap(Request $request)
    {
        $customer = $request->customer;
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $daily_date = $request->daily_date ?? date('Y-m-d');
        $customers = DB::table('customers')->get();

        if ($customer) {
            $recap = DB::table('finished_goods')->where('customer', $customer)->get();
            $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

            foreach ($recap as $fg) {
                // SAKTI: Ganti ke QTY > 0 agar angka mutasi bulanan terbaca
                $fg->total_in = DB::table('production_logs')->where('part_no', $fg->part_no)
                    ->where('qty', '>', 0)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)
                    ->sum('qty');

                $fg->total_out = DB::table('deliveries')->where('part_no', $fg->part_no)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)
                    ->sum('qty_delivery');

                $future_in = DB::table('production_logs')->where('part_no', $fg->part_no)
                    ->where('qty', '>', 0)
                    ->whereDate('created_at', '>', $endOfMonth)
                    ->sum('qty');

                $future_out = DB::table('deliveries')->where('part_no', $fg->part_no)->whereDate('created_at', '>', $endOfMonth)->sum('qty_delivery');

                $fg->stock_akhir = ($fg->actual_stock ?? 0) - $future_in + $future_out;
                $fg->stock_awal = $fg->stock_akhir - $fg->total_in + $fg->total_out;
            }

            $in_logs = DB::table('production_logs')->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
                ->where('finished_goods.customer', $customer)
                ->where('production_logs.qty', '>', 0)
                ->whereDate('production_logs.created_at', $daily_date)
                ->select(DB::raw('DATE(production_logs.created_at) as tgl'), 'production_logs.part_no', 'production_logs.qty as in_qty', DB::raw('0 as out_qty'), 'production_logs.created_at as jam');
            
            $dailyDetails = DB::table('deliveries')->where('customer_code', $customer)->whereDate('created_at', $daily_date)->select(DB::raw('DATE(created_at) as tgl'), 'part_no', DB::raw('0 as in_qty'), 'qty_delivery as out_qty', 'created_at as jam')->unionAll($in_logs)->orderBy('jam', 'desc')->get();
        } else { $recap = collect(); $dailyDetails = collect(); }

        return view('finished_goods.monthly_recap', compact('recap', 'dailyDetails', 'customers', 'month', 'year', 'daily_date'));
    }

    public function update(Request $request, $id)
    {
        DB::table('finished_goods')->where('id', $id)->update([
            'part_no' => $request->part_no, 
            'part_name' => $request->part_name, 
            'needs_per_day' => $request->needs_per_day,
            'actual_stock' => $request->actual_stock, 
            'min_stock_pcs' => $request->min_stock_pcs, 
            'max_stock_pcs' => $request->max_stock_pcs, 
            'updated_at' => now(),
        ]);
        return redirect()->route('fg.index', ['customer' => $request->customer])->with('success', 'Data Berhasil Diupdate!');
    }

    public function destroyLog($id)
    {
        DB::beginTransaction();
        try {
            $log = DB::table('production_logs')->where('id', $id)->first();
            if ($log) {
                DB::table('finished_goods')->where('part_no', $log->part_no)->decrement('actual_stock', $log->qty);
                DB::table('production_logs')->where('id', $id)->delete();
            }
            DB::commit(); return redirect()->back()->with('success', 'Log berhasil dihapus!');
        } catch (\Exception $e) { DB::rollBack(); return redirect()->back()->with('error', 'Gagal!'); }
    }

    public function printRecap(Request $request)
    {
        $customer = $request->customer;
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $daily_date = $request->daily_date;
        $month_name = date('F', mktime(0, 0, 0, $month, 10));

        if ($customer) {
            $recap = DB::table('finished_goods')->where('customer', $customer)->get();
            $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

            foreach ($recap as $fg) {
                $fg->total_in = DB::table('production_logs')->where('part_no', $fg->part_no)
                    ->where('qty', '>', 0)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)
                    ->sum('qty');

                $fg->total_out = DB::table('deliveries')->where('part_no', $fg->part_no)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)
                    ->sum('qty_delivery');
                
                $future_in = DB::table('production_logs')->where('part_no', $fg->part_no)
                    ->where('qty', '>', 0)
                    ->whereDate('created_at', '>', $endOfMonth)->sum('qty');
                $future_out = DB::table('deliveries')->where('part_no', $fg->part_no)->whereDate('created_at', '>', $endOfMonth)->sum('qty_delivery');

                $fg->stock_akhir = ($fg->actual_stock ?? 0) - $future_in + $future_out;
                $fg->stock_awal = $fg->stock_akhir - $fg->total_in + $fg->total_out;
            }

            $dailyDetails = collect();
            if ($daily_date) {
                $in_logs = DB::table('production_logs')->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
                    ->where('finished_goods.customer', $customer)
                    ->where('production_logs.qty', '>', 0)
                    ->whereDate('production_logs.created_at', $daily_date)
                    ->select(DB::raw('DATE(production_logs.created_at) as tgl'), 'production_logs.part_no', 'production_logs.qty as in_qty', DB::raw('0 as out_qty'), 'production_logs.created_at as jam');
                $dailyDetails = DB::table('deliveries')->where('customer_code', $customer)->whereDate('created_at', $daily_date)->select(DB::raw('DATE(created_at) as tgl'), 'part_no', DB::raw('0 as in_qty'), 'qty_delivery as out_qty', 'created_at as jam')->unionAll($in_logs)->orderBy('jam', 'desc')->get();
            }

            return view('finished_goods.print', compact('recap', 'dailyDetails', 'customer', 'month_name', 'year', 'daily_date'));
        }
        return redirect()->back();
    }

    public function getParts($customer)
    {
        $parts = DB::table('parts')->where('customer_code', $customer)->select('part_no', 'part_name')->get();
        return response()->json($parts);
    }

    public function destroy($id)
    {
        DB::table('finished_goods')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Data Part Berhasil Dihapus, Guru!');
    }

    public function history(Request $request)
    {
        $customers = DB::table('customers')->get();
        $customer = $request->customer;
        $query = \App\Models\PurchaseOrder::with('deliveries')->where('status', 'closed');
        if ($customer) { $query->where('customer_code', $customer); }
        $purchaseOrders = $query->orderBy('updated_at', 'desc')->get();
        return view('finished_goods.history', compact('purchaseOrders', 'customers'));
    }
}