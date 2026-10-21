<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_plans', function (Blueprint $table) {
            $table->id();
            $table->date('plan_date'); // Tanggal WOS Delivery
            $table->string('part_no'); // Mengikat ke tabel Parts
            $table->string('customer_code')->nullable(); // SAI, TMMIN, dll
            
            // Waktu Pengambilan & Pengiriman
            $table->string('pulling_time')->nullable(); // Contoh: 07:00
            $table->string('delv_time')->nullable();    // Contoh: 09:00, 11:00, 14:00
            
            // Kalkulasi Kuantitas
            $table->integer('blnc_delv')->default(0);   // Sisa hutang kirim (Balance)
            $table->integer('plan_delv')->default(0);   // Rencana kirim hari ini
            $table->integer('total_delv')->default(0);  // Total (Blnc + Plan)
            
            // Hasil Aktual
            $table->integer('act_delv_s1')->default(0); // Aktual kirim Shift 1
            $table->integer('act_delv_s2')->default(0); // Aktual kirim Shift 2
            
            // Status & Catatan
            $table->string('judge')->nullable();        // OK / DELAY / SHORTAGE
            $table->text('keterangan')->nullable();
            $table->string('status')->default('PROSES'); // PROSES / COMPLETED
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_plans');
    }
};