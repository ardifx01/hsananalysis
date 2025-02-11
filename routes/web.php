<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataAnggaranController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\RekapPerOpdController;


//SETINGAN AWAL PERSENTASE SIMULASI PENYESUAIAN
Route::get('simulasi/set-rek', [SimulasiController::class, 'set_rek'])->name('set-rek');
Route::post('simulasi/set-rek/update', [SimulasiController::class, 'updatePersentase'])->name('set-rek.update');

//TAMPILAN 
Route::get('/simulasi/set-opd-rek', [SimulasiController::class, 'setOpdRekView'])->name('simulasi.set-opd-rek');
Route::post('/simulasi/set-opd-rek/update', [SimulasiController::class, 'updatePenyesuaian'])->name('simulasi.set-opd-rek.update');


Route::post('/set-opd-rek/reset', [SimulasiController::class, 'resetOpdRek'])->name('simulasi.set-opd-rek.reset');

Route::get('/simulasi/rekening', [SimulasiController::class, 'rekapPerRekeningView'])->name('simulasi.rekening');

Route::get('/simulasi/rekap-pagu-opd', [SimulasiController::class, 'rekapPaguPerOpd'])->name('simulasi.pagu.opd');




// Route untuk menampilkan halaman rekap per OPD
// Route::get('/simulasi/rekap', [SimulasiController::class, 'rekapPerOpdView'])->name('rekap.peropd.view');

// Route untuk mengambil data rekap per OPD (AJAX)
// Route::get('/simulasi/rekap/data', [SimulasiController::class, 'rekapPerOpd'])->name('rekap.peropd.data');

// Route::get('/rekap-peropd', [SimulasiController::class, 'rekapPerOpdView'])->name('rekap.peropd.view');


Route::get('/rekap-peropd/export/excel', [RekapPerOpdController::class, 'exportExcel'])->name('rekap.peropd.export.excel');
Route::get('/rekap-peropd/export/pdf', [RekapPerOpdController::class, 'exportPdf'])->name('rekap.peropd.export.pdf');



Route::get('/rekap-rekening', [ReportController::class, 'rekapRekening'])->name('rekap.rekening');
Route::get('/rekap-rekening/data', [ReportController::class, 'getRekapRekening'])->name('rekap.rekening.data');


Route::get('/report', [ReportController::class, 'index'])->name('report.index');
Route::get('/report/data', [ReportController::class, 'getData'])->name('report.data');

Route::get('/dashboard/data', [ReportController::class, 'getDataDashboard'])->name('dashboard.data');


Route::get('/import', [DataAnggaranController::class, 'index']);
Route::post('/import', [DataAnggaranController::class, 'importData'])->name('import');
Route::get('/compare/opd-rek', [DataAnggaranController::class, 'compareData'])->name('compare');
Route::get('/compare/rek', [DataAnggaranController::class, 'compareDataRek'])->name('compare-rek');
Route::get('/compare-opd', [DataAnggaranController::class, 'comparePerOpd'])->name('compare-opd');

Route::get('/tools', [DataAnggaranController::class, 'importPage'])->name('import.page');
Route::delete('/hapus-original', [DataAnggaranController::class, 'hapusOriginal'])->name('hapus.original');
Route::delete('/hapus-revisi', [DataAnggaranController::class, 'hapusRevisi'])->name('hapus.revisi');



Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
