<?php

namespace App\Http\Controllers;

use App\Models\DataAnggaran;
use Illuminate\Http\Request;
use App\Exports\RekapPerOpdExport;
use Illuminate\Support\Facades\DB;
use App\Models\RekeningPenyesuaian;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\OpdRekeningPenyesuaian;


//note
//Nama OPD
//Pagu Murni | Jumlah Pengurangan | Pagu Setelah Pengurangan

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
            DB::raw('SUM(CASE WHEN tahapan_id = "1" THEN pagu ELSE 0 END) as pagu_original'),
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
        ->where('da.tahapan_id', '1') // Hanya data pagu original
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
            ')
        )
        ->where('da.tahapan_id', '1')
        ->groupBy('da.kode_skpd', 'da.nama_skpd')
        ->orderBy('da.kode_skpd', 'asc')
        ->get();

    return view('simulasi.opd', compact('data'));
}

// FUNCTION BARU

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


















public function rekapPerjalananDinas()
{
    // Ambil data rekening perjalanan dinas dari semua OPD
    $rekap = DB::table('data_anggarans')
        ->leftJoin('opd_rekening_penyesuaian', function ($join) {
            $join->on('data_anggarans.kode_rekening', '=', 'opd_rekening_penyesuaian.kode_rekening')
                 ->on('data_anggarans.kode_skpd', '=', 'opd_rekening_penyesuaian.kode_opd');
        })
        ->where('data_anggarans.nama_rekening', 'LIKE', '%Perjalanan Dinas%') // Hanya ambil rekening perjalanan dinas
        ->select(
            'data_anggarans.kode_skpd',
            'data_anggarans.nama_skpd AS nama_opd', // Ganti alias untuk konsistensi di Blade
            'data_anggarans.kode_rekening',
            'data_anggarans.nama_rekening',
            DB::raw('SUM(CASE WHEN tahapan_id = "1" THEN pagu ELSE 0 END) as pagu_original'),
            DB::raw('COALESCE(opd_rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_pengurangan')
        )
        ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'opd_rekening_penyesuaian.persentase_penyesuaian')
        ->orderBy('data_anggarans.kode_skpd')
        ->get();

    // Hitung nilai pengurangan dan pagu setelah pengurangan
    foreach ($rekap as $row) {
        $row->pagu_pengurangan = ($row->pagu_original * $row->persentase_pengurangan) / 100;
        $row->pagu_setelah_pengurangan = $row->pagu_original - $row->pagu_pengurangan;
    }

    return view('simulasi.rekap-perjalanan-dinas', compact('rekap'));
}


public function perjalananDinasView(Request $request)
{
    // Ambil semua tahapan untuk dropdown filter
    $tahapans = DB::table('tahapan')->orderBy('id')->get();
    
    // Ambil tahapan yang dipilih (default tahapan 1)
    $tahapanId = $request->get('tahapan_id', 1);
    
    $data = DB::table('data_anggarans')
        ->leftJoin('opd_rekening_penyesuaian', function ($join) {
            $join->on('data_anggarans.kode_rekening', '=', 'opd_rekening_penyesuaian.kode_rekening')
                 ->on('data_anggarans.kode_skpd', '=', 'opd_rekening_penyesuaian.kode_opd');
        })
        ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
        ->select(
            'data_anggarans.kode_skpd',
            'data_anggarans.nama_skpd AS nama_opd',
            'data_anggarans.kode_rekening',
            'data_anggarans.nama_rekening',
            DB::raw("SUM(CASE WHEN tahapan_id = {$tahapanId} THEN pagu ELSE 0 END) as pagu_original"),
            DB::raw('COALESCE(opd_rekening_penyesuaian.persentase_penyesuaian, rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian')
        )
        ->where('data_anggarans.nama_rekening', 'LIKE', '%Perjalanan Dinas%')
        ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'opd_rekening_penyesuaian.persentase_penyesuaian', 'rekening_penyesuaian.persentase_penyesuaian')
        ->orderBy('data_anggarans.kode_skpd', 'asc')
        ->get();

    // Hitung total perjalanan dinas per OPD, pagu pengurangan per OPD, dan pagu setelah pengurangan per OPD
    $totalPerOpd = [];
    $totalPenguranganPerOpd = [];
    $totalSetelahPenguranganPerOpd = [];

    foreach ($data as $row) {
        if (!isset($totalPerOpd[$row->nama_opd])) {
            $totalPerOpd[$row->nama_opd] = 0;
            $totalPenguranganPerOpd[$row->nama_opd] = 0;
            $totalSetelahPenguranganPerOpd[$row->nama_opd] = 0;
        }
        $totalPerOpd[$row->nama_opd] += $row->pagu_original;

        // Perhitungan nilai penyesuaian dan pagu setelah penyesuaian
        $row->nilai_penyesuaian = ($row->pagu_original * $row->persentase_penyesuaian) / 100;
        $row->pagu_setelah_penyesuaian = $row->pagu_original - $row->nilai_penyesuaian;

        // Tambahkan total pagu pengurangan dan pagu setelah pengurangan per OPD
        $totalPenguranganPerOpd[$row->nama_opd] += $row->nilai_penyesuaian;
        $totalSetelahPenguranganPerOpd[$row->nama_opd] += $row->pagu_setelah_penyesuaian;
    }

    // Tambahkan nilai total ke setiap baris
    foreach ($data as $row) {
        $row->total_perjalanan_dinas = $totalPerOpd[$row->nama_opd] ?? 0;
        $row->total_pengurangan_perjalanan_dinas = $totalPenguranganPerOpd[$row->nama_opd] ?? 0;
        $row->total_setelah_pengurangan_perjalanan_dinas = $totalSetelahPenguranganPerOpd[$row->nama_opd] ?? 0;
    }

    // Urutkan data berdasarkan total pagu perjalanan dinas terbesar
    $data = $data->sortByDesc('total_perjalanan_dinas')->values();

    return view('simulasi.perjalanan-dinas', compact('data', 'tahapans', 'tahapanId'));
}


