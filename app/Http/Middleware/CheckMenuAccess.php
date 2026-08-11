<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $roles = $user->getRoles();

        if (empty($roles)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        $hasAccess = DB::table('menus')
            ->join('menu_role', 'menus.id', '=', 'menu_role.menu_id')
            ->whereIn('menu_role.role', $roles)
            ->where('menus.status_aktif', true)
            ->where('menus.route', 'like', $this->prefixModul($routeName) . '%')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }

    /**
     * Nama route admin berbentuk `admin.<modul>.<aksi>`, sehingga hak akses
     * diukur pada dua segmen pertama agar berlaku per modul, bukan per aplikasi.
     */
    private function prefixModul(string $routeName): string
    {
        $segmen = explode('.', $routeName);

        return count($segmen) > 2
            ? $segmen[0] . '.' . $segmen[1]
            : $routeName;
    }
}
