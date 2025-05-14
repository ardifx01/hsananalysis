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
        Schema::table('data_anggarans', function (Blueprint $table) {
            $table->string('kode_sub_unit')->nullable()->after('nama_skpd');
            $table->string('nama_sub_unit')->nullable()->after('kode_sub_unit');
        });
    }

    public function down(): void
    {
        Schema::table('data_anggarans', function (Blueprint $table) {
            $table->dropColumn(['kode_sub_unit', 'nama_sub_unit']);
        });
    }
};
