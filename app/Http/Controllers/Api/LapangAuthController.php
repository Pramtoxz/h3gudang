<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\LapangAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk autentikasi operator lapangan (HP).
 * 
 * Endpoint ini berbeda dari `AuthController` untuk toko (yang pakai OTP WhatsApp).
 * Operator lapangan pakai email + password seperti admin desktop.
 * 
 * Response format: {
 *   success: bool,
 *   data: {
 *     token: string,  // Bearer token Sanctum
 *     user: { id, email, name?, area? }
 *   }
 * }
 */
class LapangAuthController extends Controller
{
    public function __construct(
        private readonly LapangAuthService $authService,
    ) {
    }

    /**
     * Login operator lapangan dengan email/password.
     * Return Bearer token untuk API requests selanjutnya.
     * 
     * Token tidak pernah expire selama operator tidak logout.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'boolean', // Optional: true = long-lived token, false = short session
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
     * Logout — delete current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }
}
