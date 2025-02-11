<?php

namespace App\Imports;

use App\Models\DataAnggaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;



class DataAnggaranImport implements ToModel, WithHeadingRow
{
    private $tipe;

    public function __construct($tipe)
    {
        $this->tipe = $tipe;
    }

    public function model(array $row)
    {
        // Cek jika kode_rekening kosong, maka abaikan baris ini
        if (empty($row['kode_rekening']) || empty($row['nama_rekening'])) {
            return null;
        }

        return new DataAnggaran([
            'kode_skpd' => $row['kode_skpd'],
            'nama_skpd' => $row['nama_skpd'],
            'kode_sub_kegiatan' => $row['kode_sub_kegiatan'],
            'nama_sub_kegiatan' => $row['nama_sub_kegiatan'],
            'kode_rekening' => $row['kode_rekening'],
            'nama_rekening' => $row['nama_rekening'],
            'pagu' => $row['pagu'] ?? 0, // Jika kosong, set 0
            'tipe_data' => $this->tipe,
        ]);
    }
}
