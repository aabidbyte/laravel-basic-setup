<?php

namespace App\Providers;

use App\Services\I18nService;
use App\Services\SideBarMenuService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share I18nService with layout templates
        View::composer([
            'components.layouts.app',
            'components.layouts.app.*',
            'components.layouts.auth',
            'components.layouts.auth.*',
        ], function ($view) {
            $view->with('i18n', app(I18nService::class));
        });

        // Share SideBarMenuService with sidebar template
        View::composer('components.layouts.app.sidebar', function ($view) {
            $view->with('menuService', app(SideBarMenuService::class));
        });
    }
}
