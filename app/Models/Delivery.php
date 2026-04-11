<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    protected $fillable = ['po_id', 'customer_code', 'part_no', 'no_sj', 'qty_delivery', 'status'];

    // Relasi ke Purchase Order
    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    // Relasi ke Master Part untuk ambil Nama Barang
    public function masterPart() {
        return $this->belongsTo(Part::class, 'part_no', 'part_no');
    }
}