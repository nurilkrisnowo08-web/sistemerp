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
    Schema::table('finished_goods', function (Blueprint $table) {
        // is_welding buat penanda barangnya
        // welding_stock buat nyimpen jumlah barang di area welding
        $table->integer('welding_stock')->default(0)->after('actual_stock');
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
