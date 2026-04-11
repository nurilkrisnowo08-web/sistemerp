<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('produksi_batches', function (Blueprint $table) {
        // ✨ Tambahin kolom buat nampung link ke Coil-nya rill!
        $table->unsignedBigInteger('rm_stock_id')->nullable()->after('material_code');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_batches', function (Blueprint $table) {
            //
        });
    }
};
