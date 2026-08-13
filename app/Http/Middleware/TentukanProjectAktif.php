<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TentukanProjectAktif
{
    public const ATRIBUT = 'projectAktif';

    private const KUNCI_SESSION = 'project_terakhir';

    /**
     * Project aktif diturunkan dari segmen pertama URL, bukan dari session,
     * supaya alamat yang dibuka dan konteks yang tampil tidak pernah berbeda.
     * Session hanya mengingat pilihan terakhir untuk menentukan tujuan awal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $project = Project::query()
            ->where('kode', $request->segment(1))
            ->where('aktif', true)
            ->first();

        if ($project) {
            $request->attributes->set(self::ATRIBUT, $project);
            $request->session()->put(self::KUNCI_SESSION, $project->kode);
        }

        return $next($request);
    }

    public static function dariRequest(Request $request): ?Project
    {
        return $request->attributes->get(self::ATRIBUT);
    }

    public static function kodeTerakhir(Request $request): ?string
    {
        return $request->session()->get(self::KUNCI_SESSION);
    }
}
