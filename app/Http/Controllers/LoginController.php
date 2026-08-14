<?php

namespace App\Http\Controllers;

use App\Services\LoginAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(private readonly LoginAdminService $loginAdminService)
    {
    }

    public function create(): Response
    {
        return Inertia::render('auth/login');
    }

    public function store(Request $request)
    {
        $kredensial = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha_token' => 'required|string',
        ]);

        if (! $this->recaptchaLolos($kredensial['recaptcha_token'])) {
            return back()->withErrors([
                'email' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        $galat = $this->loginAdminService->masuk(
            $request,
            $kredensial['email'],
            $kredensial['password'],
            $request->boolean('remember'),
        );

        if ($galat !== null) {
            return back()->withErrors(['email' => $galat])->onlyInput('email');
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request)
    {
        $this->loginAdminService->keluar($request);

        return redirect('/');
    }

    private function recaptchaLolos(string $token): bool
    {
        try {
            $tanggapan = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
            ]);

            return (bool) ($tanggapan->json()['success'] ?? false);
        } catch (\Exception $e) {
            Log::warning('Verifikasi reCAPTCHA gagal: '.$e->getMessage());

            return false;
        }
    }
}
