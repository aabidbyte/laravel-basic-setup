<?php

namespace App\Providers;

use App\Constants\Auth\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
            try {
                // Super Admin gets all permissions
                // Use isSuperAdmin() if available, otherwise check Roles constant
                if (method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : $user->hasRole(Roles::SUPER_ADMIN)) {
                    return true;
                }

                // Check if user has the permission through their roles or directly
                if ($user->hasPermissionTo($ability)) {
                    return true;
                }
            } catch (Throwable $e) {
                // Fail-closed on database or other errors during permission check
                report($e);

                return false;
            }

            // Return null to let other gates/policies handle it
            return null;
        });
    }
}
