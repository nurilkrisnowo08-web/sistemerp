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
    Schema::table('finished_goods', function (Blueprint $table) {
        // Tambahin kolom actual_stock biar Controller nggak error lagi
        $table->integer('actual_stock')->default(0)->after('part_name');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finished_goods', function (Blueprint $table) {
            //
        });
    }
};
