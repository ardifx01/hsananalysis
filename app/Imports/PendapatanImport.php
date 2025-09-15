<?php

namespace App\Imports;

use App\Models\Pendapatan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class PendapatanImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $tahapan_id;
    protected $tanggal_upload;

    public function __construct($tahapan_id, $tanggal_upload)
    {
        $this->tahapan_id = $tahapan_id;
        $this->tanggal_upload = $tanggal_upload;
    }

    public function model(array $row)
    {
        // Cek jika kode_akun kosong, maka abaikan baris ini
        if (empty($row['kode_akun']) || empty($row['nama_akun'])) {
            return null;
        }

        return new Pendapatan([
            'tahun' => $row['tahun'] ?? null,
            'kode_akun' => $row['kode_akun'] ?? null,
            'nama_akun' => $row['nama_akun'] ?? null,
            'kode_opd' => $row['kode_opd'] ?? null,
            'nama_opd' => $row['nama_opd'] ?? null,
            'uraian' => $row['uraian'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
            'pagu' => $row['pagu'] ?? 0, // Jika kosong, set 0
            'tahapan_id' => $this->tahapan_id,
            'tanggal_upload' => $this->tanggal_upload,
        ]);
    }
}
