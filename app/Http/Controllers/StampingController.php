<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StampingController extends Controller
{
    public function index()
    {
        // 1. Ambil list customer (Agar eror 'availableCustomers' hilang)
        $availableCustomers = DB::table('finished_goods')->where('is_welding', 1)->distinct()->pluck('customer');

        // 2. Ambil RIWAYAT STAMPING HARI INI
        $history = DB::table('production_logs')
            ->join('finished_goods', 'production_logs.part_no', '=', 'finished_goods.part_no')
            ->select('production_logs.*', 'finished_goods.part_name', 'finished_goods.customer')
            ->where('production_logs.qty', '>', 0) // Hanya ambil yang masuk (Stamping)
            ->whereDate('production_logs.created_at', date('Y-m-d'))
            ->orderBy('production_logs.created_at', 'desc')
            ->get();

        return view('stamping.index', compact('availableCustomers', 'history'));
    }

    public function store(Request $request)
    {
        $request->validate(['part_no' => 'required', 'qty' => 'required|numeric|min:1']);

        // 1. Catat ke Log Produksi
        DB::table('production_logs')->insert([
            'part_no' => $request->part_no,
            'qty' => $request->qty,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Tambahkan ke Welding Stock (WIP)
        DB::table('finished_goods')->where('part_no', $request->part_no)->increment('welding_stock', $request->qty);

        return redirect()->back()->with('success', 'Data Stamping Berhasil Disimpan!');
    }

    // Fungsi AJAX untuk memunculkan Part Number
    public function getParts($customer)
    {
        $parts = DB::table('finished_goods')->where('customer', $customer)->where('is_welding', 1)->get();
        return response()->json($parts);
    }
}