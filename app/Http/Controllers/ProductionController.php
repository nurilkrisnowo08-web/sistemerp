<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinishedGood;
use App\Models\ProductionLog;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
   public function index(Request $request)
{
    // 1. Ambil daftar customer unik untuk dropdown form & filter
    $customers = FinishedGood::select('customer')->distinct()->orderBy('customer', 'asc')->get();

    // 2. Ambil filter dari request
    $filterCustomer = $request->customer_filter;

    // 3. Query Riwayat Produksi Hari Ini
    $query = ProductionLog::join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
        ->whereDate('production_logs.created_at', now())
        ->select('production_logs.*', 'finished_goods.part_name', 'finished_goods.customer');

    // Jika ada selection customer, filter datanya
    if ($filterCustomer) {
        $query->where('finished_goods.customer', $filterCustomer);
    }

    $dailyLogs = $query->orderBy('production_logs.created_at', 'desc')->get();

    return view('production.index', compact('customers', 'dailyLogs', 'filterCustomer'));
}

   // File: ProductionController.php

public function getPartsByCustomer($customer)
{
    // Ambil part yang is_welding-nya 0 (Artinya ini part FG biasa / Stamping langsung)
    // Barang yang is_welding = 1 (Inventory Welding) otomatis tidak akan muncul di sini
    $parts = DB::table('finished_goods')
                ->where('customer', $customer)
                ->where('is_welding', 0) 
                ->get();

    return response()->json($parts);
}

    public function store(Request $request)
    {
        $request->validate([
            'part_no' => 'required',
            'qty'     => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();
            
            // Increment Stok
            FinishedGood::where('part_no', $request->part_no)->increment('actual_stock', $request->qty);
            
            // Catat Log
            ProductionLog::create(['part_no' => $request->part_no, 'qty' => $request->qty]);
            
            DB::commit();
            return back()->with('success', 'Hasil Produksi Berhasil Disimpan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * FITUR TAMBAHAN: Hapus Riwayat & Potong Stok Kembali
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $log = ProductionLog::findOrFail($id);
            
            // Kurangi stok yang tadi sudah terlanjur ditambah (Decrement)
            FinishedGood::where('part_no', $log->part_no)->decrement('actual_stock', $log->qty);
            
            // Hapus baris riwayatnya
            $log->delete();

            DB::commit();
            return back()->with('success', 'Riwayat dihapus & Stok berhasil dikoreksi!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}