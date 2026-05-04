<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeldingMasterController extends Controller
{
    public function lineIndex()
    {
        $lines = DB::table('line_welding')->get();
        // Sesuai dengan folder welding/master_line.blade.php
        return view('welding.master_line', compact('lines'));
    }

    /**
     * ✨ TAMBAHAN RILL: Fungsi untuk menyimpan Station/Line baru
     * Ini yang tadi bikin error 500 karena fungsinya belum ada.
     */
    public function lineStore(Request $request)
    {
        // 1. Validasi agar input tidak kosong
        $request->validate([
            'kode_line' => 'required|max:255',
            'nama_line' => 'required|max:255',
        ]);

        try {
            // 2. Simpan ke database (Tanpa timestamps karena database Bapak tidak pakai itu)
            DB::table('line_welding')->insert([
                'kode_line' => strtoupper(trim($request->kode_line)),
                'nama_line' => strtoupper(trim($request->nama_line)),
            ]);

            return redirect()->back()->with('success', 'Welding Station Baru Berhasil Didaftarkan!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Simpan Station: ' . $e->getMessage());
        }
    }

    /**
     * ✨ TAMBAHAN RILL: Fungsi untuk hapus Line (Agar master data bisa dikelola)
     */
    public function lineDestroy($id)
    {
        try {
            DB::table('line_welding')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Station Berhasil Dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Hapus Station: ' . $e->getMessage());
        }
    }

    public function ngIndex()
    {
        // ✨ PERBAIKAN: Ambil SEMUA kategori (Welding, Stamping, General) 
        // agar saat Bapak input NG Stamping, datanya langsung muncul di tabel.
        $listNG = DB::table('master_ngs')
            ->orderBy('category', 'asc')
            ->orderBy('ng_name', 'asc')
            ->get();

        return view('welding.welding_ng', compact('listNG'));
    }

    public function ngStore(Request $request)
    {
        // Validasi input
        $request->validate([
            'ng_name' => 'required|max:255',
            'category' => 'required' 
        ]);

        try {
            // ✨ PERBAIKAN: Hapus created_at & updated_at karena kolom tersebut 
            // tidak ada di database Bapak (Penyebab error SQLSTATE[42S22])
            DB::table('master_ngs')->insert([
                'ng_name'  => strtoupper(trim($request->ng_name)),
                'category' => $request->category, // Mengikuti input dari Modal (Welding/Stamping/General)
            ]);

            return redirect()->back()->with('success', 'Master NG Berhasil Ditambahkan!');

        } catch (\Exception $e) {
            // Menampilkan error jika ada masalah lain
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $e->getMessage());
        }
    }

    public function ngDestroy($id)
    {
        try {
            DB::table('master_ngs')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Master NG Berhasil Dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Hapus: ' . $e->getMessage());
        }
    }
}