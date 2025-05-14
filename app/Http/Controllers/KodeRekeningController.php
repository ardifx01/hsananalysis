<?php

namespace App\Http\Controllers;

use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KodeRekeningImport;
use Yajra\DataTables\Facades\DataTables;

class KodeRekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kodeRekenings = KodeRekening::orderBy('kode_rekening', 'asc')->get();
        return view('kode-rekening.index', compact('kodeRekenings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kode-rekening.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_rekening' => 'required|string|unique:kode_rekenings',
            'uraian' => 'required|string'
        ]);

        KodeRekening::create($request->all());

        return redirect()->route('kode-rekening.index')
            ->with('success', 'Kode Rekening berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KodeRekening $kodeRekening)
    {
        return view('kode-rekening.show', compact('kodeRekening'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KodeRekening $kodeRekening)
    {
        return view('kode-rekening.edit', compact('kodeRekening'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KodeRekening $kodeRekening)
    {
        $request->validate([
            'kode_rekening' => 'required|string|unique:kode_rekenings,kode_rekening,' . $kodeRekening->id,
            'uraian' => 'required|string'
        ]);

        $kodeRekening->update($request->all());

        return redirect()->route('kode-rekening.index')
            ->with('success', 'Kode Rekening berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KodeRekening $kodeRekening)
    {
        $kodeRekening->delete();

        return redirect()->route('kode-rekening.index')
            ->with('success', 'Kode Rekening berhasil dihapus.');
    }

    /**
     * Import kode rekening from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        
        try {
            Excel::import(new KodeRekeningImport, $request->file('file'));
            
            return redirect()->route('kode-rekening.index')
                ->with('success', 'Data Kode Rekening berhasil diimpor dari Excel.');
        } catch (\Exception $e) {
            return redirect()->route('kode-rekening.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Download sample Excel template for import.
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/kode_rekening_template.xlsx');
        
        // If template doesn't exist, create it
        if (!file_exists($filePath)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Add headers
            $sheet->setCellValue('A1', 'kode');
            $sheet->setCellValue('B1', 'uraian');
            
            // Add sample data
            $sheet->setCellValue('A2', '5.1.01.01');
            $sheet->setCellValue('B2', 'Belanja Gaji dan Tunjangan ASN');
            
            $sheet->setCellValue('A3', '5.1.01.02');
            $sheet->setCellValue('B3', 'Tambahan Penghasilan berdasarkan Beban Kerja ASN');
            
            // Create directory if it doesn't exist
            if (!file_exists(public_path('templates'))) {
                mkdir(public_path('templates'), 0755, true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
        }
        
        return response()->download($filePath, 'kode_rekening_template.xlsx');
    }
}
