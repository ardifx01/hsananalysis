<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use App\Models\Tahapan;
use App\Models\KodeRekening;
use App\Models\SimulasiPenyesuaianAnggaran;
use App\Models\Realisasi;
use App\Exports\RekapitulasiStrukturOpdExport;
use Maatwebsite\Excel\Facades\Excel;

class SimulasiPerubahanController extends Controller
{
    public function index(Request $request)
    {
        $tahapans = Tahapan::all();
        $tahapanId = $request->input('tahapan_id');

        // Ambil daftar SKPD unik (kode_skpd & nama_skpd), urut berdasarkan kode_skpd
        $skpds = DataAnggaran::select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('kode_skpd')
            ->get();

        // Ambil kode SKPD dari request (tidak ada default)
        $skpdKode = $request->input('skpd');

        // Ambil objek SKPD terpilih
        $skpdTerpilih = $skpds->where('kode_skpd', $skpdKode)->first();
        // Ambil objek tahapan terpilih
        $tahapanTerpilih = $tahapans->where('id', $tahapanId)->first();

        $rekap = collect();
        if ($tahapanId && $skpdKode) {
            $rekap = DataAnggaran::select('kode_rekening', 'nama_rekening')
                ->selectRaw('SUM(pagu) as total_pagu')
                ->where('tahapan_id', $tahapanId)
                ->where('kode_skpd', $skpdKode)
                ->groupBy('kode_rekening', 'nama_rekening')
                ->orderBy('kode_rekening')
                ->get();
        }

        // Ambil semua kode rekening yang diawali angka 5 dan hanya 2 atau 3 segmen (misal: 5.1 dan 5.1.01)
        $kodeRekenings = KodeRekening::where(function($q) {
            $q->whereRaw("kode_rekening REGEXP '^5\\.[0-9]+$'") // 2 segmen, contoh: 5.1
              ->orWhereRaw("kode_rekening REGEXP '^5\\.[0-9]+\\.[0-9]{2}$'"); // 3 segmen, contoh: 5.1.01
        })
        ->orderBy('kode_rekening')
        ->get();

        // Ambil semua data simulasi penyesuaian anggaran HANYA untuk OPD aktif
        $simulasiPenyesuaian = collect();
        if ($skpdKode) {
            $simulasiPenyesuaian = SimulasiPenyesuaianAnggaran::where('kode_opd', $skpdKode)
                ->orderBy('id', 'desc')
                ->get();
        }

        // Ambil data realisasi untuk SKPD dan mapping berdasarkan kode rekening
        $realisasiMap = [];
        $realisasiSegmenMap = [];
        if ($skpdKode) {
            $realisasiRows = Realisasi::where('kode_opd', $skpdKode)->get();
            foreach ($realisasiRows as $row) {
                $realisasiMap[$row->kode_rekening] = $row->realisasi;
                $segments = explode('.', $row->kode_rekening);
                // Hanya rekap realisasi dari kode rekening dengan 6 segmen
                if (count($segments) === 6) {
                    $seg2 = $segments[0] . '.' . $segments[1];
                    $realisasiSegmenMap[$seg2] = ($realisasiSegmenMap[$seg2] ?? 0) + $row->realisasi;
                    $seg3 = $segments[0] . '.' . $segments[1] . '.' . $segments[2];
                    $realisasiSegmenMap[$seg3] = ($realisasiSegmenMap[$seg3] ?? 0) + $row->realisasi;
                }
            }
        }

        return view('simulasi-perubahan.index', [
            'tahapans' => $tahapans,
            'tahapanId' => $tahapanId,
            'rekap' => $rekap,
            'skpds' => $skpds,
            'skpdKode' => $skpdKode,
            'skpdTerpilih' => $skpdTerpilih,
            'tahapanTerpilih' => $tahapanTerpilih,
            'kodeRekenings' => $kodeRekenings,
            'simulasiPenyesuaian' => $simulasiPenyesuaian,
            'realisasiMap' => $realisasiMap,
            'realisasiSegmenMap' => $realisasiSegmenMap,
        ]);
    }

