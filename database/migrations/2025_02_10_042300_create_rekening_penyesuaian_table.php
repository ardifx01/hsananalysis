<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rekening_penyesuaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rekening')->unique();
            $table->string('nama_rekening')->nullable();
            $table->decimal('persentase_penyesuaian', 5, 2)->default(0); // Default 0%
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rekening_penyesuaian');
    }
};
