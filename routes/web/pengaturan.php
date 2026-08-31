<?php

use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Http\Controllers\Pengaturan\MenuController;
use Illuminate\Support\Facades\Route;

/**
 * Halaman lintas project, khusus pengelola IT (`it = 't'`).
 *
 * Prefix `pengaturan`, nama `pengaturan.`, dan middleware auth + user.is.it
 * dipasang oleh routes/web.php.
 */

Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

Route::get('hak-akses', [HakAksesController::class, 'index'])->name('hak-akses.index');
Route::put('hak-akses', [HakAksesController::class, 'update'])->name('hak-akses.update');
