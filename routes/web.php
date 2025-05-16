<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataAnggaranController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\RekapPerOpdController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\KodeRekeningController;

use App\Http\Controllers\ProgressController;
use App\Http\Controllers\TahapanController;
use App\Http\Controllers\RealisasiController;

// Dashboard tetap bisa diakses tanpa login
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');
Route::middleware('auth')->group(function () {
//Jadwal Anggaran
Route::resource('tahapan', TahapanController::class);

//Data Anggaran
Route::resource('data-anggaran', DataAnggaranController::class);
Route::delete('data-anggaran/{tahapan_id}/{tanggal_upload}/{jam_upload}', [DataAnggaranController::class, 'destroy'])->name('data-anggaran.destroy');
Route::get('/data-anggaran', [DataAnggaranController::class, 'index'])->name('data');
Route::post('/data-anggaran/upload', [DataAnggaranController::class, 'upload'])->name('data-anggaran.upload');   

//Kode Rekening
Route::resource('kode-rekening', KodeRekeningController::class);
Route::post('/kode-rekening/import', [KodeRekeningController::class, 'import'])->name('kode-rekening.import');
Route::get('/kode-rekening/template/download', [KodeRekeningController::class, 'downloadTemplate'])->name('kode-rekening.template.download');

// compare data
Route::get('/compare-opd', [CompareController::class, 'compareOpd'])->name('compare-opd');
Route::get('/compare/rek', [CompareController::class, 'compareDataRek'])->name('compare-rek');
Route::get('/compare/opd-rek', [CompareController::class, 'compareDataOpdRek'])->name('compareDataOpdRek');

Route::get('/compare/sub-kegiatan', [CompareController::class, 'comparePerSubKegiatan'])->name('compare.sub-kegiatan');



//kertas kerja
Route::get('simulasi/set-rek', [SimulasiController::class, 'set_rek'])->name('set-rek');
Route::post('simulasi/set-rek/update', [SimulasiController::class, 'updatePersentase'])->name('set-rek.update');
Route::post('/simulasi/update-persentase', [SimulasiController::class, 'updatePersentasePd'])->name('simulasi.update-persentase');
Route::post('/simulasi/update-massal', [SimulasiController::class, 'updateMassal'])->name('simulasi.update-massal');

Route::get('/simulasi/set-opd-rek', [SimulasiController::class, 'setOpdRekView'])->name('simulasi.set-opd-rek');
Route::post('/simulasi/set-opd-rek/update', [SimulasiController::class, 'updatePenyesuaian'])->name('simulasi.set-opd-rek.update');
Route::post('/set-opd-rek/reset', [SimulasiController::class, 'resetOpdRek'])->name('simulasi.set-opd-rek.reset');

Route::get('/simulasi/perjalanan-dinas', [SimulasiController::class, 'perjalananDinasView'])->name('simulasi.perjalanan-dinas');


//Simulasi
Route::get('/simulasi/rekening', [SimulasiController::class, 'rekapPerRekeningView'])->name('simulasi.rekening');
Route::get('/simulasi/opdsubkegrekpd', [SimulasiController::class, 'opdSubkegrekpd'])->name('simulasi.opdsubkegrekpd');
Route::get('/simulasi/rekap-pagu-opd', [SimulasiController::class, 'rekapPaguPerOpd'])->name('simulasi.pagu.opd');

//Progress

Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
Route::get('/progress/opd-rek', [ProgressController::class, 'progressPerOpdRek'])->name('progress.opd-rek');

// Group middleware untuk memastikan hanya user yang terdaftar bisa mengakses fitur lainnya


    // SETINGAN AWAL PERSENTASE SIMULASI PENYESUAIAN
    



    // TAMPILAN SIMULASI
    

    
    
    


    //TAMPILAN SIMULASI OPD SUB KEGIATAN REKNING PERJALANAN DINAS
    
    Route::get('/simulasi/get-subkeg-by-opd', [SimulasiController::class, 'getSubkegByOpd'])->name('simulasi.get-subkeg-by-opd');

    Route::get('/simulasi/get-rekap-bpd-by-opd', [SimulasiController::class, 'getRekapBpdByOpd'])->name('simulasi.get-rekap-bpd-by-opd');

    Route::post('/simulasi/updatepersentasesubkeg', [SimulasiController::class, 'updatePersentaseSubkeg'])->name('simulasi.updatepersentasesubkeg');

    Route::post('/simulasi/update-persentase-subkeg', [SimulasiController::class, 'updatePersentasesubkeg'])
    ->name('simulasi.updatepersentasesubkeg');







    //FILTER
    // Route::get('/simulasi/rekening-filter', [SimulasiController::class, 'rekeningFilterView'])->name('simulasi.rekening-filter');
    // Route::post('/simulasi/rekening-filter/update', [SimulasiController::class, 'updateRekeningFilter'])->name('simulasi.rekening-filter.update');


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
    
    
    //NEW ROUTE PERBANDINGAN DATA
    


    // TOOLS & MANAJEMEN DATA

     

    // PROFILE USER
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Simulasi Perubahan Anggaran
    Route::get('/simulasi-perubahan', [App\Http\Controllers\SimulasiPerubahanController::class, 'index'])->name('simulasi-perubahan.index');

    // Simulasi Penyesuaian Anggaran
    Route::resource('simulasi-penyesuaian-anggaran', App\Http\Controllers\SimulasiPenyesuaianAnggaranController::class);

    // Simulasi Belanja per OPD
    Route::get('/simulasi/belanja-opd', [App\Http\Controllers\SimulasiPerubahanController::class, 'simulasiBelanjaOpd'])->name('simulasi.belanja-opd');

    // Realisasi
    Route::resource('realisasi', RealisasiController::class);
    Route::post('/realisasi/upload', [RealisasiController::class, 'upload'])->name('realisasi.upload');
    Route::post('/realisasi/bulk-delete', [RealisasiController::class, 'bulkDelete'])->name('realisasi.bulk-delete');
});

// ROUTE AUTENTIKASI
require __DIR__.'/auth.php';
