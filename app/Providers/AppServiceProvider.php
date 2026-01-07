<?php

namespace App\Providers;

use App\Auth\PasswordBrokerManager;
use App\Constants\Auth\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override the password broker manager with our custom one
        // This uses the PasswordResetToken model which has HasUuid trait
        // that automatically generates UUIDs via model events
        $this->app->extend('auth.password', function ($manager, $app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->singleton(\App\Services\FrontendPreferences\FrontendPreferencesService::class);
        $this->app->singleton(\App\Services\I18nService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! isProduction());

        Vite::useCspNonce();

        // Register authorization Gates for custom RBAC
        $this->registerPermissionGates();

        // Register search macros for Eloquent Builder
        $this->registerSearchMacros();
    }

    /**
     * Register global search macros for Eloquent Builder
     */
    private function registerSearchMacros(): void
    {
        /**
         * Simple search macro for single/multiple columns
         *
         * Usage:
         * User::search('john', ['name', 'email'])
         * User::search('john', 'name')
         * Team::search('marketing', ['name', 'description'])
         */
        Builder::macro('search', function (string $query, array|string $columns = []) {
            /** @var Builder $this */
            if (empty($query) || empty($columns)) {
                return $this;
            }

            $columns = is_array($columns) ? $columns : [$columns];

            return $this->where(function (Builder $builder) use ($query, $columns) {
                foreach ($columns as $column) {
                    // Handle relation.column syntax
                    if (str_contains($column, '.')) {
                        [$relation, $relationColumn] = explode('.', $column, 2);
                        $builder->orWhereHas($relation, function (Builder $relationQuery) use ($relationColumn, $query) {
                            $relationQuery->where($relationColumn, 'LIKE', "%{$query}%");
                        });
                    } else {
                        $builder->orWhere($column, 'LIKE', "%{$query}%");
                    }
                }
            });
        });
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
