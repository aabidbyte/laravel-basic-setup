<?php

namespace App\Providers;

use App\Constants\Auth\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPermissionGates();
    }

    /**
     * Register authorization Gates for custom RBAC.
     *
     * This uses Gate::before() with a fallback to hasPermissionTo()
     * to handle all permission checks dynamically.
     */
    private function registerPermissionGates(): void
    {
        // Use Gate::before to intercept all gate checks
        // This handles both Super Admin bypass and regular permission checks
        Gate::before(function ($user, $ability) {
            // Super Admin gets all permissions
            if ($user->hasRole(Roles::SUPER_ADMIN)) {
                return true;
            }

            // Check if user has the permission through their roles
            if ($user->hasPermissionTo($ability)) {
                return true;
            }

            // Return null to let other gates/policies handle it
            return null;
        });
    }
}
