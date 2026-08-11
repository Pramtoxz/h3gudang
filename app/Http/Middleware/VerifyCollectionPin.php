<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Services\CollectionPinService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCollectionPin
{
    public function __construct(private readonly CollectionPinService $pinService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthorized', 401);
        }

        if (! $this->pinService->sudahDiatur($user)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN belum diatur',
                'requires_setup' => true,
            ], 403);
        }

        $pin = $request->header('X-Collection-Pin') ?? $request->input('pin');

        if (! $pin) {
            return response()->json([
                'success' => false,
                'message' => 'PIN diperlukan',
                'requires_pin' => true,
            ], 403);
        }

        if (! $this->pinService->cocok($user, $pin)) {
            return ApiResponse::error('PIN salah', 403);
        }

        return $next($request);
    }
}
