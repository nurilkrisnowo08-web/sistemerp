<?php

namespace App\Http\Controllers;

use App\Models\DailyFgReport;
use App\Models\Part;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyFgController extends Controller {

   public function index(Request $request) {
    // Tangkap tanggal, default ke hari ini
    $date = $request->date ?? \Carbon\Carbon::now()->format('Y-m-d');
    
    // Kelompokkan per Customer
    $reports = DailyFgReport::where('report_date', $date)
                ->orderBy('customer_code', 'asc')
                ->get()
                ->groupBy('customer_code'); 
    
    $parts = \App\Models\Part::all(); 
    return view('report.fg_daily', compact('reports', 'date', 'parts'));
}

    public function store(Request $request) {
        // Validasi input agar tidak error layar merah
        $request->validate([
            'report_date'   => 'required|date',
            'part_no'       => 'required',
            'customer_code' => 'required', // Tambahkan customer agar bisa dipisahkan
            'in'            => 'required|numeric',
            'out'           => 'required|numeric',
        ]);

        // Rumus Stock Akhir sesuai coretan tangan Ril:
        // Stock Akhir = Stock Awal + IN - OUT
        $stock_akhir = $request->stock_awal + $request->in - $request->out;

        // Simpan data lengkap dengan kolom customer_code
        DailyFgReport::create([
            'report_date'   => $request->report_date,
            'part_no'       => $request->part_no,
            'part_name'     => $request->part_name,
            'customer_code' => $request->customer_code, // Data customer untuk grouping
            'stock_awal'    => $request->stock_awal,
            'in'            => $request->in,
            'out'           => $request->out,
            'stock_akhir'   => $stock_akhir,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data Harian Berhasil Masuk!');
    }

  public function update(Request $request, $id)
{
    $report = \App\Models\DailyFgReport::findOrFail($id);
    $fg = \App\Models\FinishedGood::where('part_no', $report->part_no)->first();

    // 1. Balikin stok Master FG ke kondisi sebelum laporan ini ada
    if ($fg) {
        $fg->actual_stock = ($fg->actual_stock - $report->in) + $report->out;
        $fg->save();
    }

    // 2. Update laporan dengan data baru (Ditambah pengaman ?? $report->report_date)
    $report->update([
        'report_date' => $request->report_date ?? $report->report_date, 
        'stock_awal'  => $request->stock_awal,
        'in'          => $request->in,
        'out'         => $request->out,
        'stock_akhir' => $request->stock_awal + $request->in - $request->out,
        'keterangan'  => $request->keterangan,
    ]);

    // 3. Terapkan pergerakan baru ke Master FG
    if ($fg) {
        $fg->actual_stock = ($fg->actual_stock + $request->in) - $request->out;
        $fg->save();
    }

    return redirect()->back()->with('success', 'Data Berhasil Diperbarui!');
}

public function destroy($id)
{
    $report = \App\Models\DailyFgReport::findOrFail($id);

    // LOGIKA SYNC: Jika laporan dihapus, stok di Master FG harus dikembalikan
    $fg = \App\Models\FinishedGood::where('part_no', $report->part_no)->first();
    if ($fg) {
        $fg->actual_stock = ($fg->actual_stock - $report->in) + $report->out;
        $fg->save();
    }

    $report->delete(); // Hapus laporannya
    return redirect()->back()->with('success', 'Laporan Berhasil Dihapus!');
}
}