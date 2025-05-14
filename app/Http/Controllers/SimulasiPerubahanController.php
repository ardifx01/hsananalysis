<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAnggaran;
use App\Models\Tahapan;

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

        return view('simulasi-perubahan.index', compact('tahapans', 'tahapanId', 'rekap', 'skpds', 'skpdKode', 'skpdTerpilih', 'tahapanTerpilih'));
    }
}
