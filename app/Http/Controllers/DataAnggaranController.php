<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataAnggaranImport;
use App\Models\Tahapan;
use Illuminate\Support\Facades\DB;

class DataAnggaranController extends Controller
{
    

// CONTROLLER BARU

public function index()
    {
        $dataAnggarans = DataAnggaran::select('tahapan_id', DB::raw('DATE(tanggal_upload) as tanggal_upload'), DB::raw('TIME(tanggal_upload) as jam_upload'), DB::raw('count(*) as jumlah'))
                                      ->groupBy('tahapan_id', 'tanggal_upload', 'jam_upload')
                                      ->get();

        $tahapans = Tahapan::all(); // Ambil data tahapan

        return view('database.index', compact('dataAnggarans', 'tahapans'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'tahapan_id' => 'required|exists:tahapan,id',
            'file' => 'required|mimes:xlsx,xls',
            'tanggal_upload' => 'required|date_format:Y-m-d\TH:i', // Validasi tanggal dan waktu upload
        ]);

        Excel::import(new DataAnggaranImport($request->tahapan_id, $request->tanggal_upload), $request->file('file'));

        return redirect()->route('data')->with('success', 'Data anggaran berhasil diupload.');
    }

    public function destroy($tahapan_id, $tanggal_upload, $jam_upload)
    {
        DataAnggaran::where('tahapan_id', $tahapan_id)
                    ->whereDate('tanggal_upload', $tanggal_upload)
                    ->whereTime('tanggal_upload', $jam_upload)
                    ->delete();

        return redirect()->route('data')->with('success', 'Data anggaran berhasil dihapus.');
    }


    

// CONTROLLER BARU



    

    
   

    
}