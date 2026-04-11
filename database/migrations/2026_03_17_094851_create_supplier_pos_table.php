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
    // ✨ JURUS SAFETY: Cek dulu tabelnya udah ada belum
    if (!Schema::hasTable('supplier_pos')) {
        Schema::create('supplier_pos', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->string('supplier_name');
            $table->string('spec');
            $table->integer('qty_order');
            $table->enum('status', ['OPEN', 'ARRIVED', 'CANCELLED'])->default('OPEN');
            $table->timestamps();
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_pos');
    }
};
