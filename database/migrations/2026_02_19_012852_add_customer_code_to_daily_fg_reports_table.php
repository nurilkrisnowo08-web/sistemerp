<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk tambah kolom.
     */
    public function up(): void
    {
        Schema::table('daily_fg_reports', function (Blueprint $table) {
            // Tambahin kolom customer_code setelah part_name
            // Gunakan nullable() biar data lama nggak error saat kolom baru ditambah
            $table->string('customer_code')->after('part_name')->nullable();
        });
    }

    /**
     * Batalkan migrasi (Hapus kolom jika di-rollback).
     */
    public function down(): void
    {
        Schema::table('daily_fg_reports', function (Blueprint $table) {
            $table->dropColumn('customer_code');
        });
    }
};