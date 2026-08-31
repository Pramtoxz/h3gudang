<?php

use App\Http\Controllers\Pmo\DashboardController;
use App\Http\Controllers\Pmo\SalesSpvController;
use App\Http\Controllers\Pmo\SalesSpvExcelController;
use App\Http\Controllers\Pmo\TokoController;
use App\Http\Controllers\Pmo\TokoExcelController;
use App\Http\Controllers\Pmo\TokoPinController;
use Illuminate\Support\Facades\Route;

/**
 * Prefix `pmo`, nama `pmo.`, dan middleware auth + check.menu.access
 * dipasang oleh routes/web.php.
 *
 * Route statis (export, import, create) wajib didaftarkan sebelum {param}
 * supaya tidak tertelan.
 */

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('toko')->name('toko.')->group(function () {
    Route::get('/', [TokoController::class, 'index'])->name('index');
    Route::get('export', [TokoExcelController::class, 'export'])->name('export');
    Route::post('import', [TokoExcelController::class, 'import'])->name('import');
    Route::get('create', [TokoController::class, 'create'])->name('create');
    Route::post('/', [TokoController::class, 'store'])->name('store');
    Route::get('{toko}', [TokoController::class, 'show'])->name('show');
    Route::get('{toko}/edit', [TokoController::class, 'edit'])->name('edit');
    Route::put('{toko}', [TokoController::class, 'update'])->name('update');
    Route::delete('{toko}', [TokoController::class, 'destroy'])->name('destroy');
    Route::post('{toko}/reset-pin', TokoPinController::class)->name('reset-pin');
});

Route::prefix('sales-spv')->name('sales-spv.')->group(function () {
    Route::get('/', [SalesSpvController::class, 'index'])->name('index');
    Route::get('export', [SalesSpvExcelController::class, 'export'])->name('export');
    Route::post('import', [SalesSpvExcelController::class, 'import'])->name('import');
    Route::get('{salesSupervisor}', [SalesSpvController::class, 'show'])->name('show');
    Route::delete('{salesSupervisor}', [SalesSpvController::class, 'destroy'])->name('destroy');
});
