<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    // Biar bisa input data lewat form
    protected $fillable = [
        'part_no', 
        'part_name', 
        'customer_code', 
    ];
}