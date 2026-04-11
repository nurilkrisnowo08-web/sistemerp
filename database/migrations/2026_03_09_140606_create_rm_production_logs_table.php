<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    // 🛡️ Proteksi: Jika sudah ada di HeidiSQL, jangan buat lagi tapi catat "DONE"
    if (!Schema::hasTable('rm_incoming_logs')) {
        Schema::create('rm_incoming_logs', function (Blueprint $table) {
            $table->id();
            $table->string('material_code');
            $table->integer('pcs_in');
            $table->timestamps();
        });
    }

    if (!Schema::hasTable('rm_production_logs')) {
        Schema::create('rm_production_logs', function (Blueprint $table) {
            $table->id();
            $table->string('material_code');
            $table->integer('pcs_used');
            $table->timestamps();
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_production_logs');
    }
};
