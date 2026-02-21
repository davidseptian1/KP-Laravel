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
use App\Http\Controllers\ReimburseWebController;
use App\Http\Controllers\AdminReimburseWebController;
use App\Http\Controllers\ReimburseFormController;
use App\Http\Controllers\AdminReimburseFormController;
use App\Http\Controllers\RecapDownloadController;
use App\Http\Controllers\DataRequest\AdminDataRequestController;
use App\Http\Controllers\DataRequest\AdminDataRequestFormController;
use App\Http\Controllers\DataRequest\DataRequestFormController;
use App\Http\Controllers\LoanRequest\AdminLoanRequestController;
use App\Http\Controllers\LoanRequest\AdminLoanRequestFormController;
use App\Http\Controllers\LoanRequest\LoanRequestFormController;
use App\Http\Controllers\AdminDepositController;
use App\Http\Controllers\AdminDepositFormController;
use App\Http\Controllers\DepositFormController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\DataMatrixController;



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

// Public Reimburse Form (no login required)
Route::get('reimburse/form/{token}', [ReimburseFormController::class, 'show'])->name('reimburse.form.show');
Route::post('reimburse/form/{token}', [ReimburseFormController::class, 'submit'])->name('reimburse.form.submit');

// Public Pengajuan Data Form (no login required)
Route::get('pengajuan-data/form/{token}', [DataRequestFormController::class, 'show'])->name('data-request.form.show');
Route::post('pengajuan-data/form/{token}', [DataRequestFormController::class, 'submit'])->name('data-request.form.submit');

// Public Peminjaman Barang Form (no login required)
Route::get('peminjaman-barang/form/{token}', [LoanRequestFormController::class, 'show'])->name('loan-request.form.show');
Route::post('peminjaman-barang/form/{token}', [LoanRequestFormController::class, 'submit'])->name('loan-request.form.submit');

// Public Deposit Form (no login required)
Route::get('deposit/form/{token}', [DepositFormController::class, 'show'])->name('deposit.form.show');
Route::post('deposit/form/{token}', [DepositFormController::class, 'submit'])->name('deposit.form.submit');

// Public signed download for recap PDFs
Route::get('recap/download/{file}', [RecapDownloadController::class, 'download'])
    ->middleware('signed')
    ->name('recap.download');

