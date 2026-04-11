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
    Schema::table('purchase_orders', function (Blueprint $table) {
        // Kita hapus "after('quantity')" biar nggak error kalau kolomnya nggak ada
        
        // Cek & Tambah kolom quantity jika belum ada
        if (!Schema::hasColumn('purchase_orders', 'quantity')) {
            $table->integer('quantity')->nullable();
        }

        // Cek & Tambah kolom due_date jika belum ada
        if (!Schema::hasColumn('purchase_orders', 'due_date')) {
            $table->date('due_date')->nullable();
        }
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
