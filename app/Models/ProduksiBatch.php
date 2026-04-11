<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiBatch extends Model
{
    // Nama tabel di database
    protected $table = 'produksi_batches';

    protected $fillable = [
        'no_produksi', 
        'shift',          // ✨ TAMBAHAN BARU
        'mesin_id',       // ✨ TAMBAHAN BARU
        'material_code', 
        'qty_ambil_pcs', 
        'qty_hasil_ok',
        'qty_ng_material', 
        'qty_ng_process', 
        'qty_hasil_ng', 
        'qty_hasil_scrap',
        'penempatan', 
        'keterangan', 
        'durasi_hari', 
        'status'
    ];

    /**
     * ✨ CASTING SAKTI
     * Biar angka-angka ini otomatis jadi Integer saat dipanggil di Controller/Blade.
     */
    protected $casts = [
        'mesin_id'        => 'integer', // ✨ TAMBAHAN BARU
        'qty_ambil_pcs'   => 'integer',
        'qty_hasil_ok'    => 'integer',
        'qty_ng_material' => 'integer',
        'qty_ng_process'  => 'integer',
        'qty_hasil_ng'    => 'integer',
        'qty_hasil_scrap' => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * ✨ RELASI KE MASTER MESIN
     * Biar di dashboard bisa panggil $p->mesin->nama_mesin
     */
    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}