<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Http\Controllers\Pengaturan\MenuController;
use App\Http\Controllers\Picking\AksesAreaController;
use App\Http\Controllers\Picking\ChannelController;
use App\Http\Controllers\Picking\LokasiRakController;
use App\Http\Controllers\Picking\PickingPartController;
use App\Http\Controllers\Pmo\DashboardController;
use App\Http\Controllers\Pmo\SalesSpvController;
use App\Http\Controllers\Pmo\SalesSpvExcelController;
use App\Http\Controllers\Pmo\TokoController;
use App\Http\Controllers\Pmo\TokoExcelController;
use App\Http\Controllers\Pmo\TokoPinController;
use App\Http\Middleware\TentukanProjectAktif;
use App\Models\AdminUser;
use App\Services\NavigasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request, NavigasiService $navigasi) {
    $user = Auth::user();

    if (! $user instanceof AdminUser) {
        return redirect()->route('login');
    }

    $project = collect($navigasi->projectUntuk($user));

    if ($project->isEmpty()) {
        abort(403, 'Belum ada project yang bisa Anda buka.');
    }

    $terakhir = TentukanProjectAktif::kodeTerakhir($request);
    $tujuan = $project->firstWhere('kode', $terakhir) ?? $project->first();
    $url = $navigasi->urlAwal($user, $tujuan['id']);

    if (! $url) {
        abort(403, 'Belum ada menu yang bisa Anda buka.');
    }

    return redirect($url);
})->name('home');

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
    ->group(function () {
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

Route::middleware(['auth', 'check.menu.access'])
    ->prefix('picking')
    ->name('picking.')
    ->group(function () {
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

        Route::prefix('akses-area')->name('akses-area.')->group(function () {
            Route::get('/', [AksesAreaController::class, 'index'])->name('index');
            Route::post('/', [AksesAreaController::class, 'store'])->name('store');
            Route::put('{aksesArea}', [AksesAreaController::class, 'update'])->name('update');
            Route::delete('{aksesArea}', [AksesAreaController::class, 'destroy'])->name('destroy');
        });
    });

Route::middleware(['auth', 'user.is.it'])
    ->prefix('pengaturan')
    ->name('pengaturan.')
    ->group(function () {
        Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
        Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

        Route::get('hak-akses', [HakAksesController::class, 'index'])->name('hak-akses.index');
        Route::put('hak-akses', [HakAksesController::class, 'update'])->name('hak-akses.update');
    });
