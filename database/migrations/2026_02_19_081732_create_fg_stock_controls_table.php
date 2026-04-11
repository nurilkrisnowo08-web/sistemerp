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
        Schema::create('fg_stock_controls', function (Blueprint $table) {
    $table->id();
    $table->string('part_no');
    $table->date('control_date'); // Untuk mapping tanggal 1-31
    
    // Kategori Kontrol sesuai Excel
    $table->integer('plan_delv')->default(0);      // Rencana Kirim
    $table->integer('act_dn')->default(0);         // Aktual DN
    $table->integer('act_delv')->default(0);       // Aktual Kirim (Barang Keluar)
    $table->integer('os_delivery')->default(0);    // Sisa Kirim
    $table->integer('order_produksi')->default(0); // Rencana Produksi
    $table->integer('plan_stock_fg')->default(0);  // Rencana Stok
    $table->integer('os_produksi')->default(0);    // Sisa Produksi
    $table->integer('incoming_part')->default(0);  // Barang Masuk
    $table->integer('stock_fg')->default(0);       // Saldo Stok Akhir
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fg_stock_controls');
    }
};
