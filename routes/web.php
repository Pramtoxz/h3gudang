<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
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

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['check.menu.access'])->group(function () {
        Route::get('settings/menus', [MenuController::class, 'index'])->name('settings.menus.index');
        Route::post('settings/menus', [MenuController::class, 'store'])->name('settings.menus.store');
        Route::put('settings/menus/{id}', [MenuController::class, 'update'])->name('settings.menus.update');
        Route::delete('settings/menus/{id}', [MenuController::class, 'destroy'])->name('settings.menus.destroy');
    });
});
