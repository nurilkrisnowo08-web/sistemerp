<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Utama STO (Kop Surat/Header)
        Schema::create('sto_logs', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no')->unique(); // Contoh: STO-2611-001
            $table->string('period'); // Contoh: 2026-11
            $table->string('created_by');
            $table->string('status');
            $table->timestamps();
        });

        // 2. Tabel Rincian per Part Number (Detail)
        Schema::create('sto_log_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sto_id');
            $table->string('part_no');
            
            // Log RM
            $table->integer('rm_sys')->default(0);
            $table->integer('rm_act')->default(0);
            $table->integer('rm_diff')->default(0);
            
            // Log WIP
            $table->integer('wip_sys')->default(0);
            $table->integer('wip_act')->default(0);
            $table->integer('wip_diff')->default(0);
            
            // Log FG
            $table->integer('fg_sys')->default(0);
            $table->integer('fg_act')->default(0);
            $table->integer('fg_diff')->default(0);
            
            $table->timestamps();

            // Relasi ke tabel utama (Kalau dokumen STO dihapus, rinciannya ikut terhapus)
            $table->foreign('sto_id')->references('id')->on('sto_logs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sto_log_details');
        Schema::dropIfExists('sto_logs');
    }
};