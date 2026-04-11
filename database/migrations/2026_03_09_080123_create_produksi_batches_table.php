<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('produksi_batches', function (Blueprint $table) {
        $table->id();
        $table->string('no_produksi', 50)->nullable();
        $table->string('material_code', 255); // Tempat Part No
        $table->integer('qty_ambil_pcs')->default(0); // Target Produksi
        $table->integer('qty_hasil_ok')->default(0);
        $table->integer('qty_ng_material')->default(0); // Dikunci cuma buat angka!
        $table->integer('qty_ng_process')->default(0);
        $table->integer('qty_hasil_ng')->default(0);
        $table->integer('qty_hasil_scrap')->default(0);
        $table->string('penempatan', 50)->nullable();
        $table->text('keterangan')->nullable();
        $table->decimal('durasi_hari', 8, 2)->default(0);
        $table->string('status', 255)->default('PROSES');
        $table->timestamps(); // Bikin created_at & updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_batches');
    }
};
