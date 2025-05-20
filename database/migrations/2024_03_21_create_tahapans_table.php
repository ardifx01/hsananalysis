<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tahapan', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Insert default data
        DB::table('tahapan')->insert([
            ['name' => 'APBD Awal', 'description' => 'Anggaran Pendapatan dan Belanja Daerah Awal', 'order' => 1],
            ['name' => 'APBD Perubahan', 'description' => 'Anggaran Pendapatan dan Belanja Daerah Perubahan', 'order' => 2],
            ['name' => 'APBD Perubahan II', 'description' => 'Anggaran Pendapatan dan Belanja Daerah Perubahan II', 'order' => 3],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapan');
    }
}; 