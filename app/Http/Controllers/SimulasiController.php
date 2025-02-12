<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekeningPenyesuaian;
use Illuminate\Support\Facades\DB;
use App\Models\OpdRekeningPenyesuaian;

class SimulasiController extends Controller
{

    // MELAKUKAN SETTINGAN AWAL PERSENTASE PENYESUAIAN
    public function set_rek()
    {
        // Ambil semua kode rekening yang ada di data_anggarans
        $data = DB::table('data_anggarans')
            ->select('data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 
                DB::raw('COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian')
            )
            ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
            ->groupBy('data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'rekening_penyesuaian.persentase_penyesuaian')
            ->orderBy('data_anggarans.kode_rekening', 'asc')
            ->get();

        return view('simulasi.set-rek', compact('data'));
    }
    // MELAKUKAN UPDATE SETTINGAN AWAL PERSENTASE SIMULASI PENYESUAIAN
    public function updatePersentase(Request $request)
    {
    // 🛠 Debugging: Cek apakah data dikirim dengan benar
    // dd($request->all());

    $request->validate([
        'kode_rekening' => 'required|array',
        'persentase_penyesuaian' => 'required|array'
    ]);

    foreach ($request->kode_rekening as $index => $kode) {
        RekeningPenyesuaian::updateOrCreate(
            ['kode_rekening' => $kode], // Hanya gunakan kode_rekening
            ['persentase_penyesuaian' => $request->persentase_penyesuaian[$index]]
        );
    }

    return redirect()->route('set-rek')->with('success', 'Persentase penyesuaian berhasil diperbarui.');
    }


//TAMPILAN PERSENTASE SIMULASI PENYESUAIAN PER OPD PER REK
public function setOpdRekView(Request $request)
{
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
            DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw('COALESCE(opd_rekening_penyesuaian.persentase_penyesuaian, rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian') // Mengutamakan data dari opd_rekening_penyesuaian jika ada
        )
        ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'opd_rekening_penyesuaian.persentase_penyesuaian', 'rekening_penyesuaian.persentase_penyesuaian')
        ->orderBy('data_anggarans.kode_rekening', 'asc');

    if ($request->filled('kode_opd')) {
        $query->where('data_anggarans.kode_skpd', $request->kode_opd);
    }

    $data = $query->get();

    return view('simulasi.set-opd-rek', compact('opds', 'data'))->with('kode_opd', $request->kode_opd);

}

//UPDATE PERSENTASE SIMULASI PENYESUAIAN PER OPD PER REK
public function updatePenyesuaian(Request $request)
{
    // dd($request->all());
    $request->validate([
        'kode_opd' => 'required',
        'kode_rekening' => 'required|array',
        'persentase_penyesuaian' => 'required|array'
    ]);

    foreach ($request->kode_rekening as $index => $kode) {
        OpdRekeningPenyesuaian::updateOrCreate(
            ['kode_opd' => $request->kode_opd, 'kode_rekening' => $kode], // Simpan per OPD dan Rekening
            ['persentase_penyesuaian' => $request->persentase_penyesuaian[$index]]
        );
    }

    return redirect()->route('simulasi.set-opd-rek', ['kode_opd' => $request->kode_opd])
                 ->with('success', 'Penyesuaian berhasil diperbarui.');

}


//RESET PERSENTASE SIMULASI PENYESUAIAN PER OPD PER REK
public function resetOpdRek(Request $request)
{
    $request->validate([
        'kode_opd' => 'required|string'
    ]);

    try {
        OpdRekeningPenyesuaian::where('kode_opd', $request->kode_opd)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Penyesuaian berhasil direset ke nilai awal.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mereset nilai: ' . $e->getMessage()
        ], 500);
    }
}




public function rekapPerOpd(Request $request)
{
    $query = DB::table('data_anggarans')
        ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
        ->select(
            'data_anggarans.kode_skpd', // ✅ Tambahkan kode_skpd
            'data_anggarans.nama_skpd',
            'data_anggarans.kode_rekening',
            'data_anggarans.nama_rekening',
            DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw('COALESCE(rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian')
        )
        ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'rekening_penyesuaian.persentase_penyesuaian');

    if ($request->filled('kode_opd')) {
        $query->where('data_anggarans.kode_skpd', $request->kode_opd);
    }

    return response()->json(['data' => $query->get()]);
}

public function rekapPerOpdView(Request $request)
{
    $kodeOpd = $request->kode_opd;

    // Ambil daftar OPD untuk dropdown filter
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

    if (!empty($kodeOpd)) {
        $query->where('data_anggarans.kode_skpd', $kodeOpd);
    }

    $data = $query->get();

    // Ambil OPD yang dipilih
    $selected_opd = $opds->where('kode_skpd', $kodeOpd)->first();

    return view('simulasi.rekap', compact('data', 'opds', 'selected_opd'));
}



