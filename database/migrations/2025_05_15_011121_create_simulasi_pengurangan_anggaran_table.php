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
        Schema::create('simulasi_pengurangan_anggaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opd');
            $table->string('kode_rekening');
            $table->decimal('nilai', 20, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulasi_pengurangan_anggaran');
    }
};
