<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\RekapPerOpdExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapPerOpdController extends Controller
{
    public function exportExcel(Request $request)
    {
        return Excel::download(new RekapPerOpdExport($request->kode_opd), 'rekap_per_opd.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $kode_opd = $request->kode_opd;

        $data = DB::table('data_anggarans')
            ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
            ->select(
                'data_anggarans.kode_skpd',
                'data_anggarans.nama_skpd',
                'data_anggarans.kode_rekening',
                'data_anggarans.nama_rekening',
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
                DB::raw('COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian'),
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) * (COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) / 100) as nilai_penyesuaian'),
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) - (SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) * (COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) / 100)) as pagu_setelah_penyesuaian')
            )
            ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'rekening_penyesuaian.persentase_penyesuaian')
            ->orderBy('data_anggarans.kode_rekening', 'asc');

        if ($kode_opd) {
            $data->where('data_anggarans.kode_skpd', $kode_opd);
        }

        $pdf = Pdf::loadView('exports.rekap_per_opd_pdf', ['data' => $data->get()]);
        return $pdf->download('rekap_per_opd.pdf');
    }

    public function rekapRekeningView(Request $request)
{
    $kode_opd = $request->kode_opd;

    // Ambil semua OPD untuk filter dropdown
    $opds = DB::table('data_anggarans')
        ->select('kode_skpd', 'nama_skpd')
        ->distinct()
        ->orderBy('kode_skpd', 'asc')
        ->get();

    // Ambil data berdasarkan filter OPD
    $query = DB::table('data_anggarans')
        ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
        ->select(
            'data_anggarans.kode_rekening',
            'data_anggarans.nama_rekening',
            'data_anggarans.nama_skpd',
            DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw('COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian')
        )
        ->groupBy('data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'data_anggarans.nama_skpd', 'rekening_penyesuaian.persentase_penyesuaian')
        ->orderBy('data_anggarans.kode_rekening', 'asc');

    if (!empty($kode_opd)) {
        $query->where('data_anggarans.kode_skpd', $kode_opd);
    }

    $data = $query->get();

    // Pastikan OPD yang dipilih bisa ditemukan
    $selected_opd = $opds->where('kode_opd', $kode_opd)->first();

    return view('simulasi.rekap', compact('opds', 'data', 'selected_opd'));
}

}
