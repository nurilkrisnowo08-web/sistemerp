<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
    Schema::create('daily_fg_reports', function (Blueprint $table) {
        $table->id();
        $table->date('report_date');
        $table->string('part_no');
        $table->string('part_name');
        $table->integer('stock_awal')->default(0);
        $table->integer('in')->default(0);
        $table->integer('out')->default(0);
        $table->integer('stock_akhir')->default(0);
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}
};
