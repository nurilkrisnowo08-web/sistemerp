<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up() {
    // 1. Header PO: Nomor PO dan Supplier mana
    Schema::create('supplier_pos', function (Blueprint $table) {
        $table->id();
        $table->string('no_po_supplier')->unique();
        $table->string('supplier_name');
        $table->enum('status', ['PENDING', 'PARTIAL', 'COMPLETED'])->default('PENDING');
        $table->timestamps();
    });

    // 2. Detail PO: Barang apa saja yang dipesan
    Schema::create('supplier_po_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_po_id')->constrained()->onDelete('cascade');
        $table->string('material_code');
        $table->integer('qty_order');
        $table->integer('qty_received')->default(0); // Berapa yang sudah sampai gudang
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_orders_tables');
    }
};
