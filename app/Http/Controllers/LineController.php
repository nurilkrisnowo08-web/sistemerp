<?php

namespace App\Http\Controllers;

use App\Models\Line; 
use Illuminate\Http\Request;

class LineController extends Controller
{
    public function index() 
    {
        $lines = Line::all();
        
        // ✨ TIPS RIL: Kalau di Hostinger foldernya 'Line', ganti jadi 'Line.index'
        // Tapi gue saranin folder & file pake huruf kecil semua biar gak pusing.
        return view('line.index', compact('lines'));
    }

    public function store(Request $request) 
    {
        $request->validate([
            'kode_Line' => 'required|unique:line,kode_Line', // Nama tabel di database lu 'line'
            'nama_Line' => 'required'
        ]);
        
        Line::create($request->all());
        return redirect()->back()->with('success', 'Line Produksi Berhasil Ditambahkan rill!');
    }

    public function update(Request $request, $id) 
    {
        $line = Line::findOrFail($id);
        
        $request->validate([
            'kode_Line' => 'required|unique:line,kode_Line,' . $id,
            'nama_Line' => 'required'
        ]);

        $line->update($request->all()); 
        return redirect()->back()->with('success', 'Data Line Berhasil Diperbarui!');
    }

    public function destroy($id) 
    {
        Line::destroy($id);
        return redirect()->back()->with('success', 'Line Produksi Dihapus!');
    }
}