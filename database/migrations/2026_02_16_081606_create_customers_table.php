<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Contoh: AMA-P2, SAI, ICHII
        $table->string('name');           // Nama Perusahaan
        $table->text('address')->nullable();
        $table->string('pic')->nullable();    // Nama orang yang bisa dihubungi
        $table->string('phone')->nullable();
        $table->timestamps();
    });
}
};
