<?php

namespace App\Providers;

use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use App\Services\SideBarMenuService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->initLayoutVariables();
        $this->initPageTitle();
        $this->initPageSubtitle();
    }

    private function initLayoutVariables()
    {
        View::composer([
            'components.layouts.app',
            'components.layouts.auth',
            'layouts::app',
            'layouts::auth',
        ], function ($view) {
            $preferences = app(FrontendPreferencesService::class);
            $view->with('currentTheme', $preferences->getTheme());

            $i18n = app(I18nService::class);
            $view->with('htmlLangAttribute', $i18n->getHtmlLangAttribute());
            $view->with('htmlDirAttribute', $i18n->getHtmlDirAttribute());
        });

        // Share I18nService and FrontendPreferencesService with layout templates and preference components
        View::composer([
            'components.preferences.locale-switcher',
        ], function ($view) {
            $preferences = app(FrontendPreferencesService::class);
            $i18n = app(I18nService::class);

            $view->with('currentLocale', $i18n->getLocale());
            $view->with('supportedLocales', $i18n->getSupportedLocales());
            $view->with('localeMetadata', $i18n->getLocaleMetadata($preferences->getLocale()));
        });

        View::composer([
            'components.preferences.theme-switcher',
        ], function ($view) {
            $preferences = app(FrontendPreferencesService::class);
            // Share current values for components
            $view->with('currentTheme', $preferences->getTheme());
        });

        // Share SideBarMenuService with sidebar template
        View::composer('components.layouts.app.*', function ($view) {
            $menuService = app(SideBarMenuService::class);

            $sideBarTopMenus = $menuService->getTopMenus();
            $sideBarBottomMenus = $menuService->getBottomMenus();
            $sideBarUserMenus = $menuService->getUserMenus();

            $view->with('sideBarTopMenus', $sideBarTopMenus);
            $view->with('sideBarBottomMenus', $sideBarBottomMenus);
            $view->with('sideBarUserMenus', $sideBarUserMenus);
        });
        // Share notification config for app layout (authenticated users)
        View::composer(['partials.head'], function ($view) {
            $user = Auth::user();
            $pendingNotifications = session()->pull('pending_toast_notifications', []);

            $notificationRealtimeConfig = [
                'userUuid' => $user?->uuid,
                'teamUuids' => $user ? $user->teams()->pluck('teams.uuid')->toArray() : [],
                'sessionId' => session()->getId(),
                'pendingNotifications' => $pendingNotifications,
            ];

            $view->with('notificationRealtimeConfig', $notificationRealtimeConfig);
        });

        // Share notification config for auth layout (non-authenticated users - session only)
        View::composer(['partials.auth.head'], function ($view) {
            $pendingNotifications = session()->pull('pending_toast_notifications', []);

            $notificationRealtimeConfig = [
                'userUuid' => null,
                'teamUuids' => [],
                'sessionId' => session()->getId(),
                'pendingNotifications' => $pendingNotifications,
            ];

            $view->with('notificationRealtimeConfig', $notificationRealtimeConfig);
        });
    }

    private function initPageTitle(): void
    {
        View::composer(['partials.head', 'partials.auth.head', 'components.layouts.app.header'], function ($view) {
            $pageTitle = null;

            // Check view data (from controller, view, or View::share())
            if (isset($view->getData()['pageTitle'])) {
                $pageTitle = $view->getData()['pageTitle'];
            }

            // Check if shared via View::share() (from Livewire component)
            if (! $pageTitle && View::shared('pageTitle')) {
                $pageTitle = View::shared('pageTitle');
            }

            // Fallback
            $view->with('pageTitle', $pageTitle ?? config('app.name'));
        });
    }

    private function initPageSubtitle(): void
    {
        View::composer(['partials.head', 'components.layouts.app.header'], function ($view) {
            $pageSubtitle = null;

            // Check view data (from controller, view, or View::share())
            if (isset($view->getData()['pageSubtitle'])) {
                $pageSubtitle = $view->getData()['pageSubtitle'];
            }

            // Check if shared via View::share() (from Livewire component)
            if (! $pageSubtitle && View::shared('pageSubtitle')) {
                $pageSubtitle = View::shared('pageSubtitle');
            }

            // Share pageSubtitle (can be null)
            $view->with('pageSubtitle', $pageSubtitle);
        });
    }
}
