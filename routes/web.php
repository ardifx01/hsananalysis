<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataAnggaranController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\RekapPerOpdController;

// Dashboard tetap bisa diakses tanpa login
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Group middleware untuk memastikan hanya user yang terdaftar bisa mengakses fitur lainnya
Route::middleware('auth')->group(function () {

    // SETINGAN AWAL PERSENTASE SIMULASI PENYESUAIAN
    Route::get('simulasi/set-rek', [SimulasiController::class, 'set_rek'])->name('set-rek');
    Route::post('simulasi/set-rek/update', [SimulasiController::class, 'updatePersentase'])->name('set-rek.update');
    Route::post('/simulasi/update-persentase', [SimulasiController::class, 'updatePersentasePd'])->name('simulasi.update-persentase');
    Route::post('/simulasi/update-massal', [SimulasiController::class, 'updateMassal'])->name('simulasi.update-massal');



    // TAMPILAN SIMULASI
    Route::get('/simulasi/set-opd-rek', [SimulasiController::class, 'setOpdRekView'])->name('simulasi.set-opd-rek');
    Route::post('/simulasi/set-opd-rek/update', [SimulasiController::class, 'updatePenyesuaian'])->name('simulasi.set-opd-rek.update');
    Route::post('/set-opd-rek/reset', [SimulasiController::class, 'resetOpdRek'])->name('simulasi.set-opd-rek.reset');

    Route::get('/simulasi/rekening', [SimulasiController::class, 'rekapPerRekeningView'])->name('simulasi.rekening');
    Route::get('/simulasi/rekap-pagu-opd', [SimulasiController::class, 'rekapPaguPerOpd'])->name('simulasi.pagu.opd');
    Route::get('/simulasi/perjalanan-dinas', [SimulasiController::class, 'perjalananDinasView'])->name('simulasi.perjalanan-dinas');


    //TAMPILAN SIMULASI OPD SUB KEGIATAN REKNING PERJALANAN DINAS
    Route::get('/simulasi/opdsubkegrekpd', [SimulasiController::class, 'opdSubkegrekpd'])->name('simulasi.opdsubkegrekpd');
    Route::get('/simulasi/get-subkeg-by-opd', [SimulasiController::class, 'getSubkegByOpd'])->name('simulasi.get-subkeg-by-opd');

    Route::get('/simulasi/get-rekap-bpd-by-opd', [SimulasiController::class, 'getRekapBpdByOpd'])->name('simulasi.get-rekap-bpd-by-opd');





    //FILTER
    Route::get('/simulasi/rekening-filter', [SimulasiController::class, 'rekeningFilterView'])->name('simulasi.rekening-filter');
    Route::post('/simulasi/rekening-filter/update', [SimulasiController::class, 'updateRekeningFilter'])->name('simulasi.rekening-filter.update');


    // EXPORT DATA REKAP
    Route::get('/rekap-peropd/export/excel', [RekapPerOpdController::class, 'exportExcel'])->name('rekap.peropd.export.excel');
    Route::get('/rekap-peropd/export/pdf', [RekapPerOpdController::class, 'exportPdf'])->name('rekap.peropd.export.pdf');

    // REKAP REKENING
    Route::get('/rekap-rekening', [ReportController::class, 'rekapRekening'])->name('rekap.rekening');
    Route::get('/rekap-rekening/data', [ReportController::class, 'getRekapRekening'])->name('rekap.rekening.data');

    // REPORT
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/data', [ReportController::class, 'getData'])->name('report.data');

    Route::get('/dashboard/data', [ReportController::class, 'getDataDashboard'])->name('dashboard.data');

    // IMPORT DATA ANGGARAN
    Route::get('/import', [DataAnggaranController::class, 'index']);
    Route::post('/import', [DataAnggaranController::class, 'importData'])->name('import');

    // PERBANDINGAN DATA
    Route::get('/compare/opd-rek', [DataAnggaranController::class, 'compareData'])->name('compare');
    Route::get('/compare/rek', [DataAnggaranController::class, 'compareDataRek'])->name('compare-rek');
    Route::get('/compare-opd', [DataAnggaranController::class, 'comparePerOpd'])->name('compare-opd');

    Route::get('/compare/sub-kegiatan', [DataAnggaranController::class, 'comparePerSubKegiatan'])->name('compare.sub-kegiatan');


    // TOOLS & MANAJEMEN DATA
    Route::get('/tools', [DataAnggaranController::class, 'importPage'])->name('import.page');
    Route::delete('/hapus-original', [DataAnggaranController::class, 'hapusOriginal'])->name('hapus.original');
    Route::delete('/hapus-revisi', [DataAnggaranController::class, 'hapusRevisi'])->name('hapus.revisi');

    // PROFILE USER
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ROUTE AUTENTIKASI
require __DIR__.'/auth.php';
