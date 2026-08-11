<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePinRequest;
use App\Http\Requests\Api\SetupPinRequest;
use App\Http\Requests\Api\VerifyPinRequest;
use App\Services\CollectionPinService;
use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CollectionController extends Controller
{
    public function __construct(
        private readonly CollectionService $collectionService,
        private readonly CollectionPinService $pinService,
    ) {
    }

    public function checkPinStatus(Request $request): JsonResponse
    {
        $sudahDiatur = $this->pinService->sudahDiatur($request->user());

        return ApiResponse::success([
            'has_pin' => $sudahDiatur,
            'requires_setup' => ! $sudahDiatur,
        ]);
    }

    public function setupPin(SetupPinRequest $request): JsonResponse
    {
        $this->pinService->atur($request->user(), $request->validated('pin'));

        return ApiResponse::success(['message' => 'PIN berhasil diatur']);
    }

    public function changePin(ChangePinRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->pinService->cocok($user, $request->validated('old_pin'))) {
            return ApiResponse::error('PIN lama salah', 403);
        }

        $this->pinService->atur($user, $request->validated('new_pin'));

        return ApiResponse::success(['message' => 'PIN berhasil diubah']);
    }

    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->pinService->sudahDiatur($user)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN belum diatur',
                'requires_setup' => true,
            ], 403);
        }

        if (! $this->pinService->cocok($user, $request->validated('pin'))) {
            return ApiResponse::error('PIN salah', 403);
        }

        return ApiResponse::success(['message' => 'PIN valid', 'verified' => true]);
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->collectionService->daftar(
            $request->user()->fk_toko,
            $request->only(['page', 'per_page', 'dari', 'sampai'])
        ));
    }

    public function summary(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->collectionService->ringkasan($request->user()->fk_toko)
        );
    }

    public function reminders(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'reminders' => $this->collectionService->pengingat($request->user()->fk_toko),
        ]);
    }

    public function detail(Request $request, string $noFaktur): JsonResponse
    {
        try {
            return ApiResponse::success(
                $this->collectionService->detailFaktur($request->user()->fk_toko, $noFaktur)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
