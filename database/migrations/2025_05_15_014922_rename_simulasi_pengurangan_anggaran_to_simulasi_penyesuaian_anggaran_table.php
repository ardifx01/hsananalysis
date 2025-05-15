<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('simulasi_pengurangan_anggaran', 'simulasi_penyesuaian_anggaran');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('simulasi_penyesuaian_anggaran', 'simulasi_pengurangan_anggaran');
    }
};
