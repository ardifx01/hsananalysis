<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataAnggaran; // Pastikan model Opd sudah ada

class ProgressController extends Controller
{
    /**
     * Display a listing of the progress pergeseran.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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

            // ✅ Tambahkan kolom pagu_revisi dari data_anggarans dengan tipe_data revisi
            DB::raw('
                (SELECT SUM(pagu) 
                 FROM data_anggarans 
                 WHERE tipe_data = "revisi" 
                 AND kode_skpd = da.kode_skpd
                ) as pagu_revisi
            ')
        )
        ->where('da.tipe_data', 'original')
        ->groupBy('da.kode_skpd', 'da.nama_skpd')
        ->orderBy('da.kode_skpd', 'asc')
        ->get();

        return view('progress.index', compact('data'));
    }
}
