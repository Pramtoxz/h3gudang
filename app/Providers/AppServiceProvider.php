<?php

namespace App\Providers;

use App\Auth\AdminUserProvider;
use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        Auth::provider(
            'admin_dms',
            fn ($app, array $config): AdminUserProvider => new AdminUserProvider($app['hash'], $config['model']),
        );

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    private function configureRateLimiting(): void
    {
        $this->configureThrottleApi();
    }

    private function configureThrottleApi(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        /*
         * Login API lapangan lebih ketat karena dipanggil dari aplikasi operator:
         * 10 per menit per IP, 3 per menit per email supaya satu akun tidak dispam.
         */
        RateLimiter::for('lapangan-login', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(3)->by((string) $request->input('email')),
            ];
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
