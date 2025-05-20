<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    public function getData(Request $request)
    {
        $query = DB::table('data_anggarans')
            ->select(
                'kode_skpd',
                'nama_skpd',
                DB::raw('GROUP_CONCAT(DISTINCT nama_rekening SEPARATOR ", ") as nama_rekening'), // Gabungkan nama rekening
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'), // Total pagu original
                DB::raw('SUM(CASE WHEN tipe_data = "revisi" THEN pagu ELSE 0 END) as pagu_revisi') // Total pagu revisi
            )
            ->groupBy('kode_skpd', 'nama_skpd'); // Group hanya berdasarkan OPD

        // Filter berdasarkan input form
        if ($request->filled('nama_rekening')) {
            $query->where('nama_rekening', 'like', "%{$request->nama_rekening}%");
        }
        if ($request->filled('kode_skpd')) {
            $query->where('kode_skpd', 'like', "%{$request->kode_skpd}%");
        }
        if ($request->filled('nama_skpd')) {
            $query->where('nama_skpd', 'like', "%{$request->nama_skpd}%");
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function getDataDashboard(Request $request)
    {
        // Query untuk data OPD
        $query = DB::table('data_anggarans as da')
            ->join('tahapans as t', 'da.tahapan_id', '=', 't.id')
            ->select(
                'da.kode_skpd',
                'da.nama_skpd',
                DB::raw('SUM(CASE WHEN da.tipe_data = "original" THEN da.pagu ELSE 0 END) as anggaran'),
                DB::raw('SUM(CASE WHEN da.tipe_data = "revisi" THEN da.pagu ELSE 0 END) as realisasi')
            )
            ->groupBy('da.kode_skpd', 'da.nama_skpd');

        // Filter berdasarkan tahapan
        if ($request->filled('tahapan')) {
            $query->where('da.tahapan_id', $request->tahapan);
        }

        // Filter berdasarkan OPD
        if ($request->filled('opd')) {
            $query->where('da.kode_skpd', $request->opd);
        }

        $opdData = $query->get()->map(function ($item) {
            $sisa = $item->anggaran - $item->realisasi;
            $persentase = $item->anggaran > 0 ? ($item->realisasi / $item->anggaran * 100) : 0;
            
            return [
                'nama_skpd' => $item->nama_skpd,
                'anggaran' => $item->anggaran,
                'realisasi' => $item->realisasi,
                'sisa' => $sisa,
                'persentase' => $persentase
            ];
        });

        // Hitung total
        $totalAnggaran = $opdData->sum('anggaran');
        $totalRealisasi = $opdData->sum('realisasi');
        $sisaAnggaran = $totalAnggaran - $totalRealisasi;
        $persentaseRealisasi = $totalAnggaran > 0 ? ($totalRealisasi / $totalAnggaran * 100) : 0;

        return response()->json([
            'data' => [
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $sisaAnggaran,
                'persentase_realisasi' => $persentaseRealisasi,
                'opd_data' => $opdData
            ]
        ]);
    }

    public function rekapRekening()
    {
        $data = DB::table('data_anggarans')
            ->select(
                'kode_rekening',
                'nama_rekening',
                DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
                DB::raw('SUM(CASE WHEN tipe_data = "revisi" THEN pagu ELSE 0 END) as pagu_revisi')
            )
            ->groupBy('kode_rekening', 'nama_rekening')
            ->orderBy('kode_rekening', 'asc')
            ->get();
    
        return view('report.rekap-rekening', compact('data'));
    }


}
