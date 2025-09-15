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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
        return $this->strukturData;
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
        static $no = 1;
        
        $row = [
            $no++,
            $item['kode_rekening'],
            $item['nama_rekening'],
            $item['level']
        ];
        
        foreach ($this->tahapans as $tahapan) {
            $pagu = $item['pagu_per_tahapan'][$tahapan->id] ?? 0;
            $row[] = $pagu ? number_format($pagu, 2, ',', '.') : '-';
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
            'A:J' => [
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
            // Number columns (right align)
            'E:J' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // No
            'B' => 20,  // Kode Rekening
            'C' => 50,  // Nama Rekening
            'D' => 10,  // Level
        ];
        
        // Add column widths for each tahapan
        $columnIndex = 5; // Start from column E
        foreach ($this->tahapans as $tahapan) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $widths[$column] = 20; // Pagu per tahapan
            $columnIndex++;
        }
        
        return $widths;
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
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + count($this->tahapans));
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
                $totalColumns = 4 + count($this->tahapans);
                for ($i = 1; $i <= $totalColumns; $i++) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
