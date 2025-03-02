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
    protected $tahapan_id;
    protected $tanggal_upload;

    public function __construct($tahapan_id, $tanggal_upload)
    {
        $this->tahapan_id = $tahapan_id;
        $this->tanggal_upload = $tanggal_upload;
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
            'tahapan_id' => $this->tahapan_id,
            'tanggal_upload' => $this->tanggal_upload,
        ]);
    }
}
