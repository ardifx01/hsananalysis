<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_anggarans', function (Blueprint $table) {
            $table->text('nama_kegiatan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_anggarans', function (Blueprint $table) {
            $table->string('nama_kegiatan')->nullable()->change();
        });
    }
}; 