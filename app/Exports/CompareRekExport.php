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
use Illuminate\Support\Collection;

class CompareRekExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $rekap;
    protected $tahapans;
    protected $availableTahapans;
    protected $tahapanId;
    protected $keyword;
    protected $tahapansData;

    public function __construct($rekap, $tahapans, $availableTahapans, $tahapanId, $keyword)
    {
        $this->rekap = $rekap;
        $this->tahapans = $tahapans;
        $this->availableTahapans = $availableTahapans;
        $this->tahapanId = $tahapanId;
        $this->keyword = $keyword;
        $this->tahapansData = $tahapans;
    }

    public function collection()
    {
        $data = collect();
        $currentSkpd = null;
        $skpdTotal = 0;
        $rowNumber = 1;

        foreach ($this->rekap as $item) {
            // Jika SKPD berubah, tambahkan row total SKPD sebelumnya
            if ($currentSkpd !== null && $currentSkpd !== $item->kode_skpd) {
                $data->push($this->createSkpdTotalRow($currentSkpd, $skpdTotal, $rowNumber++));
                $skpdTotal = 0;
            }

            // Tambahkan data item
            $data->push($item);
            $rowNumber++;

            // Update total SKPD
            if ($this->tahapanId) {
                $skpdTotal += $item->total_pagu;
            } else {
                if ($item->tahapan_id == $this->availableTahapans->first()) {
                    $skpdTotal += $item->total_pagu;
                }
            }

            $currentSkpd = $item->kode_skpd;
        }

        // Tambahkan total SKPD terakhir
        if ($currentSkpd !== null) {
            $data->push($this->createSkpdTotalRow($currentSkpd, $skpdTotal, $rowNumber++));
        }

        // Tambahkan grand total
        $data->push($this->createGrandTotalRow($rowNumber++));

        return $data;
    }

    private function createSkpdTotalRow($kodeSkpd, $skpdTotal, $rowNumber)
    {
        $skpdName = $this->rekap->where('kode_skpd', $kodeSkpd)->first()->nama_skpd ?? '';
        
        $row = (object) [
            'row_type' => 'skpd_total',
            'kode_skpd' => 'TOTAL ' . $kodeSkpd . ' - ' . $skpdName,
            'nama_skpd' => '',
            'kode_rekening' => '',
            'nama_rekening' => '',
            'nama_standar_harga' => '',
            'tahapan_id' => null,
            'total_pagu' => $skpdTotal
        ];

        return $row;
    }

    private function createGrandTotalRow($rowNumber)
    {
        $grandTotal = $this->rekap->sum('total_pagu');
        
        $row = (object) [
            'row_type' => 'grand_total',
            'kode_skpd' => 'GRAND TOTAL',
            'nama_skpd' => '',
            'kode_rekening' => '',
            'nama_rekening' => '',
            'nama_standar_harga' => '',
            'tahapan_id' => null,
            'total_pagu' => $grandTotal
        ];

        return $row;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Kode SKPD',
            'Nama SKPD',
            'Kode Rekening',
            'Nama Rekening',
            'Nama Standar Harga'
        ];

        if ($this->tahapanId) {
            $tahapanName = $this->tahapansData->find($this->tahapanId)->name ?? 'Tahapan ' . $this->tahapanId;
            $headings[] = $tahapanName;
        } else {
            foreach ($this->availableTahapans as $tahapanId) {
                $tahapanName = $this->tahapansData->find($tahapanId)->name ?? 'Tahapan ' . $tahapanId;
                $headings[] = $tahapanName;
            }
        }

        return $headings;
    }

    public function map($item): array
    {
        static $rowNumber = 1;
        
        // Jika ini adalah row total, gunakan format khusus
        if (isset($item->row_type) && $item->row_type === 'skpd_total') {
            $row = [
                $rowNumber++,
                $item->kode_skpd, // Sudah berisi "TOTAL X - Nama SKPD"
                '', // Nama SKPD kosong
                '', // Kode Rekening kosong
                '', // Nama Rekening kosong
                ''  // Nama Standar Harga kosong
            ];

            if ($this->tahapanId) {
                $row[] = $item->total_pagu; // Langsung angka, bukan string
            } else {
                foreach ($this->availableTahapans as $tahapanId) {
                    $row[] = $item->total_pagu; // Langsung angka, bukan string
                }
            }

            return $row;
        }

        // Jika ini adalah row grand total
        if (isset($item->row_type) && $item->row_type === 'grand_total') {
            $row = [
                $rowNumber++,
                $item->kode_skpd, // "GRAND TOTAL"
                '', // Nama SKPD kosong
                '', // Kode Rekening kosong
                '', // Nama Rekening kosong
                ''  // Nama Standar Harga kosong
            ];

            if ($this->tahapanId) {
                $row[] = $item->total_pagu; // Langsung angka, bukan string
            } else {
                foreach ($this->availableTahapans as $tahapanId) {
                    $row[] = $item->total_pagu; // Langsung angka, bukan string
                }
            }

            return $row;
        }

        // Row data normal
        $row = [
            $rowNumber++,
            $item->kode_skpd,
            $item->nama_skpd,
            $item->kode_rekening,
            $item->nama_rekening,
            $item->nama_standar_harga
        ];

        if ($this->tahapanId) {
            $row[] = $item->total_pagu; // Langsung angka, bukan string
        } else {
            foreach ($this->availableTahapans as $tahapanId) {
                $nilai = ($item->tahapan_id == $tahapanId) ? $item->total_pagu : 0;
                $row[] = $nilai; // Langsung angka, bukan string
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->getLastColumn();
        $lastRow = $this->rekap->count() + 10; // Tambahan untuk row total
        
        // Style untuk header
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0056B3']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style untuk semua sel
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style untuk row total SKPD (warna kuning)
        $this->styleTotalRows($sheet, $lastColumn, $lastRow);

        // Auto-size columns
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $sheet;
    }

    private function styleTotalRows(Worksheet $sheet, $lastColumn, $lastRow)
    {
        // Cari row yang berisi "TOTAL" atau "GRAND TOTAL"
        for ($row = 2; $row <= $lastRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if (is_string($cellValue) && (strpos($cellValue, 'TOTAL') !== false || strpos($cellValue, 'GRAND TOTAL') !== false)) {
                // Style untuk row total
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => strpos($cellValue, 'GRAND TOTAL') !== false ? ['rgb' => '343A40'] : ['rgb' => 'FFF3CD']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => strpos($cellValue, 'GRAND TOTAL') !== false ? ['rgb' => 'FFFFFF'] : ['rgb' => '000000']
                    ]
                ]);
            }
        }
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // No
            'B' => 25,  // Kode SKPD (lebih lebar untuk "TOTAL X - Nama SKPD")
            'C' => 25,  // Nama SKPD
            'D' => 20,  // Kode Rekening
            'E' => 30,  // Nama Rekening
            'F' => 30,  // Nama Standar Harga
        ];

        $columnIndex = 'G';
        if ($this->tahapanId) {
            $widths[$columnIndex] = 20; // Pagu
        } else {
            foreach ($this->availableTahapans as $index => $tahapanId) {
                $widths[$columnIndex] = 20; // Pagu per tahapan
                $columnIndex++;
            }
        }

        return $widths;
    }

    public function title(): string
    {
        $title = 'Rekap Rekening Belanja Seluruh OPD';
        
        if ($this->tahapanId) {
            $tahapanName = $this->tahapansData->find($this->tahapanId)->name ?? 'Tahapan ' . $this->tahapanId;
            $title .= ' - ' . $tahapanName;
        }
        
        if ($this->keyword) {
            $title .= ' - Kata Kunci: "' . $this->keyword . '"';
        }
        
        $title .= ' - APBD 2025';
        
        return $title;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->rekap->count() + 10; // Tambahan untuk row total
                $lastColumn = $this->getLastColumn();
                
                // Tambahkan informasi filter
                $filterRow = $lastRow + 2;
                $event->sheet->setCellValue('A' . $filterRow, 'INFORMASI FILTER:');
                $event->sheet->getStyle('A' . $filterRow)->getFont()->setBold(true);
                
                $filterRow++;
                if ($this->tahapanId) {
                    $tahapanName = $this->tahapansData->find($this->tahapanId)->name ?? 'Tahapan ' . $this->tahapanId;
                    $event->sheet->setCellValue('A' . $filterRow, 'Tahapan: ' . $tahapanName);
                    $filterRow++;
                }
                
                if ($this->keyword) {
                    $event->sheet->setCellValue('A' . $filterRow, 'Kata Kunci: "' . $this->keyword . '"');
                    $filterRow++;
                }
                
                $event->sheet->setCellValue('A' . $filterRow, 'Total Data: ' . $this->rekap->count());
                $event->sheet->setCellValue('A' . ($filterRow + 1), 'Tanggal Export: ' . now()->format('d/m/Y H:i:s'));
            }
        ];
    }

    private function getLastColumn()
    {
        $baseColumns = 6; // No, Kode SKPD, Nama SKPD, Kode Rekening, Nama Rekening, Nama Standar Harga
        
        if ($this->tahapanId) {
            return chr(65 + $baseColumns); // G
        } else {
            return chr(65 + $baseColumns + count($this->availableTahapans) - 1);
        }
    }
}
