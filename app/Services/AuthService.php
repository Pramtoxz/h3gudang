<?php

namespace App\Services;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly OTPService $otpService)
    {
    }

    public function loginDenganPassword(string $email, string $password): User
    {
        $user = User::with('toko')->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $toko = $user->toko;

        if ($toko && ! $toko->toko_active) {
            throw ValidationException::withMessages([
                'email' => ['Toko Anda sudah tidak aktif. Silakan hubungi admin.'],
            ]);
        }

        return $user;
    }

    /**
     * Pencarian saat request OTP mencocokkan nomor asli maupun nomor yang sudah dibersihkan,
     * karena format no_telp di database tidak seragam.
     */
    public function cariTokoUntukRequestOtp(string $nomorBersih, string $nomorAsli): ?Toko
    {
        return Toko::where('toko_active', true)
            ->where(function ($query) use ($nomorBersih, $nomorAsli) {
                $query->where('no_telp', 'LIKE', '%' . $nomorBersih . '%')
                    ->orWhere('no_telp', 'LIKE', '%' . $nomorAsli . '%');
            })
            ->first();
    }

    public function cariTokoAktif(string $nomorBersih): ?Toko
    {
        return Toko::where('toko_active', true)
            ->where('no_telp', 'LIKE', '%' . $nomorBersih . '%')
            ->first();
    }

    public function kirimOtp(string $nomorBersih): void
    {
        $kode = $this->otpService->generateOTP($nomorBersih, 'login');

        $this->otpService->sendOTP($nomorBersih, $kode);
    }

    public function verifikasiOtp(string $nomorBersih, string $kode): bool
    {
        return $this->otpService->verifyOTP($nomorBersih, $kode);
    }

    public function userUntukToko(Toko $toko): User
    {
        return User::firstOrCreate(
            ['fk_toko' => $toko->kd_toko],
            [
                'name' => $toko->toko,
                'email' => $toko->kd_toko . '@menara-agung.com',
                'password' => Hash::make(uniqid()),
                'role' => 'dealer',
            ]
        );
    }

    public function perbaruiProfil(User $user, array $data): User
    {
        $toko = $user->toko;

        if (filled($data['email'] ?? null)) {
            $user->email = $data['email'];
        }

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $kolomToko = array_filter([
            'no_telp' => $data['phone'] ?? null,
            'alamat' => $data['address'] ?? null,
            'npwp' => $data['npwp'] ?? null,
        ], fn ($nilai) => filled($nilai));

        if ($kolomToko && $toko) {
            $toko->fill($kolomToko)->save();
            $toko->refresh();
        }

        return $user->refresh();
    }
}
