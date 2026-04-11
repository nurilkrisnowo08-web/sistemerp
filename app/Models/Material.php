<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    // Sesuai dengan tabel master_materials di HeidiSQL lo
    protected $table = 'master_materials'; 

    protected $fillable = [
        'material_type', 
        'thickness', 
        'size', 
        'full_spec', 
        'customer_code',
        'std_qty_batch', // <--- SAKTI: Tambahkan ini agar bisa disimpan!
    ];
}