<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Services\NavigasiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function __construct(private readonly NavigasiService $navigasiService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user instanceof AdminUser) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $namaRoute = $request->route()?->getName();

        if (! $namaRoute) {
            return $next($request);
        }

        if (! $this->navigasiService->bolehMembuka($user, $namaRoute, $request->method())) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
