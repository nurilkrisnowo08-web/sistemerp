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
    Schema::create('deliveries', function (Blueprint $table) {
        $table->id();
        $table->string('no_sj');           // Nomor Surat Jalan otomatis
        $table->string('customer_code');    // Kode Customer (FTI, AMA, dll)
        $table->string('part_no');         // Nomor Part
        $table->integer('qty_delivery');   // Jumlah barang
        $table->string('status')->default('DRAFT'); // Status: DRAFT, READY, SHIPPED
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
