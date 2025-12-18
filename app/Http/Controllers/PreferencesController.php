<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Constants\FrontendPreferences;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences,
        private readonly I18nService $i18nService
    ) {}

    /**
     * Update the theme preference.
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        $theme = $request->input('theme');
        if (! FrontendPreferences::isValidTheme($theme)) {
            return redirect()->back()->withErrors(['theme' => __('messages.preferences.invalid_theme')]);
        }

        $this->preferences->setTheme($theme);

        return redirect()->back();
    }

    /**
     * Update the locale preference.
     */
    public function updateLocale(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (! $this->i18nService->isLocaleSupported($locale)) {
            return redirect()->back()->withErrors(['locale' => __('messages.preferences.invalid_locale')]);
        }

        $this->preferences->setLocale($locale);

        // Redirect to the same page to apply the new locale
        return redirect()->back();
    }
}
