<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RequestOtpRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\AuthUserResource;
use App\Http\Resources\ProfileResource;
use App\Services\AuthService;
use App\Services\WhatsAppGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->loginDenganPassword(
            $request->validated('email'),
            $request->validated('password')
        );

        return ApiResponse::success([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'user' => new AuthUserResource($user),
        ]);
    }

    public function requestOTP(RequestOtpRequest $request): JsonResponse
    {
        $nomorAsli = $request->validated('phone');
        $nomorBersih = WhatsAppGateway::bersihkanNomor($nomorAsli);

        if (! $this->authService->cariTokoUntukRequestOtp($nomorBersih, $nomorAsli)) {
            return ApiResponse::error('Nomor telepon tidak terdaftar', 404);
        }

        try {
            $this->authService->kirimOtp($nomorBersih);
        } catch (RuntimeException $e) {
            return ApiResponse::error('Gagal mengirim OTP: ' . $e->getMessage(), 500);
        }

        return ApiResponse::success([
            'phone' => $nomorBersih,
            'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
        ]);
    }

    public function verifyOTP(VerifyOtpRequest $request): JsonResponse
    {
        $nomorBersih = WhatsAppGateway::bersihkanNomor($request->validated('phone'));

        if (! $this->authService->verifikasiOtp($nomorBersih, $request->validated('otp_code'))) {
            return ApiResponse::error('Kode OTP tidak valid atau sudah kadaluarsa', 400);
        }

        $toko = $this->authService->cariTokoAktif($nomorBersih);

        if (! $toko) {
            return ApiResponse::error('Toko tidak ditemukan', 404);
        }

        $user = $this->authService->userUntukToko($toko);

        return ApiResponse::success([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'user' => new AuthUserResource($user),
        ], 'Login berhasil');
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new ProfileResource($request->user()->load('toko.sales'))
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->toko) {
            return ApiResponse::error('Toko tidak ditemukan', 404);
        }

        $user = $this->authService->perbaruiProfil($user, $request->validated());

        return ApiResponse::success(
            new ProfileResource($user->load('toko.sales')),
            'Profil berhasil diupdate'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }
}
