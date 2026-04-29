<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeldingMasterController extends Controller
{
    /**
     * Tampilkan Daftar Line Welding
     */
    public function lineIndex()
    {
        $lines = DB::table('line_welding')->get();
        // Sesuaikan path view dengan folder Bapak (misal: Master.welding_line)
        return view('Master.welding_line', compact('lines'));
    }

    /**
     * Tampilkan Daftar Master NG khusus WELDING
     */
    public function ngIndex()
    {
        // ✨ PERBAIKAN: Tambahkan filter category agar NG Stamping tidak muncul di sini
        $listNG = DB::table('master_ngs')
            ->where('category', 'WELDING')
            ->orderBy('ng_name', 'asc')
            ->get();

        return view('Master.welding_ng', compact('listNG'));
    }

    /**
     * Simpan Master NG Baru
     */
    public function ngStore(Request $request)
    {
        $request->validate([
            'ng_name' => 'required|max:255',
        ]);

        try {
            // Cek apakah nama NG sudah ada untuk kategori WELDING
            $exists = DB::table('master_ngs')
                ->where('ng_name', strtoupper(trim($request->ng_name)))
                ->where('category', 'WELDING')
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'Nama NG ini sudah terdaftar di database!');
            }

            DB::table('master_ngs')->insert([
                'ng_name'    => strtoupper(trim($request->ng_name)),
                'category'   => 'WELDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Master NG Berhasil Ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $e->getMessage());
        }
    }

    /**
     * ✨ TAMBAHAN: Fungsi Hapus Master NG
     */
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