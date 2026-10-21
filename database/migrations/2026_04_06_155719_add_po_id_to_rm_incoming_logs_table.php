<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rm_incoming_logs', function (Blueprint $table) {
            // SAYA MATIKAN SEMENTARA KARENA KOLOM PO_ID SUDAH ADA DI DATABASE
            // $table->integer('po_id')->nullable()->after('rm_stock_id');
        });
    }

    public function down()
    {
        Schema::table('rm_incoming_logs', function (Blueprint $table) {
            $table->dropColumn('po_id');
        });
    }
};;