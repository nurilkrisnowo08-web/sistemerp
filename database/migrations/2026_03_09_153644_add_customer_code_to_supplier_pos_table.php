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
    Schema::table('supplier_pos', function (Blueprint $table) {
        // ✨ Membuat laci customer_code setelah kolom supplier_name
        $table->string('customer_code')->after('supplier_name')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_pos', function (Blueprint $table) {
            //
        });
    }
};
