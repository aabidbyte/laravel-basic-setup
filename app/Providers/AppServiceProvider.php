<?php

namespace App\Providers;

use App\Auth\PasswordBrokerManager;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
