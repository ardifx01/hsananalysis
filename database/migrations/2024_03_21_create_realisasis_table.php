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
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opd');
            $table->date('periode');
            $table->string('kode_rekening');
            $table->text('uraian');
            $table->decimal('anggaran', 15, 2);
            $table->decimal('realisasi', 15, 2);
            $table->decimal('persentase', 5, 2);
            $table->decimal('realisasi_ly', 15, 2);
            $table->timestamps();

            // Add indexes for better performance
            $table->index('kode_opd');
            $table->index('periode');
            $table->index('kode_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasis');
    }
}; 