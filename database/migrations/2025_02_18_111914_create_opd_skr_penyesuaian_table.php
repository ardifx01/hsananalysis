<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpdSkrPenyesuaianTable extends Migration
{
    public function up()
    {
        Schema::create('opd_skr_penyesuaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opd', 50);
            $table->string('kode_sub_kegiatan', 50);
            $table->string('kode_rekening', 50);
            $table->decimal('persentase', 6, 2)->default(0);
            $table->timestamps();

            // Contoh unique index pendek
            $table->unique(['kode_opd','kode_sub_kegiatan','kode_rekening'], 'opd_skr_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('opd_skr_penyesuaian');
    }
}
