<?php

namespace App\Http\Controllers;

use App\Models\Line; 
use Illuminate\Http\Request;

class LineController extends Controller
{
    /**
     * Menampilkan daftar Line Produksi
     */
    public function index() 
    {
        $lines = Line::all();
        
        // Membaca otomatis variasi huruf besar/kecil pada folder dan file view di Linux
        return view()->first(['Line.index', 'line.index', 'Line.Index', 'line.Index'], compact('lines'));
    }

    /**
     * Simpan Line Baru
     */
    public function store(Request $request) 
    {
        $request->validate([
            'kode_Line' => 'required|unique:line,kode_Line', // Nama tabel database lu 'line'
            'nama_Line' => 'required'
        ]);
        
        Line::create($request->all());
        return redirect()->back()->with('success', 'Line Produksi Berhasil Ditambahkan !');
    }

    /**
     * Update Data Line
     */
    public function update(Request $request, $id) 
    {
        $line = Line::findOrFail($id);
        
        $request->validate([
            // Bypass unique check buat ID ini sendiri !
            'kode_Line' => 'required|unique:line,kode_Line,' . $id,
            'nama_Line' => 'required'
        ]);

        $line->update($request->all()); 
        return redirect()->back()->with('success', 'Data Line Berhasil Diperbarui!');
    }

    /**
     * Hapus Line
     */
    public function destroy($id) 
    {
        Line::destroy($id);
        return redirect()->back()->with('success', 'Line Produksi Berhasil Dihapus!');
    }
}