<?php

namespace App\Imports;

use App\Models\KodeRekening;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KodeRekeningImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Check if record with this kode_rekening already exists
        $existing = KodeRekening::where('kode_rekening', $row['kode'])->first();
        
        if ($existing) {
            // Update the existing record
            $existing->update([
                'uraian' => $row['uraian']
            ]);
            
            return null; // Return null as we're updating, not creating
        }
        
        // Create a new record if it doesn't exist
        return new KodeRekening([
            'kode_rekening' => $row['kode'],
            'uraian' => $row['uraian'],
        ]);
    }
}