public function exportExcel(Request $request)
{
    return Excel::download(new RekapPerOpdExport($request->kode_opd), 'rekap_per_opd.xlsx');
}






public function rekapPerRekeningView()
{
    $data = DB::table('data_anggarans as da')
        ->leftJoin('rekening_penyesuaian as rp', 'da.kode_rekening', '=', 'rp.kode_rekening')
        ->leftJoin('opd_rekening_penyesuaian as opd_rp', function ($join) {
            $join->on('da.kode_rekening', '=', 'opd_rp.kode_rekening')
                 ->on('da.kode_skpd', '=', 'opd_rp.kode_opd');
        })
        ->select(
            'da.kode_rekening',
            'da.nama_rekening',
            DB::raw('SUM(da.pagu) as pagu_original'),
            
            // Hitung nilai penyesuaian total dari semua OPD yang memiliki data di opd_rekening_penyesuaian
            DB::raw('
                ROUND(
                    SUM(da.pagu * COALESCE(opd_rp.persentase_penyesuaian, rp.persentase_penyesuaian, 0) / 100),
                    0
                ) as nilai_penyesuaian_total
            '),

            // Hitung pagu setelah penyesuaian
            DB::raw('
                ROUND(
                    SUM(da.pagu) - SUM(da.pagu * COALESCE(opd_rp.persentase_penyesuaian, rp.persentase_penyesuaian, 0) / 100),
                    0
                ) as pagu_setelah_penyesuaian
            '),

            // Hitung persentase akhir setelah nilai penyesuaian diperoleh
            DB::raw('
                ROUND(
                    (SUM(da.pagu * COALESCE(opd_rp.persentase_penyesuaian, rp.persentase_penyesuaian, 0) / 100) / SUM(da.pagu)) * 100,
                    2
                ) as persentase_akhir
            ')
        )
        ->where('da.tipe_data', 'original') // Hanya data pagu original
        ->groupBy('da.kode_rekening', 'da.nama_rekening')
        ->orderBy('da.kode_rekening')
        ->get();

    return view('simulasi.rekening', compact('data'));
}







public function rekapPaguPerOpd()
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

            // ✅ Perbaikan: Gunakan rata-rata berbobot berdasarkan total pagu di OPD
            DB::raw('
                ROUND(
                    COALESCE(
                        SUM(da.pagu * opd_rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        SUM(da.pagu * rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        0
                    ), 2
                ) as persentase_penyesuaian
            '),

            // ✅ Jumlah penyesuaian (dihitung ulang sesuai persentase terbaru)
            DB::raw('
                ROUND(
                    SUM(da.pagu) * (COALESCE(
                        SUM(da.pagu * opd_rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        SUM(da.pagu * rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        0
                    ) / 100), 0
                ) as nilai_penyesuaian
            '),

            // ✅ Pagu setelah penyesuaian (dihitung ulang dengan cara yang sama)
            DB::raw('
                ROUND(
                    SUM(da.pagu) - (SUM(da.pagu) * (COALESCE(
                        SUM(da.pagu * opd_rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        SUM(da.pagu * rp.persentase_penyesuaian) / NULLIF(SUM(da.pagu), 0), 
                        0
                    ) / 100)), 0
                ) as pagu_setelah_penyesuaian
            ')
        )
        ->where('da.tipe_data', 'original')
        ->groupBy('da.kode_skpd', 'da.nama_skpd')
        ->orderBy('da.kode_skpd', 'asc')
        ->get();

    return view('simulasi.opd', compact('data'));
}


public function rekapPerjalananDinas()
{
    // Ambil OPD
    $opds = DB::table('opd')->get();

    // Ambil semua rekening yang mengandung kata "Perjalanan Dinas"
    $rekeningPerjalanan = DB::table('opd_rekening')
        ->where('nama_rekening', 'LIKE', '%Perjalanan Dinas%')
        ->get();

    // Ambil data persentase penyesuaian
    $penyesuaianOPD = DB::table('opd_rekening_penyesuaian')->get()->keyBy('kode_rekening');
    $penyesuaianGlobal = DB::table('rekening_penyesuaian')->get()->keyBy('kode_rekening');

    $data = [];

    foreach ($rekeningPerjalanan as $row) {
        // Cari persentase penyesuaian: OPD dulu, kalau tidak ada cek global
        $persentase = $penyesuaianOPD[$row->kode_rekening]->persentase_penyesuaian ?? 
                      $penyesuaianGlobal[$row->kode_rekening]->persentase_penyesuaian ?? 0;

        $data[] = [
            'nama_skpd' => $opds->where('kode_skpd', $row->kode_opd)->first()->nama_skpd ?? '-',
            'kode_rekening' => $row->kode_rekening,
            'nama_rekening' => $row->nama_rekening,
            'pagu_original' => $row->pagu_original,
            'persentase_penyesuaian' => $persentase,
        ];
    }

    return view('simulasi.rekap_perjalanan_dinas', compact('data'));
}




    
}
