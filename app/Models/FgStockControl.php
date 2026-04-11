<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FgStockControl extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database (opsional jika nama tabel plural dari model)
     */
    protected $table = 'fg_stock_controls';

    /**
     * Daftar kolom yang diizinkan untuk diisi secara massal.
     * Pastikan semua parameter kontrol stok kamu terdaftar di sini.
     */
    protected $fillable = [
        'part_no', 
        'control_date', 
        'plan_delv', 
        'act_dn', 
        'act_delv', 
        'os_delivery', 
        'order_produksi', 
        'plan_stock_fg', 
        'os_produksi', 
        'incoming_part', 
        'stock_fg'
    ];

    /**
     * Casting tipe data agar lebih mudah dikelola di Controller atau View.
     */
    protected $casts = [
        'control_date' => 'date',
        'plan_delv' => 'integer',
        'act_dn' => 'integer',
        'act_delv' => 'integer',
        'os_delivery' => 'integer',
        'order_produksi' => 'integer',
        'plan_stock_fg' => 'integer',
        'os_produksi' => 'integer',
        'incoming_part' => 'integer',
        'stock_fg' => 'integer',
    ];

    /**
     * Relasi ke Master Finished Good (berdasarkan Part No).
     */
    public function finishedGood()
    {
        return $this->belongsTo(FinishedGood::class, 'part_no', 'part_no');
    }
}