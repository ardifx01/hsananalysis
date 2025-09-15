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
        Schema::create('pendapatans', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->string('kode_akun');
            $table->string('nama_akun');
            $table->string('kode_opd');
            $table->string('nama_opd');
            $table->text('uraian');
            $table->text('keterangan')->nullable();
            $table->decimal('pagu', 20, 2);
            $table->foreignId('tahapan_id')->constrained('tahapan')->onDelete('cascade');
            $table->timestamp('tanggal_upload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatans');
    }
};
