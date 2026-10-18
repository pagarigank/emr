<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Behind the Cloudflare tunnel the app receives plain HTTP from the
        // proxy and cannot reliably detect the public HTTPS host, which makes
        // Laravel generate http://127.0.0.1:8082 URLs (Livewire mixed-content).
        // Force the canonical root URL + scheme in production so every
        // generated URL uses the real public domain over HTTPS.
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Blade::if('can', function (string $permission) {
            $user = auth('api')->user();
            return $user && $user->hasPermission($permission);
        });

        Blade::if('role', function (string $role) {
            $user = auth('api')->user();
            return $user && $user->role === $role;
        });

        Blade::if('cannot', function (string $permission) {
            $user = auth('api')->user();
            return !$user || !$user->hasPermission($permission);
        });
    }
}
