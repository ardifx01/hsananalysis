<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendapatan;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PendapatanImport;
use App\Models\Tahapan;
use Illuminate\Support\Facades\DB;

class DataPendapatanController extends Controller
{
    public function index()
    {
        $pendapatans = Pendapatan::select('tahapan_id', DB::raw('DATE(tanggal_upload) as tanggal_upload'), DB::raw('TIME(tanggal_upload) as jam_upload'), DB::raw('count(*) as jumlah'))
                                 ->groupBy('tahapan_id', 'tanggal_upload', 'jam_upload')
                                 ->get();

        $tahapans = Tahapan::all(); // Ambil data tahapan

        return view('data-pendapatan.index', compact('pendapatans', 'tahapans'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'tahapan_id' => 'required|exists:tahapan,id',
            'file' => 'required|mimes:xlsx,xls,csv',
            'tanggal_upload' => 'required|date_format:Y-m-d\TH:i', // Validasi tanggal dan waktu upload
        ]);

        Excel::import(new PendapatanImport($request->tahapan_id, $request->tanggal_upload), $request->file('file'));

        return redirect()->route('data-pendapatan.index')->with('success', 'Data pendapatan berhasil diupload.');
    }

    public function destroy($tahapan_id, $tanggal_upload, $jam_upload)
    {
        Pendapatan::where('tahapan_id', $tahapan_id)
                  ->whereDate('tanggal_upload', $tanggal_upload)
                  ->whereTime('tanggal_upload', $jam_upload)
                  ->delete();

        return redirect()->route('data-pendapatan.index')->with('success', 'Data pendapatan berhasil dihapus.');
    }
}
