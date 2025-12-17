<?php

namespace App\Providers;

use App\Services\FrontendPreferences\FrontendPreferencesService;
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
        // Share I18nService and FrontendPreferencesService with layout templates and preference components
        View::composer([
            'components.layouts.app',
            'components.layouts.app.*',
            'components.layouts.auth',
            'components.layouts.auth.*',
            'components.preferences.*',
            'layouts::app',
            'layouts::app.*',
            'layouts::auth',
            'layouts::auth.*',
        ], function ($view) {
            $i18n = app(I18nService::class);
            $preferences = app(FrontendPreferencesService::class);

            $view->with('i18n', $i18n);
            $view->with('preferences', $preferences);
            // Share current values for components
            $view->with('currentTheme', $preferences->getTheme());
            $view->with('currentLocale', $preferences->getLocale());
            $view->with('supportedLocales', $i18n->getSupportedLocales());
        });

        // Share SideBarMenuService with sidebar template
        View::composer('components.layouts.app.sidebar', function ($view) {
            $view->with('menuService', app(SideBarMenuService::class));
        });
    }
}
