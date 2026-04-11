<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{FinishedGood, Customer, Part}; // Pastikan model Part (Master Data) sudah ada

class FgController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->customer; //

        // 1. Ambil data stok berdasarkan kolom 'customer' (sesuai database lo)
        if ($customer) {
            $allFG = FinishedGood::where('customer', $customer)->get();
        } else {
            $allFG = collect(); //
        }

        // 2. Data buat Chart.js
        $labels = $allFG->pluck('part_no')->toArray();
        $actStockData = $allFG->pluck('actual_stock')->toArray();
        $minStockData = $allFG->pluck('min_stock_pcs')->toArray();

        return view('finished_goods.index', compact('allFG', 'labels', 'actStockData', 'minStockData'));
    }

    public function create()
    {
        // Narik data buat dropdown di halaman tambah
        $customers = Customer::all();
        $masterParts = Part::all(); // Ini daftar Master Part buat auto-fill
        
        return view('finished_goods.create', compact('customers', 'masterParts'));
    }

    public function store(Request $request)
    {
        // Simpan ke tabel finished_goods
        FinishedGood::create($request->all());

        return redirect()->route('fg.index', ['customer' => $request->customer])
                         ->with('success', 'Master Part Berhasil Ditambah!');
    }

    public function edit($id)
    {
        $fg = FinishedGood::findOrFail($id);
        $customers = Customer::all();
        return view('finished_goods.edit', compact('fg', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $fg = FinishedGood::findOrFail($id);
        $fg->update($request->all());
        return redirect()->route('fg.index', ['customer' => $fg->customer])
                         ->with('success', 'Data Diupdate!');
    }
}