<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    // 1. DAFTARKAN SEMUA KOLOM DI SINI BIAR GAK MENTAL
   protected $fillable = [
    'po_number', 'customer_code', 'due_date', 'part_no', 
    'quantity', 'status', 'jenis_po', 'keterangan' // <--- PASTIKAN ADA INI!
];

 public function deliveries()
{
    // SAKTI: Gunakan 'po_id' sesuai kenyataan di HeidiSQL lo!
    return $this->hasMany(Delivery::class, 'po_id', 'id');
}

public function getTotalSentAttribute() 
{
    // FIX BUG: Kita filter berdasarkan Part Number juga biar gak ketuker/0!
    return $this->deliveries()
                ->where('part_no', $this->part_no)
                ->sum('qty_delivery');
}
}