<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\InternalController;
use App\Http\Controllers\Api\LapangAuthController;
use App\Http\Controllers\Api\LapangDoController;
use App\Http\Controllers\Api\LapangWorkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOTP']);
});

Route::post('/auth/request-otp', [AuthController::class, 'requestOTP'])->middleware('throttle:otp');

// ============================================
// Picking Lapangan (API token-based)
// ============================================

// Login operator lapangan — tidak perlu token, nanti dapat token baru
Route::post('/lapangan/auth/login', [LapangAuthController::class, 'login'])
    ->middleware('throttle:lapangan-login');

// Semua endpoint lainnya butuh token
Route::prefix('lapangan')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [LapangAuthController::class, 'logout']);

    // DO list & work item
    Route::get('/do', [LapangDoController::class, 'index']);
    Route::prefix('do')->group(function () {
        Route::get('{fkDo}/parts', [LapangWorkController::class, 'parts'])
            ->where('fkDo', '.*');
    });

    // Update status part dan kartu stok
    Route::post('/part/update-status', [LapangWorkController::class, 'updateStatus']);
    Route::post('/kartustok', [LapangWorkController::class, 'simpanKartuStok']);
});

Route::post('/internal/refresh-cache', [InternalController::class, 'refreshCollectionCache']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile'])->middleware('verify.collection.pin');
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/parts', [PartController::class, 'index']);
    Route::get('/parts/{partNumber}', [PartController::class, 'show']);
    Route::get('/parts/{partNumber}/stock', [PartController::class, 'checkStock']);

    Route::get('/filters/vehicle-types', [FilterController::class, 'getVehicleTypes']);
    Route::get('/filters/categories', [FilterController::class, 'getCategories']);

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::post('/checkout', [OrderController::class, 'checkout'])->middleware('throttle:checkout');
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    Route::get('/orders', [OrderController::class, 'history']);
    Route::get('/orders/{noSo}/back-order', [OrderController::class, 'backOrder'])->where('noSo', '.*');
    Route::get('/orders/{noSo}', [OrderController::class, 'detail'])->where('noSo', '.*');

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/my-achievement', [CampaignController::class, 'myAchievement']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);

    Route::prefix('collections')->group(function () {
        Route::get('/pin/status', [CollectionController::class, 'checkPinStatus']);
        Route::post('/pin/setup', [CollectionController::class, 'setupPin']);
        Route::post('/pin/change', [CollectionController::class, 'changePin']);
        Route::post('/pin/verify', [CollectionController::class, 'verifyPin']);

        Route::middleware(['verify.collection.pin', 'user.has.shop'])->group(function () {
            Route::get('/', [CollectionController::class, 'index']);
            Route::get('/summary', [CollectionController::class, 'summary']);
            Route::get('/reminders', [CollectionController::class, 'reminders']);
            Route::get('/{noFaktur}', [CollectionController::class, 'detail'])->where('noFaktur', '.*');
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/test', [NotificationController::class, 'sendTest']);
        Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);
        Route::post('/update-token', [NotificationController::class, 'updateFcmToken']);
    });

    Route::post('/fcm/update-token', [NotificationController::class, 'updateFcmToken']);
});
