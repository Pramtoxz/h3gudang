<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendTestNotificationRequest;
use App\Http\Requests\Api\UpdateFcmTokenRequest;
use App\Http\Resources\NotificationResource;
use App\Services\FirebaseService;
use App\Services\NotificationQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationQueryService $notificationQueryService,
        private readonly FirebaseService $firebaseService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            NotificationResource::collection($this->notificationQueryService->daftar($request->user())),
            'Notifications retrieved successfully'
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ['count' => $this->notificationQueryService->jumlahBelumDibaca($request->user())],
            'Unread count retrieved successfully'
        );
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        return ApiResponse::success(
            new NotificationResource($this->notificationQueryService->tandaiDibaca($request->user(), $id)),
            'Notification marked as read'
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationQueryService->tandaiSemuaDibaca($request->user());

        return ApiResponse::success(null, 'All notifications marked as read');
    }

    public function updateFcmToken(UpdateFcmTokenRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $user->fcm_token = $request->validated('fcm_token');
            $user->save();
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui FCM token: ' . $e->getMessage(), ['user_id' => $user->id]);

            return ApiResponse::error('Failed to update FCM token', 500);
        }

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'fcm_token' => $user->fcm_token,
            'shop_code' => $user->fk_toko,
            'role' => $user->role,
        ], 'FCM token updated successfully');
    }

    public function sendTest(SendTestNotificationRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->fcm_token) {
            return ApiResponse::error('FCM token not found. Please update your FCM token first.', 400);
        }

        $hasil = $this->firebaseService->sendToDevice(
            $user->fcm_token,
            $request->validated('title'),
            $request->validated('body'),
            ['type' => 'test', 'notification_id' => '0']
        );

        if (! $hasil['success']) {
            return ApiResponse::error('Failed to send notification: ' . $hasil['message'], 500);
        }

        return ApiResponse::success($hasil, 'Test notification sent successfully');
    }
}
