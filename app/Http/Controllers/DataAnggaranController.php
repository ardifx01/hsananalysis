<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataAnggaranImport;
use Illuminate\Support\Facades\DB;

class DataAnggaranController extends Controller
{
    public function index()
    {
        $data = DataAnggaran::orderBy('kode_skpd')->get();
        return view('data.index', compact('data'));
    }

    public function importPage()
{
    $totalOriginal = DataAnggaran::where('tipe_data', 'original')->count();
    $totalRevisi = DataAnggaran::where('tipe_data', 'revisi')->count();

    return view('data.index', compact('totalOriginal', 'totalRevisi'));
}

    public function importData(Request $request)
    {
        $tipe = $request->input('tipe_data'); // 'original' atau 'revisi'

        if ($tipe === 'revisi') {
            DataAnggaran::where('tipe_data', 'revisi')->delete();
        }

        Excel::import(new DataAnggaranImport($tipe), $request->file('file'));

        return back()->with('success', 'Data berhasil diunggah!');
    }

    public function compareData()
{
    $original = DataAnggaran::where('tipe_data', 'original')->get();
    $revisi = DataAnggaran::where('tipe_data', 'revisi')->get();

    $rekap = [];

    foreach ($original as $ori) {
        $key = $ori->kode_skpd . '-' . $ori->kode_rekening;

        if (!isset($rekap[$key])) {
            $rekap[$key] = [
                'kode_opd' => $ori->kode_skpd,
                'nama_opd' => $ori->nama_skpd,
                'kode_rekening' => $ori->kode_rekening,
                'nama_rekening' => $ori->nama_rekening,
                'pagu_original' => 0,
                'pagu_revisi' => 0,
            ];
        }
        $rekap[$key]['pagu_original'] += $ori->pagu;
    }

    foreach ($revisi as $rev) {
        $key = $rev->kode_skpd . '-' . $rev->kode_rekening;

        if (!isset($rekap[$key])) {
            $rekap[$key] = [
                'kode_opd' => $rev->kode_skpd,
                'nama_opd' => $rev->nama_skpd,
                'kode_rekening' => $rev->kode_rekening,
                'nama_rekening' => $rev->nama_rekening,
                'pagu_original' => 0,
                'pagu_revisi' => 0,
            ];
        }
        $rekap[$key]['pagu_revisi'] += $rev->pagu;
    }

    // Hitung selisih
    foreach ($rekap as &$item) {
        $item['selisih'] = $item['pagu_revisi'] - $item['pagu_original'];
    }

    // Urutkan berdasarkan Kode OPD, lalu Kode Rekening
    usort($rekap, function ($a, $b) {
        return $a['kode_opd'] <=> $b['kode_opd'] ?: $a['kode_rekening'] <=> $b['kode_rekening'];
    });

    return view('data.compare', compact('rekap'));
}

public function compareDataRek()
{
    $rekap = DataAnggaran::select(
        'kode_rekening',
        'nama_rekening',
        \DB::raw("SUM(CASE WHEN tipe_data = 'original' THEN pagu ELSE 0 END) as pagu_original"),
        \DB::raw("SUM(CASE WHEN tipe_data = 'revisi' THEN pagu ELSE 0 END) as pagu_revisi")
    )
    ->groupBy('kode_rekening', 'nama_rekening')
    ->orderBy('kode_rekening')
    ->get();

// Hitung selisih pagu
foreach ($rekap as $item) {
    $item->selisih = $item->pagu_revisi - $item->pagu_original;
}

return view('data.compare-rek', compact('rekap'));
}

public function comparePerOpd()
{
    $rekap = DataAnggaran::select(
            'kode_skpd',
            'nama_skpd',
            DB::raw("SUM(CASE WHEN tipe_data = 'original' THEN pagu ELSE 0 END) as pagu_original"),
            DB::raw("SUM(CASE WHEN tipe_data = 'revisi' THEN pagu ELSE 0 END) as pagu_revisi")
        )
        ->groupBy('kode_skpd', 'nama_skpd')
        ->orderBy('kode_skpd')
        ->get();

    // Hitung selisih pagu
    foreach ($rekap as $item) {
        $item->selisih = $item->pagu_revisi - $item->pagu_original;
    }

    return view('data.compare_opd', compact('rekap'));
}


public function hapusRevisi()
{
    $deleted = DataAnggaran::where('tipe_data', 'revisi')->delete();

    if ($deleted) {
        return redirect()->back()->with('success', 'Semua data revisi berhasil dihapus.');
    } else {
        return redirect()->back()->with('error', 'Tidak ada data revisi yang ditemukan.');
    }
}

public function hapusOriginal()
{
    $deleted = DataAnggaran::where('tipe_data', 'original')->delete();

    if ($deleted) {
        return redirect()->back()->with('success', 'Semua data original berhasil dihapus.');
    } else {
        return redirect()->back()->with('error', 'Tidak ada data original yang ditemukan.');
    }
}
}