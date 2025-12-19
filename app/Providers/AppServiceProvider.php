<?php

namespace App\Providers;

use App\Auth\PasswordBrokerManager;
use App\Http\Middleware\TeamsPermission;
use App\Listeners\SyncUserPreferencesOnLogin;
use App\Observers\DatabaseNotificationObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Event;
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
        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            TeamsPermission::class,
            SubstituteBindings::class,
        );

        // Register event listeners
        Event::listen(Login::class, SyncUserPreferencesOnLogin::class);

        DatabaseNotification::observe(DatabaseNotificationObserver::class);
    }
}
