<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataAnggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('data_anggarans')->insert([
            [
                'kode_skpd' => '1.01.0.00.0.00.01.0000',
                'nama_skpd' => 'Dinas Pendidikan',
                'kode_sub_kegiatan' => '1.01.02.2.01.0001',
                'nama_sub_kegiatan' => 'Pembangunan Unit Sekolah Baru (USB)',
                'kode_rekening' => '5.1.02.01.01.0025',
                'nama_rekening' => 'Belanja Alat/Bahan untuk Kegiatan Kantor',
                'pagu' => 279064.00,
                'tipe_data' => 'original',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_skpd' => '1.01.0.00.0.00.01.0000',
                'nama_skpd' => 'Dinas Pendidikan',
                'kode_sub_kegiatan' => '1.01.02.2.01.0001',
                'nama_sub_kegiatan' => 'Pembangunan Unit Sekolah Baru (USB)',
                'kode_rekening' => '5.1.02.04.01.0003',
                'nama_rekening' => 'Belanja Perjalanan Dinas Dalam Kota',
                'pagu' => 19020000.00,
                'tipe_data' => 'original',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_skpd' => '1.01.0.00.0.00.01.0000',
                'nama_skpd' => 'Dinas Pendidikan',
                'kode_sub_kegiatan' => '1.01.02.2.01.0003',
                'nama_sub_kegiatan' => 'Pembangunan Ruang Guru/Kepala Sekolah/TU',
                'kode_rekening' => '5.1.02.01.01.0024',
                'nama_rekening' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Alat',
                'pagu' => 19500.00,
                'tipe_data' => 'revisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