Route::get('reimburse/{id}/payment-proof', [AdminReimburseWebController::class, 'viewPaymentProof'])
    ->middleware('signed')
    ->name('public.reimburse.payment-proof');

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

    // Reimburse (User)
    Route::get('reimburse', [ReimburseWebController::class, 'index'])->name('reimburse.index');
    Route::post('reimburse', [ReimburseWebController::class, 'store'])->name('reimburse.store');

    // Deposit Request (User)
    Route::get('deposit/request', [DepositFormController::class, 'index'])->name('deposit.request.index');
    Route::post('deposit/request', [DepositFormController::class, 'storeFromRequestPage'])->name('deposit.request.store');
    Route::put('deposit/request/{id}/reply-penambahan', [DepositFormController::class, 'updateReplyPenambahan'])->name('deposit.request.reply.update');
    
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

        // Supplier Management
        Route::get('admin/supplier', [SupplierController::class, 'index'])->name('admin.supplier.index');
        Route::post('admin/supplier', [SupplierController::class, 'store'])->name('admin.supplier.store');
        Route::put('admin/supplier/{id}', [SupplierController::class, 'update'])->name('admin.supplier.update');
        Route::delete('admin/supplier/{id}', [SupplierController::class, 'destroy'])->name('admin.supplier.destroy');

        // Server Management
        Route::get('admin/server', [ServerController::class, 'index'])->name('admin.server.index');
        Route::post('admin/server', [ServerController::class, 'store'])->name('admin.server.store');
        Route::put('admin/server/{id}', [ServerController::class, 'update'])->name('admin.server.update');
        Route::delete('admin/server/{id}', [ServerController::class, 'destroy'])->name('admin.server.destroy');

        // Data Matrix
        Route::get('admin/data-matrix/tag-nomor-pasca-bayar', [DataMatrixController::class, 'tagNomorPascaBayar'])->name('admin.data-matrix.tag-pasca-bayar');
        Route::post('admin/data-matrix/tag-nomor-pasca-bayar/import', [DataMatrixController::class, 'importTagNomorPascaBayar'])->name('admin.data-matrix.tag-pasca-bayar.import');
        Route::post('admin/data-matrix/tag-nomor-pasca-bayar', [DataMatrixController::class, 'storeTagNomorPascaBayar'])->name('admin.data-matrix.tag-pasca-bayar.store');
        Route::put('admin/data-matrix/tag-nomor-pasca-bayar/{id}', [DataMatrixController::class, 'updateTagNomorPascaBayar'])->name('admin.data-matrix.tag-pasca-bayar.update');
        Route::delete('admin/data-matrix/tag-nomor-pasca-bayar/{id}', [DataMatrixController::class, 'destroyTagNomorPascaBayar'])->name('admin.data-matrix.tag-pasca-bayar.destroy');
        Route::get('admin/data-matrix/tag-pln-internet', [DataMatrixController::class, 'tagPlnInternet'])->name('admin.data-matrix.tag-pln-internet');
        Route::get('admin/data-matrix/tag-lainnya', [DataMatrixController::class, 'tagLainnya'])->name('admin.data-matrix.tag-lainnya');

        // Reimburse (Admin)
        Route::get('admin/reimburse', [AdminReimburseWebController::class, 'index'])->name('admin.reimburse.index');
        Route::put('admin/reimburse/{id}', [AdminReimburseWebController::class, 'update'])->name('admin.reimburse.update');
        Route::delete('admin/reimburse/{id}', [AdminReimburseWebController::class, 'destroy'])->name('admin.reimburse.destroy');
        Route::get('admin/reimburse/{id}/download', [AdminReimburseWebController::class, 'download'])->name('admin.reimburse.download');
        Route::get('admin/reimburse/{id}/bukti/{index?}', [AdminReimburseWebController::class, 'view'])->name('admin.reimburse.view');
        Route::post('admin/reimburse/{id}/send-wa', [AdminReimburseWebController::class, 'sendWa'])->name('admin.reimburse.sendWa');
        Route::get('admin/reimburse/{id}/payment-proof', [AdminReimburseWebController::class, 'viewPaymentProof'])->name('admin.reimburse.payment-proof');

        // Reimburse Form (Admin)
        Route::get('admin/reimburse/forms', [AdminReimburseFormController::class, 'index'])->name('admin.reimburse.forms');
        Route::post('admin/reimburse/forms', [AdminReimburseFormController::class, 'store'])->name('admin.reimburse.forms.store');
        Route::put('admin/reimburse/forms/{id}/toggle', [AdminReimburseFormController::class, 'toggle'])->name('admin.reimburse.forms.toggle');

        // Pengajuan Data (Admin)
        Route::get('admin/pengajuan-data', [AdminDataRequestController::class, 'index'])->name('admin.data-request.index');
        Route::put('admin/pengajuan-data/{id}', [AdminDataRequestController::class, 'update'])->name('admin.data-request.update');
        Route::delete('admin/pengajuan-data/{id}', [AdminDataRequestController::class, 'destroy'])->name('admin.data-request.destroy');
        Route::get('admin/pengajuan-data/{id}/view/{type}', [AdminDataRequestController::class, 'viewFile'])->name('admin.data-request.view');
        Route::get('admin/pengajuan-data/{id}/download/{type}', [AdminDataRequestController::class, 'downloadFile'])->name('admin.data-request.download');
        Route::post('admin/pengajuan-data/{id}/send-wa', [AdminDataRequestController::class, 'sendWa'])->name('admin.data-request.sendWa');

        // Pengajuan Data Form (Admin)
        Route::get('admin/pengajuan-data/forms', [AdminDataRequestFormController::class, 'index'])->name('admin.data-request.forms');
        Route::post('admin/pengajuan-data/forms', [AdminDataRequestFormController::class, 'store'])->name('admin.data-request.forms.store');
        Route::put('admin/pengajuan-data/forms/{id}/toggle', [AdminDataRequestFormController::class, 'toggle'])->name('admin.data-request.forms.toggle');

        // Peminjaman Barang (Admin)
        Route::get('admin/peminjaman-barang', [AdminLoanRequestController::class, 'index'])->name('admin.loan-request.index');
        Route::put('admin/peminjaman-barang/{id}', [AdminLoanRequestController::class, 'update'])->name('admin.loan-request.update');
        Route::delete('admin/peminjaman-barang/{id}', [AdminLoanRequestController::class, 'destroy'])->name('admin.loan-request.destroy');
        Route::post('admin/peminjaman-barang/{id}/send-wa', [AdminLoanRequestController::class, 'sendWa'])->name('admin.loan-request.sendWa');

        // Peminjaman Barang Form (Admin)
        Route::get('admin/peminjaman-barang/forms', [AdminLoanRequestFormController::class, 'index'])->name('admin.loan-request.forms');
        Route::post('admin/peminjaman-barang/forms', [AdminLoanRequestFormController::class, 'store'])->name('admin.loan-request.forms.store');
        Route::put('admin/peminjaman-barang/forms/{id}/toggle', [AdminLoanRequestFormController::class, 'toggle'])->name('admin.loan-request.forms.toggle');

        // Deposit (Admin)
        Route::get('admin/deposit/forms', [AdminDepositFormController::class, 'index'])->name('admin.deposit.forms');
        Route::post('admin/deposit/forms', [AdminDepositFormController::class, 'store'])->name('admin.deposit.forms.store');
        Route::put('admin/deposit/forms/{id}/toggle', [AdminDepositFormController::class, 'toggle'])->name('admin.deposit.forms.toggle');
        Route::get('admin/deposit/monitoring', [AdminDepositController::class, 'monitoring'])->name('admin.deposit.monitoring');
        Route::put('admin/deposit/{id}', [AdminDepositController::class, 'update'])->name('admin.deposit.update');
        Route::put('admin/deposit/{id}/details', [AdminDepositController::class, 'updateDetails'])->name('admin.deposit.update-details');
        Route::put('admin/deposit/{id}/status', [AdminDepositController::class, 'updateStatus'])->name('admin.deposit.update-status');
        Route::delete('admin/deposit/{id}', [AdminDepositController::class, 'destroy'])->name('admin.deposit.destroy');

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

    Route::get('admin/deposit/analysis', [AdminDepositController::class, 'analysis'])->name('admin.deposit.analysis');

});
