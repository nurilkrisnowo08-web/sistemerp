<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeldingMasterController extends Controller
{
    public function lineIndex()
    {
        $lines = DB::table('line_welding')->get();
        // ✨ DISESUAIKAN: Folder 'welding', file 'master_line'
        return view('welding.master_line', compact('lines'));
    }

    public function ngIndex()
    {
        // Ambil NG khusus WELDING
        $listNG = DB::table('master_ngs')
            ->where('category', 'WELDING')
            ->orderBy('ng_name', 'asc')
            ->get();

        // ✨ DISESUAIKAN: Folder 'welding', file 'welding_ng' 
        // (Pastikan file sudah di-rename jadi welding_ng.blade.php)
        return view('welding.welding_ng', compact('listNG'));
    }

    public function ngStore(Request $request)
    {
        $request->validate(['ng_name' => 'required|max:255']);

        try {
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