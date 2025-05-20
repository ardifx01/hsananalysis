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
            'tahun' => $row['tahun'] ?? null,
            'kode_urusan' => $row['kode_urusan'] ?? null,
            'nama_urusan' => $row['nama_urusan'] ?? null,
            'kode_skpd' => $row['kode_skpd'] ?? null,
            'nama_skpd' => $row['nama_skpd'] ?? null,
            'kode_sub_unit' => $row['kode_sub_unit'] ?? null,
            'nama_sub_unit' => $row['nama_sub_unit'] ?? null,
            'kode_bidang_urusan' => $row['kode_bidang_urusan'] ?? null,
            'nama_bidang_urusan' => $row['nama_bidang_urusan'] ?? null,
            'kode_program' => $row['kode_program'] ?? null,
            'nama_program' => $row['nama_program'] ?? null,
            'kode_kegiatan' => $row['kode_kegiatan'] ?? null,
            'nama_kegiatan' => $row['nama_kegiatan'] ?? null,
            'kode_sub_kegiatan' => $row['kode_sub_kegiatan'] ?? null,
            'nama_sub_kegiatan' => $row['nama_sub_kegiatan'] ?? null,
            'kode_sumber_dana' => $row['kode_sumber_dana'] ?? null,
            'nama_sumber_dana' => $row['nama_sumber_dana'] ?? null,
            'kode_rekening' => $row['kode_rekening'] ?? null,
            'nama_rekening' => $row['nama_rekening'] ?? null,
            'kode_standar_harga' => $row['kode_standar_harga'] ?? null,
            'nama_standar_harga' => $row['nama_standar_harga'] ?? null,
            'pagu' => $row['pagu'] ?? 0, // Jika kosong, set 0
            'tahapan_id' => $this->tahapan_id,
            'tanggal_upload' => $this->tanggal_upload,
        ]);
    }
}
