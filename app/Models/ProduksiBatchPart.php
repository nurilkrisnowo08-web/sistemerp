<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiBatchPart extends Model
{
    // Nama tabel di database yang barusan kamu migrate di Hostinger
    protected $table = 'produksi_batch_parts';

    protected $fillable = [
        'batch_id', 
        'part_no', 
        'qty_hasil_ok', 
        'qty_ng'
    ];

    /**
     * ✨ RELASI BALIK KE PARENT BATCH
     */
    public function batch()
    {
        return $this->belongsTo(ProduksiBatch::class, 'batch_id');
    }
}