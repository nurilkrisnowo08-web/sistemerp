<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number');      // Pondasi utama nomor PO
            $table->string('customer_code');   // Kode Customer (FTI, AMA-P2, dll)
            $table->string('part_no');         // Part Number
            $table->integer('quantity');       // Jumlah pesanan
            $table->date('due_date');          // Tanggal jatuh tempo
            $table->string('status')->default('OPEN'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};