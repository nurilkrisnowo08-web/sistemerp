<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menghubungkan Delivery ke PO.
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Cek dulu apakah kolom po_id sudah ada, kalau belum baru buat
            if (!Schema::hasColumn('deliveries', 'po_id')) {
                $table->foreignId('po_id') 
                      ->after('id') // Biar rapi di urutan database
                      ->nullable() 
                      ->constrained('purchase_orders') 
                      ->onDelete('cascade'); // PO dihapus, SJ ikut hapus!
            }
        });
    }

    /**
     * Batalkan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Hapus constraint foreign key dulu baru hapus kolomnya
            $table->dropForeign(['po_id']);
            $table->dropColumn('po_id');
        });
    }
};