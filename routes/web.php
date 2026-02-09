<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MinusanController;
use App\Http\Controllers\ReportKhususController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ImportStatusController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Middleware isLogin
Route::middleware('isLogin')->group(function () {
    // Login
    Route::redirect('/', '/login');
    Route::get('login', [AuthController::class, 'login'])->name('login');

    // Login Proses
    Route::post('login', [AuthController::class, 'loginProses'])->name('loginProses');
});

// Logout
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Route Area Chart
Route::get('/chart/minusan', [MinusanController::class, 'chartMinusan']);

Route::middleware('checkLogin')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Minusan
    Route::get('minusan', [MinusanController::class, 'index'])->name('minusan');

    // Rekap Bulanan
    Route::get('rekap-bulanan', [MinusanController::class, 'rekapBulanan'])->name('admin.rekap.index');

    // Rekap Bulanan PDF
    Route::get('rekap-bulanan/cetak/{bulan}/{tahun}', [MinusanController::class, 'rekapBulananPdf'])->name('admin.rekap.cetak');

    // Rekap Bulanan Excel
    Route::get('rekap-bulanan/excel/{bulan}/{tahun}', [MinusanController::class, 'rekapBulananExcel'])->name('admin.rekap.excel');

    // Rekap Tilangan
    Route::get('rekap-tilangan', [MinusanController::class, 'rekapTilangan'])->name('admin.rekap.tilangan');

    // Rekap Tilangan PDF
    Route::get('rekap-tilangan/cetak/{bulan}/{tahun}', [MinusanController::class, 'rekapTilanganPdf'])->name('admin.rekap.tilangan.cetak');

    // Rekap Tilangan Excel
    Route::get('rekap-tilangan/excel/{bulan}/{tahun}', [MinusanController::class, 'rekapTilanganExcel'])->name('admin.rekap.tilangan.excel');

    // Report Khusus
    Route::get('report-khusus', [ReportKhususController::class, 'index'])->name('admin.report.khusus.index');
    Route::post('report-khusus', [ReportKhususController::class, 'store'])->name('admin.report.khusus.store');
    Route::put('report-khusus/{id}', [ReportKhususController::class, 'update'])->name('admin.report.khusus.update');
    Route::delete('report-khusus/{id}', [ReportKhususController::class, 'destroy'])->name('admin.report.khusus.destroy');

    // Transaksi
    Route::get('transaksi/upload', [TransaksiController::class, 'uploadForm'])->name('transaksi.upload');
    Route::post('transaksi/import', [TransaksiController::class, 'importCsv'])->name('transaksi.import');
    Route::get('transaksi/analisis', [TransaksiController::class, 'analisis'])->name('transaksi.analisis');
    Route::get('transaksi/export', [TransaksiController::class, 'exportAnalisis'])->name('transaksi.export');
    Route::delete('transaksi/clear', [TransaksiController::class, 'clearData'])->name('transaksi.clear');
    
    // Import status polling
    Route::get('imports/status', [ImportStatusController::class, 'status'])->name('imports.status');

    // Middleware isAdmin
    Route::middleware('isAdmin')->group(function () {
        // User
        Route::get('user', [UserController::class, 'index'])->name('user');

        // User Create
        Route::get('user/create', [UserController::class, 'create'])->name('userCreate');

        // Store Data User
        Route::post('user/store', [UserController::class, 'store'])->name('userStore');

        // User Edit
        Route::get('user/edit/{id}', [UserController::class, 'edit'])->name('userEdit');

        // User Update
        Route::post('user/update/{id}', [UserController::class, 'update'])->name('userUpdate');

        // User Hapus Destroy
        Route::delete('user/destroy/{id}', [UserController::class, 'destroy'])->name('userDestroy');

        // User Excel
        Route::get('user/excel', [UserController::class, 'excel'])->name('userExcel');

        // User PDF
        Route::get('user/pdf', [UserController::class, 'pdf'])->name('userPdf');

        // Create Minusan
        Route::get('minusan/create', [MinusanController::class, 'create'])->name('minusanCreate');

        // Store Data Minusan
        Route::post('minusan/store', [MinusanController::class, 'store'])->name('minusanStore');

        // Minusan Edit
        Route::get('minusan/edit{id}', [MinusanController::class, 'edit'])->name('minusanEdit');

        // Minusan Update
        Route::post('minusan/update{id}', [MinusanController::class, 'update'])->name('minusanUpdate');

        // Minusan Hapus Destroy
        Route::delete('minusan/destroy{id}', [MinusanController::class, 'destroy'])->name('minusanDestroy');

        // Minusan Excel
        Route::get('minusan/excel', [MinusanController::class, 'excel'])->name('minusanExcel');

        // Minusan PDF
        Route::get('minusan/pdf', [MinusanController::class, 'pdf'])->name('minusanPdf');

        // Rekap Bulanan Excel
        Route::get('rekap-bulanan/excel', [MinusanController::class, 'rekapExcel'])->name('rekap.excel');

        // Rekap Bulanan PDF
        Route::get('rekap-bulanan/pdf', [MinusanController::class, 'rekapPdf'])->name('rekap.pdf');
        
    });
});
