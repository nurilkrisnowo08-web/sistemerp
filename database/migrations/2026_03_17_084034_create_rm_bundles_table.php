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
    Schema::create('rm_bundles', function (Blueprint $table) {
        $table->id();
        $table->string('no_bandel')->unique();
        $table->string('spec'); // ✨ KUNCINYA DI SINI: Bandel itu milik Spec
        $table->string('customer_code');
        $table->integer('qty_awal'); 
        $table->integer('qty_sisa'); 
        $table->enum('status', ['available', 'in_process', 'depleted'])->default('available');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_bundles');
    }
};