public function updatePersentasePd(Request $request)
{
    $request->validate([
        'kode_opd' => 'required|string',
        'kode_rekening' => 'required|string',
        'persentase_penyesuaian' => 'required|numeric|min:0|max:100'
    ]);

    OpdRekeningPenyesuaian::updateOrCreate(
        ['kode_opd' => $request->kode_opd, 'kode_rekening' => $request->kode_rekening],
        ['persentase_penyesuaian' => $request->persentase_penyesuaian]
    );

    return response()->json(['success' => true]);
}

public function updateMassal(Request $request)
{
    $request->validate([
        'data' => 'required|array',
        'data.*.kode_opd' => 'required|string',
        'data.*.kode_rekening' => 'required|string',
        'data.*.persentase_penyesuaian' => 'required|numeric|min:0|max:100'
    ]);

    foreach ($request->data as $item) {
        OpdRekeningPenyesuaian::updateOrCreate(
            [
                'kode_opd' => $item['kode_opd'],
                'kode_rekening' => $item['kode_rekening']
            ],
            [
                'persentase_penyesuaian' => $item['persentase_penyesuaian']
            ]
        );
    }

    return response()->json(['success' => true, 'message' => 'Semua perubahan berhasil disimpan!']);
}



// public function rekeningFilterView(Request $request)
// {
//     $data = collect(); // Default kosong saat halaman pertama kali dimuat

//     if ($request->has('nama_rekening') && $request->filled('nama_rekening')) {
//         $query = DB::table('data_anggarans')
//             ->leftJoin('opd_rekening_penyesuaian', function ($join) {
//                 $join->on('data_anggarans.kode_rekening', '=', 'opd_rekening_penyesuaian.kode_rekening')
//                      ->on('data_anggarans.kode_skpd', '=', 'opd_rekening_penyesuaian.kode_opd');
//             })
//             ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
//             ->select(
//                 'data_anggarans.kode_skpd',
//                 'data_anggarans.nama_skpd AS nama_opd',
//                 'data_anggarans.kode_rekening',
//                 'data_anggarans.nama_rekening',
//                 DB::raw('SUM(CASE WHEN tipe_data = "original" THEN pagu ELSE 0 END) as pagu_original'),
//                 DB::raw('COALESCE(opd_rekening_penyesuaian.persentase_penyesuaian, rekening_penyesuaian.persentase_penyesuaian, 0) as persentase_penyesuaian')
//             )
//             ->groupBy('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 'data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'opd_rekening_penyesuaian.persentase_penyesuaian', 'rekening_penyesuaian.persentase_penyesuaian')
//             ->orderBy('data_anggarans.kode_skpd', 'asc');

//         // 🔥 Filter berdasarkan Nama Rekening
//         $query->where('data_anggarans.nama_rekening', 'LIKE', "%{$request->nama_rekening}%");

//         $data = $query->get();

//         // 🔥 Hitung total perjalanan dinas per OPD, pagu pengurangan per OPD, dan pagu setelah pengurangan per OPD
//         $totalPerOpd = [];
//         $totalPenguranganPerOpd = [];
//         $totalSetelahPenguranganPerOpd = [];

