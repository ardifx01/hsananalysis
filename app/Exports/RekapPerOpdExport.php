<?php

namespace App\Exports;

use App\Models\DataAnggaran;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapPerOpdExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $kode_opd;

    public function __construct($kode_opd)
    {
        $this->kode_opd = $kode_opd;
    }

    public function collection()
    {
        $query = DB::table('data_anggarans')
            ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
            ->select(
                'data_anggarans.kode_skpd',
                'data_anggarans.nama_skpd',
                'data_anggarans.kode_rekening',
                'data_anggarans.nama_rekening',
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
                DB::raw('COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian'),
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) * (COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) / 100) as nilai_penyesuaian'),
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) - (SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) * (COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) / 100)) as pagu_setelah_penyesuaian')
            )
            ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'rekening_penyesuaian.persentase_penyesuaian')
            ->orderBy('data_anggarans.kode_rekening', 'asc');

        if ($this->kode_opd) {
            $query->where('data_anggarans.kode_skpd', $this->kode_opd);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Kode OPD',
            'Nama OPD',
            'Kode Rekening',
            'Nama Rekening',
            'Pagu Original',
            'Persentase Penyesuaian (%)',
            'Nilai Penyesuaian',
            'Pagu Setelah Penyesuaian'
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_skpd,
            $row->nama_skpd,
            $row->kode_rekening,
            $row->nama_rekening,
            number_format($row->pagu_original, 0, ',', '.'),
            $row->persentase_penyesuaian . "%",
            number_format($row->nilai_penyesuaian, 0, ',', '.'),
            number_format($row->pagu_setelah_penyesuaian, 0, ',', '.'),
        ];
    }
}
