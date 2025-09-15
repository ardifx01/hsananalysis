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

class StrukturBelanjaApbdExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $strukturData;
    protected $tahapans;

    public function __construct($strukturData, $tahapans)
    {
        $this->strukturData = $strukturData;
        $this->tahapans = $tahapans;
    }

    public function collection()
    {
        $data = $this->strukturData;
        
        // Tambahkan row total
        $totalRow = [
            'kode_rekening' => 'TOTAL',
            'nama_rekening' => '',
            'level' => '',
            'pagu_per_tahapan' => []
        ];
        
        // Hitung total per tahapan (hanya level 2)
        foreach ($this->tahapans as $tahapan) {
            $totalPerTahapan = $data->where('level', 2)->sum(function($item) use ($tahapan) {
                return $item['pagu_per_tahapan'][$tahapan->id] ?? 0;
            });
            $totalRow['pagu_per_tahapan'][$tahapan->id] = $totalPerTahapan;
        }
        
        return $data->push($totalRow);
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Kode Rekening',
            'Nama Rekening',
            'Level'
        ];
        
        foreach ($this->tahapans as $tahapan) {
            $headings[] = $tahapan->name;
        }
        
        return $headings;
    }

    public function map($item): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        // Jika ini adalah row total, gunakan format khusus
        if ($item['kode_rekening'] === 'TOTAL') {
            $row = [
                '',
                'TOTAL',
                '',
                ''
            ];
        } else {
            $row = [
                $rowNumber,
                $item['kode_rekening'],
                $item['nama_rekening'],
                $item['level']
            ];
        }
        
        // Tambahkan data pagu per tahapan
        foreach ($this->tahapans as $tahapan) {
            $pagu = $item['pagu_per_tahapan'][$tahapan->id] ?? 0;
            $row[] = $pagu;
        }
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '4472C4',
                    ],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Data rows
            'A:Z' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Number columns (right align) - mulai dari kolom E
            'E:Z' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 20,  // Kode Rekening
            'C' => 50,  // Nama Rekening
            'D' => 10,  // Level
            'E' => 20,  // Tahapan 1
            'F' => 20,  // Tahapan 2
            'G' => 20,  // Tahapan 3
            'H' => 20,  // Tahapan 4
            'I' => 20,  // Tahapan 5
            'J' => 20,  // Tahapan 6
        ];
    }

    public function title(): string
    {
        return 'Struktur Belanja APBD - Semua Tahapan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set title
                $sheet->insertNewRowBefore(1, 2);
                $lastColumn = chr(ord('A') + 3 + count($this->tahapans) - 1);
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->setCellValue('A1', 'STRUKTUR BELANJA APBD - SEMUA TAHAPAN TAHUN 2025');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Add filter info
                $sheet->setCellValue('A2', 'Data menampilkan semua tahapan anggaran');
                $sheet->getStyle('A2')->getFont()->setSize(10);
                
                // Adjust row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(3)->setRowHeight(20);
                
                // Auto-size columns
                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
