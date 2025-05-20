<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data tahapan
        $tahapans = DB::table('tahapan')->get();
        
        // Ambil data SKPD
        $skpds = DB::table('data_anggarans')
            ->select('kode_skpd', 'nama_skpd')
            ->distinct()
            ->orderBy('nama_skpd')
            ->get();

        return view('dashboard', compact('tahapans', 'skpds'));
    }
} 