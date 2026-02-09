<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReimburseController;
use App\Http\Controllers\Api\AdminReimburseController;

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
    });
});