//         foreach ($data as $row) {
//             if (!isset($totalPerOpd[$row->nama_opd])) {
//                 $totalPerOpd[$row->nama_opd] = 0;
//                 $totalPenguranganPerOpd[$row->nama_opd] = 0;
//                 $totalSetelahPenguranganPerOpd[$row->nama_opd] = 0;
//             }
//             $totalPerOpd[$row->nama_opd] += $row->pagu_original;

//             // Perhitungan nilai penyesuaian dan pagu setelah penyesuaian
//             $row->nilai_penyesuaian = ($row->pagu_original * $row->persentase_penyesuaian) / 100;
//             $row->pagu_setelah_penyesuaian = $row->pagu_original - $row->nilai_penyesuaian;

//             // Tambahkan total pagu pengurangan dan pagu setelah pengurangan per OPD
//             $totalPenguranganPerOpd[$row->nama_opd] += $row->nilai_penyesuaian;
//             $totalSetelahPenguranganPerOpd[$row->nama_opd] += $row->pagu_setelah_penyesuaian;
//         }

//         // Tambahkan nilai total ke setiap baris
//         foreach ($data as $row) {
//             $row->total_perjalanan_dinas = $totalPerOpd[$row->nama_opd] ?? 0;
//             $row->total_pengurangan_perjalanan_dinas = $totalPenguranganPerOpd[$row->nama_opd] ?? 0;
//             $row->total_setelah_pengurangan_perjalanan_dinas = $totalSetelahPenguranganPerOpd[$row->nama_opd] ?? 0;
//         }
//     }

//     return view('simulasi.rekening-filter', compact('data'));
// }





public function updateRekeningFilter(Request $request)
{
    $request->validate([
        'data' => 'required|array',
        'data.*.kode_opd' => 'required|string',
        'data.*.kode_rekening' => 'required|string',
        'data.*.persentase_penyesuaian' => 'required|numeric|min:0|max:100'
    ]);

    foreach ($request->data as $item) {
        OpdRekeningPenyesuaian::updateOrCreate(
            [
                'kode_opd' => $item['kode_opd'],
                'kode_rekening' => $item['kode_rekening']
            ],
            [
                'persentase_penyesuaian' => $item['persentase_penyesuaian']
            ]
        );
    }

    return response()->json(['success' => true, 'message' => 'Semua perubahan berhasil disimpan!']);
}


public function opdSubkegrekpd(Request $request)
{
    // Ambil daftar OPD yang unik
    $opds = DataAnggaran::select('kode_skpd', 'nama_skpd')->distinct()->orderBy('kode_skpd')->get();
    
    return view('simulasi.opdsubkegrekpd', compact('opds'));
}

