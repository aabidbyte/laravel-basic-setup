<?php

namespace App\Providers;

use App\Services\I18nService;
use Illuminate\Support\ServiceProvider;

class I18nServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(I18nService::class);
    }
}
