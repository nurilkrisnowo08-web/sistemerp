<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('rm_stocks', function (Blueprint $table) {
        $table->string('coil_id')->nullable()->after('material_code'); // ID unik coil
    });
}
    public function down(): void
    {
        Schema::table('rm_stocks', function (Blueprint $table) {
            //
        });
    }
};
