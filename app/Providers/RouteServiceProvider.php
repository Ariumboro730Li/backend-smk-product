<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {

        $this->routes(function () {

            Route::middleware([
                'web',
            ])->group(base_path('routes/web.php'));

            Route::middleware([
                'web',
                // 'is_auth',
                // 'app_type_user:internal'
                'check.token',
                'auth.jwt',
                'role:internal'
            ])
            ->name((string) "internal.")
            ->prefix((string) "/internal")
            ->group(base_path('routes/internal.php'));

            Route::middleware([
                'web',
                // 'is_auth',
                // 'app_type_user:perusahaan'
                'check.token',
                // 'auth.jwt',
                'role:company'
            ])
            ->name((string) "company.")
            ->prefix((string) "/company")
            ->group(base_path('routes/company.php'));

        });
    }
}
