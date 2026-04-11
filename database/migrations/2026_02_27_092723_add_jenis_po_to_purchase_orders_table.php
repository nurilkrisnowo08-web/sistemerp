<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::table('purchase_orders', function (Blueprint $table) {
        // Tambahkan kolom jenis_po setelah customer_code
        $table->string('jenis_po')->default('REGULER')->after('customer_code');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            //
        });
    }
};