    public function simulasiBelanjaOpd(Request $request)
    {
        $tahapans = Tahapan::all();
        $tahapanId = $request->input('tahapan_id');

        // Ambil rekap pagu per OPD
        $rekapOpd = collect();
        $simulasiPenyesuaian = collect();
        if ($tahapanId) {
            $rekapOpd = DataAnggaran::select('kode_skpd', 'nama_skpd')
                ->selectRaw('SUM(pagu) as total_pagu')
                ->where('tahapan_id', $tahapanId)
                ->groupBy('kode_skpd', 'nama_skpd')
                ->orderBy('kode_skpd')
                ->get();

            // Ambil semua penyesuaian untuk seluruh OPD
            $simulasiPenyesuaian = SimulasiPenyesuaianAnggaran::all();

            // Ambil total realisasi per OPD (hanya kode rekening 6 segmen)
            $realisasiPerOpd = \App\Models\Realisasi::select('kode_opd')
                ->whereRaw('LENGTH(kode_rekening) - LENGTH(REPLACE(kode_rekening, ".", "")) = 5')
                ->selectRaw('SUM(realisasi) as total_realisasi')
                ->groupBy('kode_opd')
                ->pluck('total_realisasi', 'kode_opd');

            // Tambahkan kolom total_pagu_setelah_penyesuaian dan realisasi ke setiap OPD
            foreach ($rekapOpd as $opd) {
                $penyesuaian = $simulasiPenyesuaian->where('kode_opd', $opd->kode_skpd);
                $totalPenyesuaian = 0;
                foreach ($penyesuaian as $adj) {
                    if ($adj->operasi == '+') {
                        $totalPenyesuaian += $adj->nilai;
                    } elseif ($adj->operasi == '-') {
                        $totalPenyesuaian -= $adj->nilai;
                    }
                }
                $opd->total_pagu_setelah_penyesuaian = $opd->total_pagu + $totalPenyesuaian;
                $opd->total_penyesuaian = $totalPenyesuaian;
                $opd->total_realisasi = $realisasiPerOpd[$opd->kode_skpd] ?? 0;
            }
        }

        return view('simulasi-perubahan.simulasi-belanja-opd', [
            'tahapans' => $tahapans,
            'tahapanId' => $tahapanId,
            'rekapOpd' => $rekapOpd,
        ]);
    }

