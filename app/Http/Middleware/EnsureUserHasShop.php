<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasShop
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->fk_toko) {
            return ApiResponse::error('User tidak terdaftar di toko', 400);
        }

        return $next($request);
    }
}
