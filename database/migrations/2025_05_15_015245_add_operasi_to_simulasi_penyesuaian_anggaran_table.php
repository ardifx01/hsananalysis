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
        Schema::table('simulasi_penyesuaian_anggaran', function (Blueprint $table) {
            $table->string('operasi', 1)->nullable()->after('kode_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulasi_penyesuaian_anggaran', function (Blueprint $table) {
            $table->dropColumn('operasi');
        });
    }
};
