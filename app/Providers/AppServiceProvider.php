<?php

namespace App\Providers;

use App\Auth\PasswordBrokerManager;
use App\Constants\Auth\Roles;
use App\Http\Middleware\Teams\TeamsPermission;
use App\Listeners\DevEmailRedirectListener;
use App\Listeners\Preferences\SyncUserPreferencesOnLogin;
use App\Observers\Notifications\DatabaseNotificationObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Event;
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
        Vite::useCspNonce();

        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            TeamsPermission::class,
            SubstituteBindings::class,
        );

        // Register event listeners
        Event::listen(Login::class, SyncUserPreferencesOnLogin::class);
        Event::listen(MessageSending::class, DevEmailRedirectListener::class);

        DatabaseNotification::observe(DatabaseNotificationObserver::class);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        // Following Spatie Permissions best practices: https://spatie.be/docs/laravel-permission/v6/basic-usage/super-admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
        });

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
}
