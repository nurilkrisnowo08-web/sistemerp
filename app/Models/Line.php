<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    // ✨ Pastikan nama tabel sama persis dengan di HeidiSQL lu (line)
    protected $table = 'line'; 
protected $fillable = ['kode_Line', 'nama_Line'];

    // ✨ Pastikan timestamps aktif (default true, tapi buat jaga-jaga)
    public $timestamps = true;
}