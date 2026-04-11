<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('parts', function (Blueprint $table) {
        $table->id();
        $table->string('part_no')->unique(); // ID Unik Barang
        $table->string('part_name');         // Nama Barang
        $table->string('customer_code');     // Kode Customer (Konek ke Master Customer)
        $table->timestamps();
    });
}
};
