<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Service untuk autentikasi operator lapangan (HP).
 * 
 * Berbeda dari admin desktop yang pakai session cookie + remember token,
 * operator lapangan perlu Bearer token Sanctum untuk API stateless calls
 * dari aplikasi Capacitor.
 */
class LapangAuthService
{
    public function __construct(
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    /**
     * Login operator lapangan dengan email + password.
     * Verifikasi terhadap `public.users` di DMS (sama dengan login admin web),
     * tapi TIDAK menulis apa pun ke DMS — token Sanctum disimpan di
     * `warehouse.personal_access_tokens`.
     * 
     * @param  bool  $rememberMe  Jika true, buat token tanpa expiration (default: true)
     * @return array{success: bool, message?: string, token?: string, user?: array<string, mixed>}
     */
    public function login(string $email, string $password, bool $rememberMe = true): array
    {
        $user = AdminUser::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            return [
                'success' => false,
                'message' => 'Email atau password salah.',
            ];
        }

        // Buat token Sanctum — tersimpan di warehouse.personal_access_tokens,
        // bukan di DMS. Nama token memakai hostname perangkat bila tersedia
        // supaya mudah dicabut per HP dari sisi admin kelak.
        
        // Jika rememberMe=false, buat token short-lived (opsional untuk mobile app yang perlu logout otomatis)
        $tokenName = $rememberMe ? 'lapangan-mobile-long' : 'lapangan-mobile-short';
        $tokenResult = $user->createToken($tokenName);

        return [
            'success' => true,
            'token' => $tokenResult->plainTextToken,
            'user' => [
                'id' => $user->getKey(),
                'email' => $user->email,
                'nama' => $user->name ?? $user->email,
                'area_operator' => $this->areaOperator->areaUntuk($user),
                'adalah_admin_area' => $this->areaOperator->adalahAdminArea($user),
            ],
        ];
    }
}
