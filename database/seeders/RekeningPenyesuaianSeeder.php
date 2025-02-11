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
        // Baca file JSON
        $json = file_get_contents(database_path('seeders/rekening_penyesuaian.json'));
        $data = json_decode($json, true);

        // Masukkan data ke dalam database
        DB::table('rekening_penyesuaian')->insert($data);
    }
}
