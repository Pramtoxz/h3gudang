<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SalesSpvController;
use App\Http\Controllers\Admin\SalesSpvExcelController;
use App\Http\Controllers\Admin\TokoController;
use App\Http\Controllers\Admin\TokoExcelController;
use App\Http\Controllers\Admin\TokoPinController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'check.menu.access'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
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
    });

    Route::get('settings/menus', [MenuController::class, 'index'])->name('settings.menus.index');
    Route::post('settings/menus', [MenuController::class, 'store'])->name('settings.menus.store');
    Route::put('settings/menus/{id}', [MenuController::class, 'update'])->name('settings.menus.update');
    Route::delete('settings/menus/{id}', [MenuController::class, 'destroy'])->name('settings.menus.destroy');
});
