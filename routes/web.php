<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Untuk Adek Adek Q yang akan Melanjutkan Project Globok Ini
|--------------------------------------------------------------------------
|
| Berkas ini hanya memuat route lintas project (beranda, login, logout) dan
| memetakan tiap project ke berkasnya di routes/web/. Prefix, nama, dan
| middleware sengaja ditulis di sini supaya penjagaan tiap project bisa
| dibaca sekali lihat tanpa membuka berkasnya satu per satu.
|
| Menambah project baru: buat routes/web/<project>.php, lalu daftarkan satu
| blok di bawah.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'check.menu.access'])
    ->prefix('pmo')
    ->name('pmo.')
    ->group(base_path('routes/web/pmo.php'));

Route::middleware(['auth', 'check.menu.access'])
    ->prefix('picking')
    ->name('picking.')
    ->group(base_path('routes/web/picking.php'));

Route::middleware(['auth', 'user.is.it'])
    ->prefix('pengaturan')
    ->name('pengaturan.')
    ->group(base_path('routes/web/pengaturan.php'));
