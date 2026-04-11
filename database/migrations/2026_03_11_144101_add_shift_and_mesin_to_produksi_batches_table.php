<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi buat nambah kolom.
     */
    public function up(): void
    {
        Schema::table('produksi_batches', function (Blueprint $table) {
            // Kita taruh setelah no_produksi biar rapi pas dicek di HeidiSQL
            $table->string('shift')->nullable()->after('no_produksi'); 
            
            // foreignId otomatis bikin kolom mesin_id & constrained nyari tabel 'mesins'
            $table->foreignId('mesin_id')->nullable()->after('shift')->constrained('mesins')->onDelete('set null');
        });
    }

    /**
     * Rollback migrasi (buat jaga-jaga kalau mau di-reset).
     */
    public function down(): void
    {
        Schema::table('produksi_batches', function (Blueprint $table) {
            // Hapus foreign key dulu baru hapus kolomnya
            $table->dropForeign(['mesin_id']);
            $table->dropColumn(['shift', 'mesin_id']);
        });
    }
};