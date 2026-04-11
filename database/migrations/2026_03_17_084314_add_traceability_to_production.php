<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('produksi_batches', function (Blueprint $table) {
        // Cek dulu, kalau qty_return_warehouse BELUM ADA, baru bikin
        if (!Schema::hasColumn('produksi_batches', 'qty_return_warehouse')) {
            $table->integer('qty_return_warehouse')->default(0)->after('qty_hasil_ng');
        }
        
        // Cek return_reason
        if (!Schema::hasColumn('produksi_batches', 'return_reason')) {
            $table->text('return_reason')->nullable()->after('qty_return_warehouse');
        }

        // Cek bandel_id (Buat sistem Perbandel lo)
        if (!Schema::hasColumn('produksi_batches', 'bandel_id')) {
            $table->unsignedBigInteger('bandel_id')->nullable()->after('material_code');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production', function (Blueprint $table) {
            //
        });
    }
};
