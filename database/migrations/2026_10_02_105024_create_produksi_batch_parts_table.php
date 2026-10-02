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
    Schema::create('produksi_batch_parts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('batch_id')->constrained('produksi_batches')->onDelete('cascade');
        $table->string('part_no');
        $table->integer('qty_hasil_ok');
        $table->integer('qty_ng')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_batch_parts');
    }
};