public function getSubkegByOpd(Request $request)
{
    // Validasi input
    $request->validate([
        'kode_opd' => 'required|string|exists:data_anggarans,kode_skpd'
    ]);

    $kodeOpd = $request->kode_opd;

    // Ambil daftar sub kegiatan berdasarkan OPD
    $subkegiatan = DataAnggaran::where('kode_skpd', $kodeOpd)
        ->where('tipe_data', 'original')
        ->select('kode_sub_kegiatan', 'nama_sub_kegiatan', DB::raw('SUM(pagu) as pagu_murni'))
        ->groupBy('kode_sub_kegiatan', 'nama_sub_kegiatan')
        ->orderBy('kode_sub_kegiatan')
        ->get();

    // Ambil hanya rekening "Belanja Perjalanan Dinas%"
    $rekeningList = DataAnggaran::where('kode_skpd', $kodeOpd)
        ->where('tipe_data', 'original')
        ->where('nama_rekening', 'like', 'Belanja Perjalanan Dinas%') 
        ->select('kode_sub_kegiatan', 'kode_rekening', 'nama_rekening', 'pagu')
        ->orderBy('kode_sub_kegiatan')
        ->orderBy('kode_rekening')
        ->get();

    
    // Ambil persentase penyesuaian dari tabel 'opd_skr_penyesuaian'
    $penyesuaian = DB::table('opd_skr_penyesuaian')
    ->where('kode_opd', $kodeOpd)
    ->select('kode_sub_kegiatan', 'kode_rekening', 'persentase')
    ->get();

// Buat map [kode_sub_keg][kode_rek] => persentase
$persenMap = [];
foreach ($penyesuaian as $ps) {
    $persenMap[$ps->kode_sub_kegiatan][$ps->kode_rekening] = $ps->persentase;
}

    // Gabungkan data ke dalam format yang sesuai
    $result = [];

    foreach ($subkegiatan as $sub) {
        $rekeningData = $rekeningList->where('kode_sub_kegiatan', $sub->kode_sub_kegiatan)->values();

        if ($rekeningData->isNotEmpty()) {
            foreach ($rekeningData as $index => $rek) {
                // Cari persentase dari map, jika tidak ada => 0
                $persen = $persenMap[$sub->kode_sub_kegiatan][$rek->kode_rekening] ?? 0;
                $result[] = [
                    'kode_sub_kegiatan' => $index === 0 ? $sub->kode_sub_kegiatan : '',
                    'nama_sub_kegiatan' => $index === 0 ? $sub->nama_sub_kegiatan : '',
                    'pagu_murni'        => $index === 0 ? number_format($sub->pagu_murni, 0, ',', '.') : '',
                    'kode_rekening'     => $rek->kode_rekening,
                    'nama_rekening'     => $rek->nama_rekening,
                    'pagu'              => number_format($rek->pagu, 0, ',', '.'),
                    'persentase'       => $persen  // <-- Tambahkan persentase
                ];
            }
        } else {
            // Jika sub kegiatan tidak memiliki rekening "Belanja Perjalanan Dinas%", tetap tampilkan sub kegiatannya
            $result[] = [
                'kode_sub_kegiatan' => $sub->kode_sub_kegiatan,
                'nama_sub_kegiatan' => $sub->nama_sub_kegiatan,
                'pagu_murni'        => number_format($sub->pagu_murni, 0, ',', '.'),
                'kode_rekening'     => '-',
                'nama_rekening'     => '-',
                'pagu'              => '-',
                'persentase'        => 0  // default 0
            ];
        }
    }

    return response()->json($result);
}

public function getRekapBpdByOpd(Request $request)
{
    $request->validate([
        'kode_opd' => 'required|string|exists:data_anggarans,kode_skpd'
    ]);

    $kodeOpd = $request->kode_opd;

    // Ambil data Belanja Perjalanan Dinas di OPD ini
    $data = DataAnggaran::where('kode_skpd', $kodeOpd)
        ->where('tipe_data', 'original')
        ->where('nama_rekening', 'like', 'Belanja Perjalanan Dinas%')
        ->select(
            'kode_rekening',
            'nama_rekening',
            DB::raw('SUM(pagu) as total_pagu')
        )
        ->groupBy('kode_rekening', 'nama_rekening')
        ->orderBy('kode_rekening')
        ->get();

    return response()->json($data);
}


public function updatePersentaseSubkeg(Request $request)
{
    try {
        Log::info('Request masuk:', ['data' => $request->all()]);

        $data = $request->input('data');

        if (!$data || !is_array($data)) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak valid'], 400);
        }

        foreach ($data as $item) {
            // Pastikan semua parameter ada
            if (!isset($item['kode_opd'], $item['kode_sub_kegiatan'], $item['kode_rekening'], $item['persentase'])) {
                Log::error('Data tidak lengkap:', $item);
                continue;
            }

            Log::info('Menyimpan data:', $item);

            // Update atau Insert ke `opd_skr_penyesuaian`
            DB::table('opd_skr_penyesuaian')->updateOrInsert(
                [
                    'kode_opd' => $item['kode_opd'],
                    'kode_sub_kegiatan' => $item['kode_sub_kegiatan'],
                    'kode_rekening' => $item['kode_rekening']
                ],
                [
                    'persentase' => $item['persentase'],
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Data berhasil diperbarui']);

    } catch (\Exception $e) {
        Log::error('Gagal menyimpan data:', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan data', 'error' => $e->getMessage()], 500);
    }
}


//SET PAGU PER OPD
public function set_pagu_opd()
    {
        // Ambil semua kode rekening yang ada di data_anggarans
        $data = DB::table('data_anggarans')
            ->select('data_anggarans.kode_skpd', 'data_anggarans.nama_skpd', 
                DB::raw('COALESCE(paguopd.nilai, 0) as paguopdbaru')
            )
            ->leftJoin('rekening_penyesuaian', 'data_anggarans.kode_rekening', '=', 'rekening_penyesuaian.kode_rekening')
            ->groupBy('data_anggarans.kode_rekening', 'data_anggarans.nama_rekening', 'rekening_penyesuaian.persentase_penyesuaian')
            ->orderBy('data_anggarans.kode_rekening', 'asc')
            ->get();

        return view('simulasi.set-rek', compact('data'));
    }

}
