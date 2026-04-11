<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('master_materials', function (Blueprint $table) {
    $table->id();
    $table->string('material_type');
    $table->string('thickness');
    $table->string('size');
    $table->string('full_spec');
    $table->string('customer_code');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_materials');
    }
};
