<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('opd_rekening_penyesuaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opd'); // OPD terkait
            $table->string('kode_rekening'); // Rekening terkait
            $table->decimal('persentase_penyesuaian', 5, 2)->default(0); // Nilai persentase penyesuaian
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_rekening_penyesuaian');
    }
};
