<?php

use App\Http\Controllers\Api\LapangAuthController;
use App\Http\Controllers\Api\LapangDoController;
use App\Http\Controllers\Api\LapangStoringController;
use App\Http\Controllers\Api\LapangWorkController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login-kode', [LapangAuthController::class, 'loginKode'])
    ->middleware('throttle:5,1');

Route::post('/auth/login', [LapangAuthController::class, 'login'])
    ->middleware('throttle:lapangan-login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [LapangAuthController::class, 'logout']);

    Route::get('/do', [LapangDoController::class, 'index']);

    Route::get('/do/{fkDo}/parts', [LapangWorkController::class, 'parts'])
        ->where('fkDo', '.*');

    Route::post('/part/update-status', [LapangWorkController::class, 'updateStatus']);
    Route::post('/kartustok', [LapangWorkController::class, 'simpanKartuStok']);

    Route::get('/storing', [LapangStoringController::class, 'index']);
    Route::get('/storing/{noPenerimaan}/parts', [LapangStoringController::class, 'parts'])
        ->where('noPenerimaan', '.*');
    Route::post('/storing/simpan', [LapangStoringController::class, 'simpan']);
    Route::post('/storing/tandai-semua', [LapangStoringController::class, 'tandaiSemua']);
});

