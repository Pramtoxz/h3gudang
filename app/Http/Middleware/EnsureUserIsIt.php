<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsIt
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user instanceof AdminUser || ! $user->isIt()) {
            abort(403, 'Halaman ini hanya untuk pengelola IT.');
        }

        return $next($request);
    }
}
