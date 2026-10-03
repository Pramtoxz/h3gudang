<?php

use App\Http\Controllers\Picking\AksesAreaController;
use App\Http\Controllers\Picking\ChannelController;
use App\Http\Controllers\Picking\FinalCheckController;
use App\Http\Controllers\Picking\KodeAksesController;
use App\Http\Controllers\Picking\LokasiRakController;
use App\Http\Controllers\Picking\PickingPartController;
use App\Http\Controllers\Picking\StoringPartController;
use Illuminate\Support\Facades\Route;

/**
 * Prefix `picking`, nama `picking.`, dan middleware auth + check.menu.access
 * dipasang oleh routes/web.php.
 *
 * Layar operator lapangan tidak ada di sini ?" itu dilayani aplikasi Capacitor
 * terpisah lewat routes/api/lapangan.php.
 */

Route::prefix('channel')->name('channel.')->group(function () {
    Route::get('/', [ChannelController::class, 'index'])->name('index');
    Route::post('/', [ChannelController::class, 'store'])->name('store');
    Route::put('{channel}', [ChannelController::class, 'update'])->name('update');
    Route::delete('{channel}', [ChannelController::class, 'destroy'])->name('destroy');
});

Route::prefix('lokasi-rak')->name('lokasi-rak.')->group(function () {
    Route::get('/', [LokasiRakController::class, 'index'])->name('index');
    Route::post('/', [LokasiRakController::class, 'store'])->name('store');
    Route::delete('massal', [LokasiRakController::class, 'destroyMassal'])->name('destroy-massal');
    Route::put('{lokasiRak}', [LokasiRakController::class, 'update'])->name('update');
    Route::delete('{lokasiRak}', [LokasiRakController::class, 'destroy'])->name('destroy');
});

Route::prefix('picking-part')->name('picking-part.')->group(function () {
    Route::get('/', [PickingPartController::class, 'index'])->name('index');
    Route::get('/detail', [PickingPartController::class, 'detail'])->name('detail');
    Route::post('/update-status', [PickingPartController::class, 'updateStatus'])->name('update-status');
    Route::post('/kartustok', [PickingPartController::class, 'kartustok'])->name('kartustok');
    Route::delete('/items/{id}', [PickingPartController::class, 'hapusItem'])->name('hapus-item');
    Route::delete('/', [PickingPartController::class, 'destroy'])->name('destroy');
});

Route::prefix('final-check')->name('final-check.')->group(function () {
    Route::get('/', [FinalCheckController::class, 'index'])->name('index');
    Route::get('/detail', [FinalCheckController::class, 'detail'])->name('detail');
    Route::post('/', [FinalCheckController::class, 'simpan'])->name('store');
});

Route::prefix('akses-area')->name('akses-area.')->group(function () {
    Route::get('/', [AksesAreaController::class, 'index'])->name('index');
    Route::post('/', [AksesAreaController::class, 'store'])->name('store');
    Route::put('{aksesArea}', [AksesAreaController::class, 'update'])->name('update');
    Route::delete('{aksesArea}', [AksesAreaController::class, 'destroy'])->name('destroy');
});

Route::prefix('kode-akses')->name('kode-akses.')->group(function () {
    Route::get('/', [KodeAksesController::class, 'index'])->name('index');
    Route::post('/', [KodeAksesController::class, 'store'])->name('store');
});

Route::prefix('storing-part')->name('storing-part.')->group(function () {
    Route::get('/', [StoringPartController::class, 'index'])->name('index');
    Route::get('/detail', [StoringPartController::class, 'detail'])->name('detail');
    Route::post('/update-status', [StoringPartController::class, 'updateStatus'])->name('update-status');
    Route::post('/sync', [StoringPartController::class, 'sync'])->name('sync');
});

