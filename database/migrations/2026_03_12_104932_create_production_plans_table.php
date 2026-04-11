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
    Schema::create('production_plans', function (Blueprint $table) {
        $table->id();
        $table->string('order_no')->unique(); // Contoh: PO-2024-001
        $table->string('part_no');
        $table->integer('plan_qty');
        $table->integer('actual_qty')->default(0);
        $table->date('due_date');
        $table->enum('status', ['WAITING', 'RUNNING', 'LATE', 'COMPLETED'])->default('WAITING');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
