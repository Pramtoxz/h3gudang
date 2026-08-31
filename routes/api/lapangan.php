<?php

use App\Http\Controllers\Api\LapangAuthController;
use App\Http\Controllers\Api\LapangDoController;
use App\Http\Controllers\Api\LapangWorkController;
use Illuminate\Support\Facades\Route;

/**
 * API operator lapangan — dikonsumsi aplikasi Capacitor di `picking-lapangan/`,
 * bukan halaman Inertia. Autentikasinya Bearer token Sanctum atas `AdminUser`.
 *
 * Prefix `lapangan` dipasang oleh routes/api.php.
 */

Route::post('/auth/login', [LapangAuthController::class, 'login'])
    ->middleware('throttle:lapangan-login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [LapangAuthController::class, 'logout']);

    Route::get('/do', [LapangDoController::class, 'index']);

    /**
     * Nomor DO berisi garis miring (`2026/018310/DO-OTHER`), jadi pembatasnya
     * harus `.*` supaya tidak terpecah jadi beberapa segmen path.
     */
    Route::get('/do/{fkDo}/parts', [LapangWorkController::class, 'parts'])
        ->where('fkDo', '.*');

    Route::post('/part/update-status', [LapangWorkController::class, 'updateStatus']);
    Route::post('/kartustok', [LapangWorkController::class, 'simpanKartuStok']);
});
