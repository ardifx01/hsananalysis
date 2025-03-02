<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataAnggaran;

class ProgressController extends Controller
{
    /**
     * Display a listing of the progress pergeseran.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tahapanTerbaru = DataAnggaran::max('tahapan_id');

        $data = DB::table('data_anggarans as da')
            ->leftJoin('rekening_penyesuaian as rp', 'da.kode_rekening', '=', 'rp.kode_rekening')
            ->leftJoin('opd_rekening_penyesuaian as opd_rp', function ($join) {
                $join->on('da.kode_rekening', '=', 'opd_rp.kode_rekening')
                     ->on('da.kode_skpd', '=', 'opd_rp.kode_opd');
            })
            ->select(
                'da.kode_skpd',
                'da.nama_skpd',
                DB::raw('SUM(da.pagu) as pagu_original'),

                // ✅ Langkah 1: Hitung nilai pengurangan langsung berdasarkan persentase di opd_rekening_penyesuaian atau rekening_penyesuaian
                DB::raw('
                    SUM(da.pagu * COALESCE(
                        (SELECT persentase_penyesuaian / 100 
                         FROM opd_rekening_penyesuaian 
                         WHERE opd_rekening_penyesuaian.kode_rekening = da.kode_rekening 
                         AND opd_rekening_penyesuaian.kode_opd = da.kode_skpd 
                         LIMIT 1),
                        rp.persentase_penyesuaian / 100,
                        0
                    )) as nilai_penyesuaian
                '),

                // ✅ Langkah 2: Hitung ulang persentase berdasarkan total nilai pengurangan
                DB::raw('
                    ROUND(
                        (SUM(da.pagu * COALESCE(
                            (SELECT persentase_penyesuaian / 100 
                             FROM opd_rekening_penyesuaian 
                             WHERE opd_rekening_penyesuaian.kode_rekening = da.kode_rekening 
                             AND opd_rekening_penyesuaian.kode_opd = da.kode_skpd 
                             LIMIT 1),
                            rp.persentase_penyesuaian / 100,
                            0
                        )) / SUM(da.pagu)) * 100, 2
                    ) as persentase_penyesuaian
                '),

                // ✅ Hitung pagu setelah penyesuaian
                DB::raw('
                    SUM(da.pagu) - SUM(da.pagu * COALESCE(
                        (SELECT persentase_penyesuaian / 100 
                         FROM opd_rekening_penyesuaian 
                         WHERE opd_rekening_penyesuaian.kode_rekening = da.kode_rekening 
                         AND opd_rekening_penyesuaian.kode_opd = da.kode_skpd 
                         LIMIT 1),
                        rp.persentase_penyesuaian / 100,
                        0
                    )) as pagu_setelah_penyesuaian
                '),

                // Tambahkan kolom pagu_tahapan_terbaru dari data_anggarans dengan tahapan_id terbaru
                DB::raw('
                    (SELECT SUM(pagu) 
                     FROM data_anggarans 
                     WHERE tahapan_id = "' . $tahapanTerbaru . '" 
                     AND kode_skpd = da.kode_skpd
                    ) as pagu_tahapan_terbaru
                '),

                // Tambahkan kolom untuk tanggal upload terbaru
                DB::raw('
                    (SELECT DATE_FORMAT(tanggal_upload, "%d-%m-%Y")
                     FROM data_anggarans
                     WHERE tahapan_id = "' . $tahapanTerbaru . '"
                     LIMIT 1
                    ) as tanggal_upload_terbaru
                ')
            )
            ->where('da.tahapan_id', '1')
            ->groupBy('da.kode_skpd', 'da.nama_skpd')
            ->orderBy('da.kode_skpd', 'asc')
            ->get();

        return view('progress.index', compact('data', 'tahapanTerbaru'));
    }

    public function progressPerOpdRek(Request $request)
{
    $tahapanTerbaru = DataAnggaran::max('tahapan_id');

    // Ambil daftar OPD
    $opds = DB::table('data_anggarans')
        ->select('kode_skpd', 'nama_skpd')
        ->distinct()
        ->orderBy('kode_skpd', 'asc')
        ->get();

    // Ambil data anggaran per OPD dan per rekening
    $query = DB::table('data_anggarans')
        ->leftJoin('opd_rekening_penyesuaian', function ($join) {
            $join->on('data_anggarans.kode_rekening', '=', 'opd_rekening_penyesuaian.kode_rekening')
                 ->on('data_anggarans.kode_skpd', '=', 'opd_rekening_penyesuaian.kode_opd');
        })
        ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
        ->select(
            'data_anggarans.kode_skpd',
            'data_anggarans.nama_skpd',
            'data_anggarans.kode_rekening',
            'data_anggarans.nama_rekening',
            DB::raw('SUM(CASE WHEN tahapan_id = 1 THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw("
                COALESCE(
                    (SELECT SUM(pagu) 
                     FROM data_anggarans AS da2
                     WHERE da2.kode_rekening = data_anggarans.kode_rekening
                     AND da2.kode_skpd = data_anggarans.kode_skpd
                     AND da2.tahapan_id = {$tahapanTerbaru}
                     GROUP BY da2.kode_rekening, da2.kode_skpd), 0
                ) as pagu_terbaru
            "),
            DB::raw('COALESCE(opd_rekening_penyesuaian.persentase_penyesuaian, rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian') // Mengutamakan data dari opd_rekening_penyesuaian jika ada
        )
        ->groupBy(
            'data_anggarans.kode_skpd', 
            'data_anggarans.nama_skpd', 
            'data_anggarans.kode_rekening', 
            'data_anggarans.nama_rekening', 
            'opd_rekening_penyesuaian.persentase_penyesuaian', 
            'rekening_penyesuaian.persentase_penyesuaian'
        )
        ->orderBy('data_anggarans.kode_rekening', 'asc');

    if ($request->filled('kode_opd')) {
        $query->where('data_anggarans.kode_skpd', $request->kode_opd);
    }

    $data = $query->get();

    return view('progress.opd-rek', compact('opds', 'data'))->with('kode_opd', $request->kode_opd);
}
}
