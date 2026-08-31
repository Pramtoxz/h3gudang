<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TentukanProjectAktif;
use App\Models\AdminUser;
use App\Services\NavigasiService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Beranda `/` bukan halaman, melainkan pengalih: satu codebase melayani
 * beberapa project, jadi tujuannya bergantung pada project dan menu yang
 * dimiliki user.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request, NavigasiService $navigasi): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof AdminUser) {
            return redirect()->route('login');
        }

        $url = $navigasi->urlPendaratan($user, TentukanProjectAktif::kodeTerakhir($request));

        abort_if($url === null, 403, 'Belum ada project yang bisa Anda buka.');
        abort_if($url === '', 403, 'Belum ada menu yang bisa Anda buka.');

        return redirect($url);
    }
}
