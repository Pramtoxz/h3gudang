<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Penghubung route API
|--------------------------------------------------------------------------
|
| Sama seperti routes/web.php: berkas ini hanya memetakan tiap konsumen API
| ke berkasnya di routes/api/. Seluruh route di sini otomatis berawalan
| `api/` (diatur bootstrap/app.php).
|
| PMO tidak diberi prefix karena path `/api/*`-nya adalah kontrak beku dengan
| aplikasi mobile produksi yang tidak ikut diperbarui saat cutover.
|
*/

Route::group([], base_path('routes/api/pmo.php'));

Route::prefix('lapangan')->group(base_path('routes/api/lapangan.php'));
