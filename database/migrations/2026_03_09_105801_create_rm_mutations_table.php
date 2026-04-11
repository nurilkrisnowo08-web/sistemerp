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
    Schema::create('rm_mutations', function (Blueprint $table) {
        $table->id();
        $table->string('material_code'); // Merujuk ke material_code di rm_stocks
        $table->integer('qty');
        $table->enum('type', ['IN', 'OUT']); // Mencatat masuk atau keluar
        $table->timestamps(); // Ini otomatis mencatat jam dan tanggal mutasi
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_mutations');
    }
};
