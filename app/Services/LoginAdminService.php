<?php

namespace App\Services;

use App\Auth\AdminUserProvider;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAdminService
{
    public function __construct(private readonly NavigasiService $navigasiService)
    {
    }

    /**
     * Mengembalikan pesan galat, atau null bila login berhasil.
     */
    public function masuk(Request $request, string $email, string $password, bool $ingatSaya): ?string
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $ingatSaya)) {
            return 'Email atau password salah.';
        }

        $user = Auth::user();

        if (! $user instanceof AdminUser || ! $this->navigasiService->punyaAkses($user)) {
            $this->keluar($request);

            return 'Anda tidak punya hak untuk akses ini.';
        }

        $request->session()->regenerate();

        return null;
    }

    /**
     * Baris remember token dihapus, bukan sekadar diputar, supaya tidak ada
     * sisa baris tanpa pemilik di `warehouse.admin_remember_tokens`.
     */
    public function keluar(Request $request): void
    {
        $user = Auth::user();

        Auth::logout();

        if ($user instanceof AdminUser) {
            AdminUserProvider::cabutToken($user->email);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
