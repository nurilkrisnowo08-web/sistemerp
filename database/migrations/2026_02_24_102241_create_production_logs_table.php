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
    Schema::create('production_logs', function (Blueprint $table) {
        $table->id();
        $table->string('part_no'); 
        $table->integer('qty');    
        $table->timestamps(); // Ini bakal otomatis ngisi jam masuk (WIB)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
