<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use App\Models\Tahapan;
use App\Models\KodeRekening;
use App\Models\SimulasiPenyesuaianAnggaran;

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
        ]);
    }
}
