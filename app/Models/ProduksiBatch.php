<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiBatch extends Model
{
    // Nama tabel di database
    protected $table = 'produksi_batches';

    protected $fillable = [
        'no_produksi', 
        'shift', 
        'mesin_id',
        'rm_stock_id',    // ✨ WAJIB ADA: Untuk mencatat material Coil apa yang dipakai
        'qty_ambil_pcs',  // Jumlah total potongan dari Coil
        
        // --- KOLOM DI BAWAH INI DIPINDAH KE TABEL CHILD ---
        // 'material_code', 
        // 'qty_hasil_ok',
        // 'qty_ng_material', 
        // 'qty_ng_process', 
        // 'qty_hasil_ng', 
        // 'qty_hasil_scrap',
        // --------------------------------------------------

        'penempatan', 
        'keterangan', 
        'durasi_hari', 
        'status'
    ];

    /**
     * ✨ CASTING SAKTI
     */
    protected $casts = [
        'mesin_id'        => 'integer',
        'rm_stock_id'     => 'integer',
        'qty_ambil_pcs'   => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * ✨ RELASI KE MASTER MESIN
     */
    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    /**
     * ✨ RELASI KE TABEL CHILD (HASIL PART)
     * Biar di dashboard/view bisa panggil $batch->parts untuk melihat 1 material jadi part apa saja
     */
    public function parts()
    {
        return $this->hasMany(ProduksiBatchPart::class, 'batch_id');
    }
}