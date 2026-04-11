<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id'); // Link ke ID PO
            $table->string('part_no');
            $table->string('part_name')->nullable();
            $table->integer('quantity'); // Jumlah yang dipesan di PO
            $table->timestamps();

            // Opsional: Kasih foreign key biar datanya nyambung terus
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};