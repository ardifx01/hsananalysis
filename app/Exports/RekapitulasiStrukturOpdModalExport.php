<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class RekapitulasiStrukturOpdModalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $rekapitulasiData;
    protected $kodeRekenings;
    protected $tahapanName;

    public function __construct($rekapitulasiData, $kodeRekenings, $tahapanName)
    {
        $this->rekapitulasiData = $rekapitulasiData;
        $this->kodeRekenings = $kodeRekenings;
        $this->tahapanName = $tahapanName;
    }

    public function collection()
    {
        $data = $this->rekapitulasiData;
        
        // Tambahkan row total
        $totalRow = [
            'kode_skpd' => 'TOTAL',
            'nama_skpd' => '',
            'total_anggaran' => $data->sum('total_anggaran'),
            'total_realisasi' => $data->sum('total_realisasi'),
            'total_penyesuaian' => $data->sum('total_penyesuaian'),
            'total_proyeksi' => $data->sum('total_proyeksi'),
            'struktur_belanja' => []
        ];
        
        // Hitung total per struktur belanja
        foreach ($this->kodeRekenings as $kr) {
            if (count(explode('.', $kr->kode_rekening)) === 3) {
                $totalPerRekening = $data->sum(function($opd) use ($kr) {
                    $strukturData = $opd['struktur_belanja'][$kr->kode_rekening] ?? null;
                    return $strukturData ? $strukturData['anggaran'] : 0;
                });
                
                $totalRow['struktur_belanja'][$kr->kode_rekening] = [
                    'nama_rekening' => $kr->uraian,
                    'anggaran' => $totalPerRekening,
                    'realisasi' => 0,
                    'anggaran_realisasi' => 0,
                    'penyesuaian' => 0,
                    'proyeksi' => 0,
                    'is_3_segmen' => true
                ];
            }
        }
        
        return $data->push($totalRow);
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama OPD'];
        $modalColumnAdded = false;
        
        // Tambahkan kolom struktur belanja dengan posisi modal yang benar
        foreach ($this->kodeRekenings as $kr) {
            if (count(explode('.', $kr->kode_rekening)) === 3) {
                $isModal = str_starts_with($kr->kode_rekening, '5.2.');
                if (!$isModal) {
                    $headings[] = $kr->kode_rekening . ' - ' . $kr->uraian;
                } elseif (!$modalColumnAdded) {
                    $headings[] = '5.2 - Belanja Modal';
                    $modalColumnAdded = true;
                }
            }
        }
        
        // Tambahkan kolom total anggaran
        $headings[] = 'Total Anggaran';
        
        return $headings;
    }

    public function map($opd): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        // Jika ini adalah row total, gunakan format khusus
        if ($opd['kode_skpd'] === 'TOTAL') {
            $row = [
                '',
                'TOTAL'
            ];
        } else {
            $row = [
                $rowNumber,
                $opd['nama_skpd']
            ];
        }
        
        // Hitung total modal terlebih dahulu
        $totalModal = 0;
        foreach ($this->kodeRekenings as $kr) {
            if (count(explode('.', $kr->kode_rekening)) === 3) {
                $isModal = str_starts_with($kr->kode_rekening, '5.2.');
                if ($isModal) {
                    $strukturData = $opd['struktur_belanja'][$kr->kode_rekening] ?? null;
                    $anggaran = $strukturData ? $strukturData['anggaran'] : 0;
                    $totalModal += $anggaran;
                }
            }
        }
        
        // Tambahkan data struktur belanja dengan posisi modal yang benar
        $modalColumnAdded = false;
        foreach ($this->kodeRekenings as $kr) {
            if (count(explode('.', $kr->kode_rekening)) === 3) {
                $isModal = str_starts_with($kr->kode_rekening, '5.2.');
                $strukturData = $opd['struktur_belanja'][$kr->kode_rekening] ?? null;
                $anggaran = $strukturData ? $strukturData['anggaran'] : 0;
                
                if (!$isModal) {
                    $row[] = $anggaran;
                } elseif (!$modalColumnAdded) {
                    $row[] = $totalModal;
                    $modalColumnAdded = true;
                }
            }
        }
        $row[] = $opd['total_anggaran'];
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        
        // Style untuk header
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E86AB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style untuk data
        $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style untuk kolom angka (right align)
        $dataRange = 'C2:' . $lastColumn . $lastRow;
        $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Style untuk kolom nama OPD (left align)
        $sheet->getStyle('B2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Style untuk total row
        $totalRow = $lastRow;
        $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Auto size untuk kolom nama OPD
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,  // No
            'B' => 40, // Nama OPD
        ];
        
        // Set width untuk kolom struktur belanja dengan posisi modal yang benar
        $columnIndex = 3; // Mulai dari kolom C
        $modalColumnAdded = false;
        foreach ($this->kodeRekenings as $kr) {
            if (count(explode('.', $kr->kode_rekening)) === 3) {
                $isModal = str_starts_with($kr->kode_rekening, '5.2.');
                if (!$isModal) {
                    $column = $this->getColumnLetter($columnIndex);
                    $widths[$column] = 20;
                    $columnIndex++;
                } elseif (!$modalColumnAdded) {
                    $column = $this->getColumnLetter($columnIndex);
                    $widths[$column] = 20;
                    $columnIndex++;
                    $modalColumnAdded = true;
                }
            }
        }
        
        // Set width untuk kolom total
        $totalColumn = $this->getColumnLetter($columnIndex);
        $widths[$totalColumn] = 20;
        
        return $widths;
    }

    public function title(): string
    {
        return 'Rekapitulasi Struktur OPD - Modal Digabung';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tambahkan judul di atas tabel
                $title = 'Rekapitulasi Struktur Semua OPD ' . $this->tahapanName . ' APBD 2025';
                
                // Insert row untuk judul
                $sheet->insertNewRowBefore(1, 2);
                
                // Set judul
                $sheet->setCellValue('A1', $title);
                
                // Merge cells untuk judul
                $lastColumn = $sheet->getHighestColumn();
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                
                // Style untuk judul
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Set tinggi row judul
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Geser semua data ke bawah 2 baris
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Copy data dari row 3 ke row 3 (tidak perlu copy, hanya perlu menyesuaikan style)
                $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Style untuk header (sekarang di row 3)
                $sheet->getStyle('A3:' . $highestColumn . '3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E86AB'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Style untuk kolom angka (right align) - mulai dari row 4
                $dataRange = 'C4:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Style untuk kolom nama OPD (left align) - mulai dari row 4
                $sheet->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Style untuk total row
                $totalRow = $highestRow;
                $sheet->getStyle('A' . $totalRow . ':' . $highestColumn . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }

    private function getColumnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intval($index / 26);
        }
        return $letter;
    }
}
