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
    Schema::create('finished_goods', function (Blueprint $table) {
        $table->id();
        $table->string('part_no')->unique();       // Contoh: 57153-BZ110
        $table->string('part_name');              // Contoh: BLANK (57153-BZ110)
        $table->string('customer');               // Contoh: FTI, SAI
        $table->integer('qty_per_unit')->default(1);
        $table->integer('qty_per_pallet');
        $table->integer('qty_order')->default(0);
        $table->integer('needs_per_day');         // Kebutuhan per hari
        $table->integer('min_stock_pcs');         // Min Stock dalam Pcs
        $table->integer('max_stock_pcs');         // Max Stock dalam Pcs
        $table->integer('act_stock')->default(0); // Stok saat ini (Actual Stock)
        $table->text('remark')->nullable();
        $table->timestamps();
    });
}
};
