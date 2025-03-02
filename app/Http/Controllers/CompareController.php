<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use App\Models\Tahapan;
use Illuminate\Support\Facades\DB;


class CompareController extends Controller
{
    public function compareOpd()
{
    // Ambil data rekap dari database
    $rekap = DataAnggaran::select(
            'kode_skpd', 
            'nama_skpd', 
            'tahapan_id', 
            DB::raw('DATE(tanggal_upload) as tanggal_upload'), 
            DB::raw('TIME(tanggal_upload) as jam_upload'), 
            DB::raw('SUM(pagu) as total_pagu')
        )
        ->groupBy('kode_skpd', 'nama_skpd', 'tahapan_id', 'tanggal_upload', 'jam_upload')
        ->get()
        ->groupBy('kode_skpd');

    // Ambil data tahapan dari database
    $tahapans = Tahapan::all();

    // Hitung total pagu untuk setiap kombinasi tahapan_id, tanggal_upload, dan jam_upload
    $totalPagu = [];
    $selisihPagu = [];
    $persentaseSelisihPagu = [];
    $totalSelisihPagu = 0;
    $totalPaguTahapanPertama = 0;
    $totalPaguTahapanTerakhir = 0;

    foreach ($rekap as $kode_skpd => $items) {
        $firstItem = $items->first();
        $lastItem = $items->last();
        $selisihPagu[$kode_skpd] = $lastItem->total_pagu - $firstItem->total_pagu;
        $totalSelisihPagu += $selisihPagu[$kode_skpd];

        // Hitung persentase selisih
        if ($firstItem->total_pagu != 0) {
            $persentaseSelisihPagu[$kode_skpd] = ($selisihPagu[$kode_skpd] / $firstItem->total_pagu) * 100;
        } else {
            $persentaseSelisihPagu[$kode_skpd] = 0;
        }

        $totalPaguTahapanPertama += $firstItem->total_pagu;
        $totalPaguTahapanTerakhir += $lastItem->total_pagu;

        foreach ($items as $item) {
            $key = $item->tahapan_id . '_' . str_replace('-', '_', $item->tanggal_upload) . '_' . str_replace(':', '_', $item->jam_upload);
            if (!isset($totalPagu[$key])) {
                $totalPagu[$key] = 0;
            }
            $totalPagu[$key] += $item->total_pagu;
        }
    }

    // Hitung total persentase selisih
    $totalPersentaseSelisihPagu = 0;
    if ($totalPaguTahapanPertama != 0) {
        $totalPersentaseSelisihPagu = ($totalSelisihPagu / $totalPaguTahapanPertama) * 100;
    }

    return view('compare.compare_opd', compact('rekap', 'tahapans', 'totalPagu', 'selisihPagu', 'persentaseSelisihPagu', 'totalSelisihPagu', 'totalPersentaseSelisihPagu'));
}


public function compareDataRek()
{
    // Ambil data rekap dari database
    $rekap = DataAnggaran::select(
            'kode_rekening', 
            'nama_rekening', 
            'tahapan_id', 
            DB::raw('DATE(tanggal_upload) as tanggal_upload'), 
            DB::raw('TIME(tanggal_upload) as jam_upload'), 
            DB::raw('SUM(pagu) as total_pagu')
        )
        ->groupBy('kode_rekening', 'nama_rekening', 'tahapan_id', 'tanggal_upload', 'jam_upload')
        ->get()
        ->groupBy('kode_rekening');

    // Ambil data tahapan dari database
    $tahapans = Tahapan::all();

    // Hitung total pagu untuk setiap kombinasi tahapan_id, tanggal_upload, dan jam_upload
    $totalPagu = [];
    $selisihPagu = [];
    $persentaseSelisihPagu = [];
    $totalSelisihPagu = 0;
    $totalPaguTahapanPertama = 0;
    $totalPaguTahapanTerakhir = 0;

    foreach ($rekap as $kode_rekening => $items) {
        $firstItem = $items->first();
        $lastItem = $items->last();
        $selisihPagu[$kode_rekening] = $lastItem->total_pagu - $firstItem->total_pagu;
        $totalSelisihPagu += $selisihPagu[$kode_rekening];

        // Hitung persentase selisih
        if ($firstItem->total_pagu != 0) {
            $persentaseSelisihPagu[$kode_rekening] = ($selisihPagu[$kode_rekening] / $firstItem->total_pagu) * 100;
        } else {
            $persentaseSelisihPagu[$kode_rekening] = 0;
        }

        $totalPaguTahapanPertama += $firstItem->total_pagu;
        $totalPaguTahapanTerakhir += $lastItem->total_pagu;

        foreach ($items as $item) {
            $key = $item->tahapan_id . '_' . str_replace('-', '_', $item->tanggal_upload) . '_' . str_replace(':', '_', $item->jam_upload);
            if (!isset($totalPagu[$key])) {
                $totalPagu[$key] = 0;
            }
            $totalPagu[$key] += $item->total_pagu;
        }
    }

    // Hitung total persentase selisih
    $totalPersentaseSelisihPagu = 0;
    if ($totalPaguTahapanPertama != 0) {
        $totalPersentaseSelisihPagu = ($totalSelisihPagu / $totalPaguTahapanPertama) * 100;
    }

    return view('compare.compare_rek', compact('rekap', 'tahapans', 'totalPagu', 'selisihPagu', 'persentaseSelisihPagu', 'totalSelisihPagu', 'totalPersentaseSelisihPagu'));
}


public function compareDataOpdRek(Request $request)
    {
        // Ambil daftar SKPD untuk dropdown
        $skpds = DataAnggaran::select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('kode_skpd')
            ->get();

        // Ambil filter SKPD dari request
        $kodeSkpd = $request->input('kode_skpd');

        // Jika tidak ada filter SKPD, kirimkan view tanpa data
        if (empty($kodeSkpd)) {
            return view('compare.compare_opd_rek', [
                'rekap' => collect(),
                'tahapans' => Tahapan::all(),
                'totalPagu' => [],
                'selisihPagu' => [],
                'persentaseSelisihPagu' => [],
                'totalSelisihPagu' => 0,
                'totalPersentaseSelisihPagu' => 0,
                'skpds' => $skpds,
                'kodeSkpd' => $kodeSkpd
            ]);
        }

        // Query data berdasarkan kode rekening
        $query = DataAnggaran::select(
            'kode_skpd',
            'nama_skpd',
            'kode_rekening',
            'nama_rekening',
            'tahapan_id',
            DB::raw('DATE(tanggal_upload) as tanggal_upload'),
            DB::raw('TIME(tanggal_upload) as jam_upload'),
            DB::raw('SUM(pagu) as total_pagu')
        )
        ->groupBy('kode_skpd', 'nama_skpd', 'kode_rekening', 'nama_rekening', 'tahapan_id', 'tanggal_upload', 'jam_upload')
        ->orderBy('tahapan_id')
        ->orderBy('tanggal_upload')
        ->orderBy('jam_upload');

        // Jika ada filter SKPD, tambahkan kondisi
        if (!empty($kodeSkpd)) {
            $query->where('kode_skpd', $kodeSkpd);
        }

        $rekap = $query->get()->groupBy('kode_rekening');

        // Pastikan $rekap tidak null
        if ($rekap->isEmpty()) {
            $rekap = collect();
        }

        // Ambil data tahapan dari database
        $tahapans = Tahapan::all();

        // Hitung total pagu untuk setiap kombinasi tahapan_id, tanggal_upload, dan jam_upload
        $totalPagu = [];
        $selisihPagu = [];
        $persentaseSelisihPagu = [];
        $totalSelisihPagu = 0;
        $totalPaguTahapanPertama = 0;
        $totalPaguTahapanTerakhir = 0;

        foreach ($rekap as $kode_rekening => $items) {
            if ($items) {
                $firstItem = $items->first();
                $lastItem = $items->last();
                $selisihPagu[$kode_rekening] = $lastItem->total_pagu - $firstItem->total_pagu;
                $totalSelisihPagu += $selisihPagu[$kode_rekening];

                // Hitung persentase selisih
                if ($firstItem->total_pagu != 0) {
                    $persentaseSelisihPagu[$kode_rekening] = ($selisihPagu[$kode_rekening] / $firstItem->total_pagu) * 100;
                } else {
                    $persentaseSelisihPagu[$kode_rekening] = 0;
                }

                $totalPaguTahapanPertama += $firstItem->total_pagu;
                $totalPaguTahapanTerakhir += $lastItem->total_pagu;

                foreach ($items as $item) {
                    $key = $item->tahapan_id . '_' . str_replace('-', '_', $item->tanggal_upload) . '_' . str_replace(':', '_', $item->jam_upload);
                    if (!isset($totalPagu[$key])) {
                        $totalPagu[$key] = 0;
                    }
                    $totalPagu[$key] += $item->total_pagu;
                }
            }
        }

        // Hitung total persentase selisih
        $totalPersentaseSelisihPagu = 0;
        if ($totalPaguTahapanPertama != 0) {
            $totalPersentaseSelisihPagu = ($totalSelisihPagu / $totalPaguTahapanPertama) * 100;
        }

        return view('compare.compare_opd_rek', compact('rekap', 'tahapans', 'totalPagu', 'selisihPagu', 'persentaseSelisihPagu', 'totalSelisihPagu', 'totalPersentaseSelisihPagu', 'skpds', 'kodeSkpd'));
    }


public function comparePerSubKegiatan(Request $request)
    {
        // Ambil daftar OPD untuk dropdown, diurutkan berdasarkan kode OPD
        $opds = DataAnggaran::select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('kode_skpd') // Urutkan berdasarkan kode OPD
            ->get();

        // Ambil filter OPD dari request
        $kodeOpd = $request->input('kode_opd');

        // Query data berdasarkan kode sub kegiatan
        $query = DataAnggaran::select(
            'kode_sub_kegiatan',
            'nama_sub_kegiatan',
            'kode_skpd as kode_opd',
            'nama_skpd as nama_opd',
            DB::raw('SUM(CASE WHEN tahapan_id = 1 THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw('SUM(CASE WHEN tahapan_id = 2 THEN pagu ELSE 0 END) as pagu_revisi')
        )
        ->groupBy('kode_sub_kegiatan', 'nama_sub_kegiatan', 'kode_skpd', 'nama_skpd')
        ->orderBy('kode_sub_kegiatan'); // Urutkan hasil berdasarkan kode sub kegiatan

        // Jika ada filter OPD, tambahkan kondisi
        if (!empty($kodeOpd)) {
            $query->where('kode_skpd', $kodeOpd);
        }

        $rekap = $query->get();

        // Hitung selisih dan persentase perubahan
        foreach ($rekap as $item) {
            $item->selisih = $item->pagu_revisi - $item->pagu_original;
            $item->persentase = $item->pagu_original > 0 ? ($item->selisih / $item->pagu_original) * 100 : 0;
        }

        return view('compare.compare-sub-kegiatan', compact('rekap', 'opds'));
    }
}