<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('rm_stocks', function (Blueprint $table) {
        $table->id();
        $table->string('material_code')->unique();
        $table->string('material_name');
        $table->string('spec')->nullable(); //
        $table->string('size')->nullable();
        $table->string('customer');
        $table->integer('stock_pcs')->default(0); // Ganti dari KG ke PCS!
        $table->integer('target_prod')->default(0); 
        $table->integer('min_stock')->default(0);
        $table->integer('max_stock')->default(0);
        // PCS_PER_LOT inilah yang jadi pembagi biar 1000 PCS jadi 10 LOT
        $table->integer('pcs_per_lot')->default(100); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_stocks');
    }
};
