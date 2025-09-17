<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Add Blade directive for permission checking
        Blade::if("permission", function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });

        // Add Blade directive for role checking
        Blade::if("role", function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Add Blade directive for any role checking
        Blade::if("anyrole", function (...$roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });
    }
}
