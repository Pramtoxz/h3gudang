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
            $daftarProject = $this->navigasiService->projectUntuk($user);
            $konteks = $this->projectKonteks($request, $daftarProject);

            Inertia::share([
                'menus' => $this->navigasiService->menuUntuk($user, $konteks['id'] ?? null),
                'projects' => $daftarProject,
                'projectAktif' => $konteks['kode'] ?? null,
                'izin' => $this->navigasiService->izinUntuk($user, $request->route()?->getName()),
            ]);
        }

        return $next($request);
    }

    private function projectKonteks(Request $request, array $daftarProject): ?array
    {
        $kode = TentukanProjectAktif::dariRequest($request)?->kode
            ?? TentukanProjectAktif::kodeTerakhir($request);

        foreach ($daftarProject as $project) {
            if ($project['kode'] === $kode) {
                return $project;
            }
        }

        return $daftarProject[0] ?? null;
    }
}
