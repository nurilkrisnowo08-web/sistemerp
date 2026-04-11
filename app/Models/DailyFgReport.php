<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyFgReport extends Model 
{
    // Pastikan nama tabelnya sudah benar sesuai database
    protected $table = 'daily_fg_reports'; 

    /**
     * Gabungkan semua kolom ke dalam satu $fillable saja.
     * Ini agar semua data dari catatan tangan Ril bisa masuk ke database.
     */
    protected $fillable = [
        'report_date', 
        'part_no', 
        'part_name', 
        'customer_code', // Penting: Agar laporan bisa dipisah per customer
        'stock_awal', 
        'in', 
        'out', 
        'stock_akhir', 
        'keterangan'
    ];
}