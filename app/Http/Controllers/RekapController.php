<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index()
    {
        $rekap = [
            ['kode_opd' => '001', 'nama_opd' => 'Dinas Pendidikan', 'kode_rekening' => '5.1.02', 'nama_rekening' => 'Belanja Pegawai', 'pagu_original' => 1000000000, 'pagu_revisi' => 1050000000, 'selisih' => 50000000],
            ['kode_opd' => '002', 'nama_opd' => 'Dinas Kesehatan', 'kode_rekening' => '5.2.01', 'nama_rekening' => 'Belanja Modal', 'pagu_original' => 2000000000, 'pagu_revisi' => 1800000000, 'selisih' => -200000000],
        ];
        
        return view('data.rekap', compact('rekap'));
    }
}
