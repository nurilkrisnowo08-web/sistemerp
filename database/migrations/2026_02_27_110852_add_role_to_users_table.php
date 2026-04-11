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
    Schema::table('users', function (Blueprint $table) {
        // Menambahkan kolom role dengan default 'produksi'
        $table->enum('role', ['kepala_ppic', 'staff_ppic', 'produksi'])
              ->default('produksi')
              ->after('email'); // diletakkan setelah kolom email
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
