<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Blade directive: check module permission (READ or FULL)
        Blade::if('hasPermission', function ($modules, ?string $accessType = null, $companyId = null) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            return $user->hasPermission($modules, $accessType, $companyId);
        });

        // Blade directive: check FULL access permission
        Blade::if('fullAccess', function ($modules, $companyId = null) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            return $user->hasFullPermission($modules, $companyId);
        });

        // Blade directive: check if super admin
        Blade::if('superAdmin', function () {
            return auth()->user()?->isSuperAdmin() ?? false;
        });
    }
}
