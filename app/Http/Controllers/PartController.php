<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Customer;
use Illuminate\Http\Request;

class PartController extends Controller {

  public function index()
{
    // Mengurutkan berdasarkan customer_code agar ICHII kumpul dengan ICHII
    $parts = Part::orderBy('customer_code', 'asc')->get(); 
    return view('parts.index', compact('parts'));
}

    public function create() {
        $customers = Customer::all();
        return view('parts.create', compact('customers'));
    }

    public function store(Request $request) {
        Part::create($request->all());
        return redirect()->route('parts.index')->with('success', 'Part Berhasil Disimpan!');
    }

    // 1. Membuka Form Edit Part
    public function edit($id) {
        // Cari data part berdasarkan ID
        $part = Part::findOrFail($id); 
        
        // Ambil data customer untuk dropdown pilihan di form edit
        $customers = Customer::all(); 
        
        return view('parts.edit', compact('part', 'customers'));
    }

    // 2. Proses Update Data Part
    public function update(Request $request, $id) {
        $part = Part::findOrFail($id);
        
        // Update semua field (Part No, Name, Customer, dll)
        $part->update($request->all()); 
        
        return redirect()->route('parts.index')->with('success', 'Data Part Berhasil Diperbarui!');
    }

    // 3. Proses Hapus Part
    public function destroy($id) {
        $part = Part::findOrFail($id);
        
        // Hapus data secara permanen dari database
        $part->delete(); 
        
        return redirect()->route('parts.index')->with('success', 'Part Berhasil Dihapus!');
    }
}