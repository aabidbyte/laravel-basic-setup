<?php

namespace App\Providers;

use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Support\ServiceProvider;

class FrontendPreferencesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FrontendPreferencesService::class);
    }
}
