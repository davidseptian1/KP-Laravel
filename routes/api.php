<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReimburseController;
use App\Http\Controllers\Api\AdminReimburseController;
use App\Http\Controllers\Api\AdminDataRequestController;
use App\Http\Controllers\Api\AdminLoanRequestController;
use App\Http\Controllers\Api\AdminDepositController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reimburse', [ReimburseController::class, 'index']);
    Route::post('/reimburse', [ReimburseController::class, 'store']);

    Route::middleware('admin.api')->group(function () {
        Route::get('/admin/reimburse', [AdminReimburseController::class, 'index']);
        Route::get('/admin/reimburse/{id}', [AdminReimburseController::class, 'show']);
        Route::put('/admin/reimburse/{id}', [AdminReimburseController::class, 'update']);
        Route::get('/admin/reimburse/{id}/download', [AdminReimburseController::class, 'download']);

        Route::get('/admin/monitoring/data-request', [AdminDataRequestController::class, 'index']);
        Route::get('/admin/monitoring/data-request/{id}', [AdminDataRequestController::class, 'show']);
        Route::put('/admin/monitoring/data-request/{id}', [AdminDataRequestController::class, 'update']);
        Route::delete('/admin/monitoring/data-request/{id}', [AdminDataRequestController::class, 'destroy']);

        Route::get('/admin/monitoring/loan-request', [AdminLoanRequestController::class, 'index']);
        Route::get('/admin/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'show']);
        Route::put('/admin/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'update']);
        Route::delete('/admin/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'destroy']);

        Route::get('/admin/monitoring/deposit', [AdminDepositController::class, 'index']);
        Route::get('/admin/monitoring/deposit/{id}', [AdminDepositController::class, 'show']);
        Route::put('/admin/monitoring/deposit/{id}/details', [AdminDepositController::class, 'updateDetails']);
        Route::put('/admin/monitoring/deposit/{id}/status', [AdminDepositController::class, 'updateStatus']);
        Route::delete('/admin/monitoring/deposit/{id}', [AdminDepositController::class, 'destroy']);
    });
});

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::get('/monitoring/data-request', [AdminDataRequestController::class, 'index']);
    Route::get('/monitoring/data-request/{id}', [AdminDataRequestController::class, 'show']);
    Route::put('/monitoring/data-request/{id}', [AdminDataRequestController::class, 'update']);
    Route::delete('/monitoring/data-request/{id}', [AdminDataRequestController::class, 'destroy']);

    Route::get('/monitoring/loan-request', [AdminLoanRequestController::class, 'index']);
    Route::get('/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'show']);
    Route::put('/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'update']);
    Route::delete('/monitoring/loan-request/{id}', [AdminLoanRequestController::class, 'destroy']);

    Route::get('/monitoring/deposit', [AdminDepositController::class, 'index']);
    Route::get('/monitoring/deposit/{id}', [AdminDepositController::class, 'show']);
    Route::put('/monitoring/deposit/{id}/details', [AdminDepositController::class, 'updateDetails']);
    Route::put('/monitoring/deposit/{id}/status', [AdminDepositController::class, 'updateStatus']);
    Route::delete('/monitoring/deposit/{id}', [AdminDepositController::class, 'destroy']);
});
