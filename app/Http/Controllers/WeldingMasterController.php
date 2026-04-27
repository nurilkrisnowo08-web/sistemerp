<?php

namespace App\Http\Controllers; // <--- Pastikan baris ini ada dan benar

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeldingMasterController extends Controller // <--- Nama class harus sama dengan nama file
{
    public function lineIndex()
    {
        $lines = DB::table('line_welding')->get();
        return view('welding.master_line', compact('lines'));
    }

    public function ngIndex()
    {
        $listNG = DB::table('master_ngs')->get();
        return view('welding.master_ng', compact('listNG'));
    }
}