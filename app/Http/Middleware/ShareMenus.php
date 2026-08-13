<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Services\NavigasiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareMenus
{
    public function __construct(private readonly NavigasiService $navigasiService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user instanceof AdminUser) {
            $project = TentukanProjectAktif::dariRequest($request);

            Inertia::share([
                'menus' => $this->navigasiService->menuUntuk($user, $project?->id),
                'projects' => $this->navigasiService->projectUntuk($user),
                'projectAktif' => $project?->kode,
                'izin' => $this->navigasiService->izinUntuk($user, $request->route()?->getName()),
            ]);
        }

        return $next($request);
    }
}
