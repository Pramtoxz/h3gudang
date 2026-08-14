<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Services\LoginAdminService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Login untuk operator yang memakai HP di lengan lewat WebView. Tanpa
 * reCAPTCHA — penahan brute-force-nya rate limiter `login-lapangan` yang
 * dipasang di route.
 */
class LoginLapanganController extends Controller
{
    public function __construct(private readonly LoginAdminService $loginAdminService)
    {
    }

    public function create(): Response
    {
        return Inertia::render('picking/lapangan/Masuk');
    }

    public function store(Request $request)
    {
        $kredensial = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $galat = $this->loginAdminService->masuk(
            $request,
            $kredensial['email'],
            $kredensial['password'],
            $request->boolean('remember', true),
        );

        if ($galat !== null) {
            return back()->withErrors(['email' => $galat])->onlyInput('email');
        }

        return redirect()->intended(route('home'));
    }
}
