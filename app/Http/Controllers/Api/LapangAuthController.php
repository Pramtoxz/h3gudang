<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\LapangAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk autentikasi operator lapangan (HP).
 */
class LapangAuthController extends Controller
{
    public function __construct(
        private readonly LapangAuthService $authService,
    ) {
    }

    /**
     * Login operator lapangan dengan kode akses 6 digit yang digenerate oleh Kepala Gudang.
     */
    public function loginKode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|size:6',
        ]);

        $result = $this->authService->loginWithCode($validated['kode']);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Kode akses tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $result['token'],
                'user' => $result['user'],
            ],
        ]);
    }

    /**
     * Login operator lapangan dengan email/password (legacy/cadangan).
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $result = $this->authService->login(
            $validated['email'],
            $validated['password'],
            $validated['remember_me'] ?? true
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Email atau password salah.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $result['token'],
                'user' => $result['user'],
            ],
        ]);
    }

    /**
     * Logout ?" delete current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }
}
