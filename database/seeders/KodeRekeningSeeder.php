<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KodeRekening;

class KodeRekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kodeRekenings = [
            [
                'kode_rekening' => '5.1.01.01',
                'uraian' => 'Belanja Gaji dan Tunjangan ASN',
            ],
            [
                'kode_rekening' => '5.1.01.02',
                'uraian' => 'Tambahan Penghasilan berdasarkan Beban Kerja ASN',
            ],
            [
                'kode_rekening' => '5.1.02.01',
                'uraian' => 'Belanja Barang Pakai Habis',
            ],
            [
                'kode_rekening' => '5.1.02.02',
                'uraian' => 'Belanja Jasa Kantor',
            ],
            [
                'kode_rekening' => '5.1.02.03',
                'uraian' => 'Belanja Pemeliharaan',
            ],
            [
                'kode_rekening' => '5.1.02.04',
                'uraian' => 'Belanja Perjalanan Dinas',
            ],
            [
                'kode_rekening' => '5.1.02.05',
                'uraian' => 'Belanja Barang untuk Diserahkan kepada Masyarakat/Pihak Ketiga',
            ],
            [
                'kode_rekening' => '5.2.01.01',
                'uraian' => 'Belanja Modal Tanah',
            ],
            [
                'kode_rekening' => '5.2.02.01',
                'uraian' => 'Belanja Modal Peralatan dan Mesin',
            ],
            [
                'kode_rekening' => '5.2.03.01',
                'uraian' => 'Belanja Modal Gedung dan Bangunan',
            ],
            [
                'kode_rekening' => '5.2.04.01',
                'uraian' => 'Belanja Modal Jalan, Jaringan, dan Irigasi',
            ],
            [
                'kode_rekening' => '5.2.05.01',
                'uraian' => 'Belanja Modal Aset Tetap Lainnya',
            ],
        ];

        foreach ($kodeRekenings as $kodeRekening) {
            if (!KodeRekening::where('kode_rekening', $kodeRekening['kode_rekening'])->exists()) {
                KodeRekening::create($kodeRekening);
            }
        }
    }
}
