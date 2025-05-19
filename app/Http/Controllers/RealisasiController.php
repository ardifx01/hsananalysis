<?php

namespace App\Http\Controllers;

use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class RealisasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Realisasi::query();

        // Only show data if both filters are selected
        if ($request->has('periode') && $request->has('kode_opd')) {
            // Filter by periode
            $query->whereYear('periode', substr($request->periode, 0, 4))
                  ->whereMonth('periode', substr($request->periode, 5, 2));

            // Filter by kode_opd
            $query->where('kode_opd', $request->kode_opd);

            // Filter kode_rekening with 6 segments (x.x.xx.xx.xx.xxxx)
            $query->whereRaw("kode_rekening REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$'");

            $realisasis = $query->orderBy('kode_rekening')
                               ->get();
        } else {
            $realisasis = collect(); // Empty collection when filters are not selected
        }

        // Get unique OPDs from data_anggaran table
        $opds = DB::table('data_anggarans')
                  ->select('kode_skpd', 'nama_skpd')
                  ->distinct()
                  ->orderBy('kode_skpd')
                  ->get();

        // Get unique periods for filter dropdown (grouped by year and month)
        $periods = Realisasi::selectRaw('DATE_FORMAT(periode, "%Y-%m") as periode')
                          ->distinct()
                          ->orderBy('periode', 'desc')
                          ->pluck('periode');

        return view('realisasi.index', compact('realisasis', 'opds', 'periods'));
    }

    public function create()
    {
        return view('realisasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_opd' => 'required|string',
            'periode' => 'required|date',
            'kode_rekening' => 'required|string',
            'uraian' => 'required|string',
            'anggaran' => 'required|numeric',
            'realisasi' => 'required|numeric',
            'persentase' => 'required|numeric',
            'realisasi_ly' => 'required|numeric',
        ]);

        Realisasi::create($validated);

        return redirect()->route('realisasi.index')
                        ->with('success', 'Data realisasi berhasil ditambahkan');
    }

    public function edit(Realisasi $realisasi)
    {
        return view('realisasi.edit', compact('realisasi'));
    }

    public function update(Request $request, Realisasi $realisasi)
    {
        $validated = $request->validate([
            'kode_opd' => 'required|string',
            'periode' => 'required|date',
            'kode_rekening' => 'required|string',
            'uraian' => 'required|string',
            'anggaran' => 'required|numeric',
            'realisasi' => 'required|numeric',
            'persentase' => 'required|numeric',
            'realisasi_ly' => 'required|numeric',
        ]);

        $realisasi->update($validated);

        return redirect()->route('realisasi.index')
                        ->with('success', 'Data realisasi berhasil diperbarui');
    }

    public function destroy(Realisasi $realisasi)
    {
        $realisasi->delete();

        return redirect()->route('realisasi.index')
                        ->with('success', 'Data realisasi berhasil dihapus');
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_opd' => 'required|string',
            'periode' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $import = Excel::toArray([], $file)[0];
            
            // Skip header row
            $rows = array_slice($import, 1);
            
            $data = [];
            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty rows
                
                $periode = $request->periode;
                
                $data[] = [
                    'kode_opd' => $request->kode_opd,
                    'periode' => $periode,
                    'kode_rekening' => $row[0], // kode rekening
                    'uraian' => $row[1], // uraian
                    'anggaran' => $this->parseNumber($row[2]), // anggaran
                    'realisasi' => $this->parseNumber($row[3]), // realisasi
                    'persentase' => $this->parseNumber($row[4]), // persentase
                    'realisasi_ly' => $this->parseNumber($row[5]), // realisasi ly
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert data in chunks to avoid memory issues
            foreach (array_chunk($data, 100) as $chunk) {
                Realisasi::insert($chunk);
            }

            return redirect()->route('realisasi.index')
                ->with('success', 'Data berhasil diupload');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function parseNumber($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        
        // Remove any non-numeric characters except decimal point and minus sign
        $value = preg_replace('/[^0-9.,-]/', '', $value);
        
        // Replace comma with dot for decimal point
        $value = str_replace(',', '.', $value);
        
        // Remove any thousand separators
        $value = str_replace('.', '', $value);
        
        return (float) $value;
    }

    public function bulkDelete(Request $request)
    {
        $query = Realisasi::query();

        // Delete by selected IDs if provided
        if ($request->has('selected_ids')) {
            $request->validate([
                'selected_ids' => 'required|array',
                'selected_ids.*' => 'exists:realisasis,id'
            ]);
            $query->whereIn('id', $request->selected_ids);
        }

        // Delete by kode_opd if provided
        if ($request->has('kode_opd') && !empty($request->kode_opd)) {
            $query->where('kode_opd', $request->kode_opd);
        }

        // Delete by periode if provided
        if ($request->has('periode') && !empty($request->periode)) {
            $query->whereYear('periode', substr($request->periode, 0, 4))
                  ->whereMonth('periode', substr($request->periode, 5, 2));
        }

        // Get count before deletion for message
        $count = $query->count();

        // Perform deletion
        $query->delete();

        return redirect()->route('realisasi.index')
                        ->with('success', $count . ' data berhasil dihapus');
    }
} 