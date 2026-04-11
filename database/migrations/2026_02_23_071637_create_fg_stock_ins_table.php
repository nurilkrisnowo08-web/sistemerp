<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fg_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->string('customer'); // Untuk filter customer (ICHII, dll)
            $table->string('part_no');
            $table->integer('qty');
            $table->timestamps(); // Ini buat 'created_at' yang lo panggil di Controller
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fg_stock_ins');
    }
};