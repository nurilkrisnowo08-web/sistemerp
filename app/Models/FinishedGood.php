<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGood extends Model
{
    use HasFactory;

    // Nama tabel sudah benar
    protected $table = 'finished_goods';

    // Sesuaikan nama kolom dengan migrasi terbaru
    protected $fillable = [
    'part_no', 'part_name', 'customer', 
    'qty_per_unit', 'qty_per_pallet', 'qty_order', 
    'needs_per_day', 'min_stock_pcs', 'max_stock_pcs', 
    'actual_stock', // Pastikan namanya ini ya, Ril!
    'remark'
];

}