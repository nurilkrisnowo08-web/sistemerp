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
    Schema::create('mesins', function (Blueprint $table) {
        $table->id();
        $table->string('kode_mesin')->unique(); // Contoh: MCN-01
        $table->string('nama_mesin');           // Contoh: Mesin Stamping 1
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesins');
    }
};
