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
    Schema::table('rm_incoming_logs', function (Blueprint $table) {
        $table->string('source')->default('supplier')->after('pcs_in');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rm_incoming_logs', function (Blueprint $table) {
            //
        });
    }
};