    public function rekapitulasiStrukturOpd(Request $request)
    {
        $tahapans = Tahapan::all();
        $tahapanId = $request->input('tahapan_id');

        // Ambil semua kode rekening yang diawali angka 5 dan hanya 2 atau 3 segmen (misal: 5.1 dan 5.1.01)
        $kodeRekenings = KodeRekening::where(function($q) {
            $q->whereRaw("kode_rekening REGEXP '^5\\.[0-9]+$'") // 2 segmen, contoh: 5.1
              ->orWhereRaw("kode_rekening REGEXP '^5\\.[0-9]+\\.[0-9]{2}$'"); // 3 segmen, contoh: 5.1.01
        })
        ->orderBy('kode_rekening')
        ->get();

        // Ambil daftar semua OPD
        $opds = DataAnggaran::select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('kode_skpd')
            ->get();

        $rekapitulasiData = collect();
        
        if ($tahapanId) {
            // Ambil semua data simulasi penyesuaian anggaran
            $simulasiPenyesuaian = SimulasiPenyesuaianAnggaran::all();

            // Ambil data realisasi untuk semua OPD
            $realisasiMap = [];
            $realisasiRows = Realisasi::all();
            foreach ($realisasiRows as $row) {
                $realisasiMap[$row->kode_opd][$row->kode_rekening] = $row->realisasi;
            }

            // Proses data untuk setiap OPD
            foreach ($opds as $opd) {
                $opdData = [
                    'kode_skpd' => $opd->kode_skpd,
                    'nama_skpd' => $opd->nama_skpd,
                    'total_anggaran' => 0,
                    'total_realisasi' => 0,
                    'total_penyesuaian' => 0,
                    'total_proyeksi' => 0,
                    'struktur_belanja' => []
                ];

                // Ambil data anggaran per kode rekening untuk OPD ini
                $anggaranPerRekening = DataAnggaran::select('kode_rekening', 'nama_rekening')
                    ->selectRaw('SUM(pagu) as total_pagu')
                    ->where('tahapan_id', $tahapanId)
                    ->where('kode_skpd', $opd->kode_skpd)
                    ->groupBy('kode_rekening', 'nama_rekening')
                    ->get();

                // Proses setiap kode rekening struktur
                foreach ($kodeRekenings as $kr) {
                    $is3Segmen = count(explode('.', $kr->kode_rekening)) === 3;
                    
                    // Hitung total pagu untuk kode rekening ini
                    $totalPagu = $anggaranPerRekening->where(function($item) use ($kr) {
                        return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                    })->sum('total_pagu');

                    // Hitung total realisasi untuk kode rekening ini
                    $totalRealisasi = 0;
                    $realisasiOpd = $realisasiMap[$opd->kode_skpd] ?? [];
                    foreach ($realisasiOpd as $kodeRek => $realisasi) {
                        if (str_starts_with($kodeRek, $kr->kode_rekening)) {
                            $totalRealisasi += $realisasi;
                        }
                    }

                    // Hitung penyesuaian untuk kode rekening ini
                    $totalPenyesuaian = 0;
                    $penyesuaianOpd = $simulasiPenyesuaian->where('kode_opd', $opd->kode_skpd);
                    foreach ($penyesuaianOpd as $adj) {
                        if (str_starts_with($adj->kode_rekening, $kr->kode_rekening)) {
                            if ($adj->operasi == '+') {
                                $totalPenyesuaian += $adj->nilai;
                            } elseif ($adj->operasi == '-') {
                                $totalPenyesuaian -= $adj->nilai;
                            }
                        }
                    }

                    // Hitung proyeksi perubahan
                    $anggaranRealisasi = $totalPagu - $totalRealisasi;
                    $proyeksiPerubahan = $anggaranRealisasi + $totalPenyesuaian;

                    // Tambahkan ke struktur belanja
                    $opdData['struktur_belanja'][$kr->kode_rekening] = [
                        'nama_rekening' => $kr->uraian,
                        'anggaran' => $totalPagu,
                        'realisasi' => $totalRealisasi,
                        'anggaran_realisasi' => $anggaranRealisasi,
                        'penyesuaian' => $totalPenyesuaian,
                        'proyeksi' => $proyeksiPerubahan,
                        'is_3_segmen' => $is3Segmen
                    ];

                    // Tambahkan ke total jika 3 segmen
                    if ($is3Segmen) {
                        $opdData['total_anggaran'] += $totalPagu;
                        $opdData['total_realisasi'] += $totalRealisasi;
                        $opdData['total_penyesuaian'] += $totalPenyesuaian;
                        $opdData['total_proyeksi'] += $proyeksiPerubahan;
                    }
                }

                $rekapitulasiData->push($opdData);
            }
        }

        return view('simulasi-perubahan.rekapitulasi-struktur-opd', [
            'tahapans' => $tahapans,
            'tahapanId' => $tahapanId,
            'kodeRekenings' => $kodeRekenings,
            'rekapitulasiData' => $rekapitulasiData,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tahapanId = $request->input('tahapan_id');
        
        if (!$tahapanId) {
            return redirect()->back()->with('error', 'Silakan pilih tahapan terlebih dahulu.');
        }

        // Ambil data yang sama seperti di method rekapitulasiStrukturOpd
        $kodeRekenings = KodeRekening::where(function($q) {
            $q->whereRaw("kode_rekening REGEXP '^5\\.[0-9]+$'")
              ->orWhereRaw("kode_rekening REGEXP '^5\\.[0-9]+\\.[0-9]{2}$'");
        })
        ->orderBy('kode_rekening')
        ->get();

        $opds = DataAnggaran::select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('kode_skpd')
            ->get();

        $rekapitulasiData = collect();
        
        // Ambil semua data simulasi penyesuaian anggaran
        $simulasiPenyesuaian = SimulasiPenyesuaianAnggaran::all();

        // Ambil data realisasi untuk semua OPD
        $realisasiMap = [];
        $realisasiRows = Realisasi::all();
        foreach ($realisasiRows as $row) {
            $realisasiMap[$row->kode_opd][$row->kode_rekening] = $row->realisasi;
        }

        // Proses data untuk setiap OPD
        foreach ($opds as $opd) {
            $opdData = [
                'kode_skpd' => $opd->kode_skpd,
                'nama_skpd' => $opd->nama_skpd,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'total_penyesuaian' => 0,
                'total_proyeksi' => 0,
                'struktur_belanja' => []
            ];

            // Ambil data anggaran per kode rekening untuk OPD ini
            $anggaranPerRekening = DataAnggaran::select('kode_rekening', 'nama_rekening')
                ->selectRaw('SUM(pagu) as total_pagu')
                ->where('tahapan_id', $tahapanId)
                ->where('kode_skpd', $opd->kode_skpd)
                ->groupBy('kode_rekening', 'nama_rekening')
                ->get();

            // Proses setiap kode rekening struktur
            foreach ($kodeRekenings as $kr) {
                $is3Segmen = count(explode('.', $kr->kode_rekening)) === 3;
                
                // Hitung total pagu untuk kode rekening ini
                $totalPagu = $anggaranPerRekening->where(function($item) use ($kr) {
                    return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                })->sum('total_pagu');

                // Hitung total realisasi untuk kode rekening ini
                $totalRealisasi = 0;
                $realisasiOpd = $realisasiMap[$opd->kode_skpd] ?? [];
                foreach ($realisasiOpd as $kodeRek => $realisasi) {
                    if (str_starts_with($kodeRek, $kr->kode_rekening)) {
                        $totalRealisasi += $realisasi;
                    }
                }

                // Hitung penyesuaian untuk kode rekening ini
                $totalPenyesuaian = 0;
                $penyesuaianOpd = $simulasiPenyesuaian->where('kode_opd', $opd->kode_skpd);
                foreach ($penyesuaianOpd as $adj) {
                    if (str_starts_with($adj->kode_rekening, $kr->kode_rekening)) {
                        if ($adj->operasi == '+') {
                            $totalPenyesuaian += $adj->nilai;
                        } elseif ($adj->operasi == '-') {
                            $totalPenyesuaian -= $adj->nilai;
                        }
                    }
                }

                // Hitung proyeksi perubahan
                $anggaranRealisasi = $totalPagu - $totalRealisasi;
                $proyeksiPerubahan = $anggaranRealisasi + $totalPenyesuaian;

                // Tambahkan ke struktur belanja
                $opdData['struktur_belanja'][$kr->kode_rekening] = [
                    'nama_rekening' => $kr->uraian,
                    'anggaran' => $totalPagu,
                    'realisasi' => $totalRealisasi,
                    'anggaran_realisasi' => $anggaranRealisasi,
                    'penyesuaian' => $totalPenyesuaian,
                    'proyeksi' => $proyeksiPerubahan,
                    'is_3_segmen' => $is3Segmen
                ];

                // Tambahkan ke total jika 3 segmen
                if ($is3Segmen) {
                    $opdData['total_anggaran'] += $totalPagu;
                    $opdData['total_realisasi'] += $totalRealisasi;
                    $opdData['total_penyesuaian'] += $totalPenyesuaian;
                    $opdData['total_proyeksi'] += $proyeksiPerubahan;
                }
            }

            $rekapitulasiData->push($opdData);
        }

        $tahapanName = Tahapan::find($tahapanId)->name ?? 'Tahapan ' . $tahapanId;
        $filename = 'rekapitulasi-struktur-opd-' . $tahapanName . '-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new RekapitulasiStrukturOpdExport($rekapitulasiData, $kodeRekenings, $tahapanName), $filename);
    }
}
