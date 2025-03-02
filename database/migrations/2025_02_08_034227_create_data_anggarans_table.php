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
        Schema::create('data_anggarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_skpd');
            $table->string('nama_skpd');
            $table->string('kode_sub_kegiatan');
            $table->text('nama_sub_kegiatan');
            $table->string('kode_rekening');
            $table->string('nama_rekening');
            $table->decimal('pagu', 15, 2);
            $table->foreignId('tahapan_id')->constrained('tahapan')->onDelete('cascade');
            $table->timestamp('tanggal_upload'); // Ubah menjadi timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_anggarans');
    }
};
