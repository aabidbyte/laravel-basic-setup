<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Constants\FrontendPreferences;
use App\Http\Requests\UpdateLocaleRequest;
use App\Http\Requests\UpdateThemeRequest;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use Illuminate\Http\RedirectResponse;

class PreferencesController extends Controller
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences,
        private readonly I18nService $i18nService
    ) {}

    /**
     * Update the theme preference.
     */
    public function updateTheme(UpdateThemeRequest $request): RedirectResponse
    {
        /** @var string $theme */
        $theme = $request->validated(FrontendPreferences::KEY_THEME);

        $this->preferences->setTheme($theme);

        return redirect()->back();
    }

    /**
     * Update the locale preference.
     */
    public function updateLocale(UpdateLocaleRequest $request): RedirectResponse
    {
        /** @var string $locale */
        $locale = $request->validated(FrontendPreferences::KEY_LOCALE);

        $this->preferences->setLocale($locale);

        // Redirect to the same page to apply the new locale
        return redirect()->back();
    }
}
