<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RekeningPenyesuaianSeeder extends Seeder
{
    public function run()
    {
        $filePath = storage_path('app/public/PERSENTASE.xlsx'); // Sesuaikan path jika perlu

        if (!file_exists($filePath)) {
            $this->command->error("File Excel tidak ditemukan: $filePath");
            return;
        }

        // Load file Excel
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Mulai dari baris kedua (baris pertama adalah header)
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            DB::table('rekening_penyesuaian')->updateOrInsert([
                'kode_rekening' => trim($row[0]),
            ], [
                'nama_rekening' => trim($row[1]),
                'persentase_penyesuaian' => (float) $row[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeder Rekening Penyesuaian berhasil dijalankan!');
    }
}
