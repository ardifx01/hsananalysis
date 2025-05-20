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
            if (!Schema::hasColumn('data_anggarans', 'tahun')) $table->integer('tahun')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_urusan')) $table->string('kode_urusan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_urusan')) $table->string('nama_urusan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_skpd')) $table->string('kode_skpd')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_skpd')) $table->string('nama_skpd')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_sub_unit')) $table->string('kode_sub_unit')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_sub_unit')) $table->string('nama_sub_unit')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_bidang_urusan')) $table->string('kode_bidang_urusan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_bidang_urusan')) $table->string('nama_bidang_urusan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_program')) $table->string('kode_program')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_program')) $table->string('nama_program')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_kegiatan')) $table->string('kode_kegiatan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_kegiatan')) $table->string('nama_kegiatan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_sub_kegiatan')) $table->string('kode_sub_kegiatan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_sub_kegiatan')) $table->string('nama_sub_kegiatan')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_sumber_dana')) $table->string('kode_sumber_dana')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_sumber_dana')) $table->string('nama_sumber_dana')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_rekening')) $table->string('kode_rekening')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_rekening')) $table->string('nama_rekening')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'kode_standar_harga')) $table->string('kode_standar_harga')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'nama_standar_harga')) $table->string('nama_standar_harga')->nullable();
            if (!Schema::hasColumn('data_anggarans', 'pagu')) $table->decimal('pagu', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_anggarans', function (Blueprint $table) {
            // Tidak perlu drop kolom pada down agar data tetap aman
        });
    }
}; 