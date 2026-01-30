<?php

declare(strict_types=1);

namespace App\Services\FrontendPreferences;

use App\Constants\Preferences\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\Stores\SessionPreferencesStore;
use App\Services\FrontendPreferences\Stores\UserJsonPreferencesStore;
use App\Services\I18nService;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Auth;

class FrontendPreferencesService
{
    private ?SessionPreferencesStore $sessionStore = null;

    public function __construct(
        private readonly SessionStore $session,
        private readonly I18nService $i18nService,
    ) {}

    /**
     * Get the session store (single source of truth for reads).
     */
    private function getSessionStore(): SessionPreferencesStore
    {
        if ($this->sessionStore === null) {
            $this->sessionStore = new SessionPreferencesStore($this->session);
        }

        return $this->sessionStore;
    }

    /**
     * Sync preferences from user database to session if needed.
     * This ensures session is always the single source of truth.
     */
    private function syncFromDatabaseIfNeeded(?Request $request = null): void
    {
        $user = Auth::user();

        // Only sync if user is authenticated
        if (! $user instanceof User) {
            return;
        }

        $sessionStore = $this->getSessionStore();
        $sessionPrefs = $sessionStore->all();

        // If session is empty, load from database
        if (empty($sessionPrefs)) {
            $userStore = new UserJsonPreferencesStore($user);
            $userPrefs = $userStore->all();

            if (! empty($userPrefs)) {
                // Sync from DB to session
                $sessionStore->setMany($userPrefs);
            } else {
                // No user preferences, try browser detection on first visit
                if ($request !== null) {
                    $detectedPrefs = $this->detectBrowserPreferences($request);
                    if (! empty($detectedPrefs)) {
                        // Save detected preferences to both DB and session
                        $userStore->setMany($detectedPrefs);
                        $sessionStore->setMany($detectedPrefs);
                    }
                }
            }
        }
    }

    /**
     * Get a preference value.
     * Session is the single source of truth for reads.
     */
    public function get(string $key, mixed $default = null, ?Request $request = null): mixed
    {
        // Sync from database if needed (first load for authenticated users)
        $this->syncFromDatabaseIfNeeded($request);

        $sessionStore = $this->getSessionStore();
        $sessionPrefs = $sessionStore->all();
        $defaults = FrontendPreferences::getDefaults();

        // For guests, try browser detection on first visit
        if (empty($sessionPrefs) && Auth::guest() && $request !== null) {
            $detectedPrefs = $this->detectBrowserPreferences($request);
            if (! empty($detectedPrefs)) {
                $sessionStore->setMany($detectedPrefs);
                $sessionPrefs = $sessionStore->all();
            }
        }

        $merged = \array_merge($defaults, $sessionPrefs);

        return $merged[$key] ?? $default;
    }

    /**
     * Set a preference value.
     * For authenticated users: update DB first, then session.
     * For guests: update session only.
     */
    public function set(string $key, mixed $value): void
    {
        $user = Auth::user();

        // If authenticated, update DB first, then session
        if ($user instanceof User) {
            $userStore = new UserJsonPreferencesStore($user);
            $userStore->set($key, $value);
        }

        // Always update session (single source of truth)
        $this->getSessionStore()->set($key, $value);
    }

    /**
     * Set multiple preferences at once.
     * For authenticated users: update DB first, then session.
     * For guests: update session only.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(array $preferences): void
    {
        $user = Auth::user();

        // If authenticated, update DB first, then session
        if ($user instanceof User) {
            $userStore = new UserJsonPreferencesStore($user);
            $userStore->setMany($preferences);
        }

        // Always update session (single source of truth)
        $this->getSessionStore()->setMany($preferences);
    }

    /**
     * Get all preferences.
     * Session is the single source of truth for reads.
     *
     * @return array<string, mixed>
     */
    public function all(?Request $request = null): array
    {
        // Sync from database if needed (first load for authenticated users)
        $this->syncFromDatabaseIfNeeded($request);

        $sessionStore = $this->getSessionStore();
        $sessionPrefs = $sessionStore->all();
        $defaults = FrontendPreferences::getDefaults();

        // For guests, try browser detection on first visit
        if (empty($sessionPrefs) && Auth::guest() && $request !== null) {
            $detectedPrefs = $this->detectBrowserPreferences($request);
            if (! empty($detectedPrefs)) {
                $sessionStore->setMany($detectedPrefs);
                $sessionPrefs = $sessionStore->all();
            }
        }

        return \array_merge($defaults, $sessionPrefs);
    }

    /**
     * Refresh preferences from database to session.
     * This reloads user preferences from DB and syncs to session.
     */
    public function refresh(): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->syncUserPreferencesToSession($user);
        }
    }

    /**
     * Sync user preferences from database to session.
     * This is called on login to ensure preferences are loaded from DB and synced to session.
     */
    public function syncUserPreferencesToSession(User $user): void
    {
        // Refresh user model to get latest data
        $user->refresh();

        // Load from DB and sync to session
        $userStore = new UserJsonPreferencesStore($user);
        $userPrefs = $userStore->all();

        // Sync to session (single source of truth)
        $this->getSessionStore()->setMany($userPrefs);
    }

    /**
     * Alias for syncUserPreferencesToSession() for backward compatibility
     */
    public function syncUserPreferences(User $user): void
    {
        $this->syncUserPreferencesToSession($user);
    }

    /**
     * Get the locale preference.
     */
    public function getLocale(?Request $request = null): string
    {
        $locale = $this->get(FrontendPreferences::KEY_LOCALE, FrontendPreferences::DEFAULT_LOCALE, $request);

        return $this->i18nService->getValidLocale($locale);
    }

    /**
     * Set the locale preference.
     */
    public function setLocale(string $locale): void
    {
        $validLocale = $this->i18nService->getValidLocale($locale);
        $this->set(FrontendPreferences::KEY_LOCALE, $validLocale);
    }

    /**
     * Get the theme preference.
     */
    public function getTheme(?Request $request = null): string
    {
        $theme = $this->get(FrontendPreferences::KEY_THEME, FrontendPreferences::DEFAULT_THEME, $request);

        return FrontendPreferences::isValidTheme($theme) ? $theme : FrontendPreferences::DEFAULT_THEME;
    }

    /**
     * Set the theme preference.
     * Valid values: "light" or "dark".
     */
    public function setTheme(string $theme): void
    {
        if (! FrontendPreferences::isValidTheme($theme)) {
            $theme = FrontendPreferences::DEFAULT_THEME;
        }

        $this->set(FrontendPreferences::KEY_THEME, $theme);
    }

    /**
     * Get the timezone preference.
     * Note: Timezone preference is for display purposes only.
     * All dates/times are stored in the database using the application timezone from config/app.php.
     * Date/time formatting helpers (formatDate, formatTime, formatDateTime) use this preference when displaying dates/times.
     */
    public function getTimezone(): string
    {
        $timezone = $this->get(FrontendPreferences::KEY_TIMEZONE, FrontendPreferences::DEFAULT_TIMEZONE);

        // Validate timezone
        if (\in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            return $timezone;
        }

        return FrontendPreferences::DEFAULT_TIMEZONE;
    }

    /**
     * Set the timezone preference.
     * Note: Timezone preference is for display purposes only.
     * All dates/times are stored in the database using the application timezone from config/app.php.
     */
    public function setTimezone(string $timezone): void
    {
        // Validate timezone
        if (\in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            $this->set(FrontendPreferences::KEY_TIMEZONE, $timezone);
        } else {
            $this->set(FrontendPreferences::KEY_TIMEZONE, FrontendPreferences::DEFAULT_TIMEZONE);
        }
    }

    /**
     * Get datatable preferences for a specific datatable identifier.
     *
     * @param  string  $identifier  Datatable identifier (typically the full class name)
     * @return array<string, mixed>
     */
    public function getDatatablePreferences(string $identifier, ?Request $request = null): array
    {
        $allDatatables = $this->get(FrontendPreferences::KEY_DATATABLES, [], $request);

        return \is_array($allDatatables) && isset($allDatatables[$identifier]) && \is_array($allDatatables[$identifier])
            ? $allDatatables[$identifier]
            : [];
    }

    /**
     * Set datatable preferences for a specific datatable identifier.
     *
     * @param  string  $identifier  Datatable identifier (typically the full class name)
     * @param  array<string, mixed>  $preferences  Preferences to save (excludes search term)
     */
    public function setDatatablePreferences(string $identifier, array $preferences): void
    {
        $allDatatables = $this->get(FrontendPreferences::KEY_DATATABLES, []);

        if (! \is_array($allDatatables)) {
            $allDatatables = [];
        }

        $allDatatables[$identifier] = $preferences;

        $this->set(FrontendPreferences::KEY_DATATABLES, $allDatatables);
    }

    /**
     * Get a specific datatable preference value.
     *
     * @param  string  $identifier  Datatable identifier (typically the full class name)
     * @param  string  $key  Preference key (e.g., 'sortBy', 'perPage', 'filters')
     */
    public function getDatatablePreference(string $identifier, string $key, mixed $default = null, ?Request $request = null): mixed
    {
        $preferences = $this->getDatatablePreferences($identifier, $request);

        return $preferences[$key] ?? $default;
    }

    /**
     * Set a specific datatable preference value.
     *
     * @param  string  $identifier  Datatable identifier (typically the full class name)
     * @param  string  $key  Preference key (e.g., 'sortBy', 'perPage', 'filters')
     */
    public function setDatatablePreference(string $identifier, string $key, mixed $value): void
    {
        $preferences = $this->getDatatablePreferences($identifier);
        $preferences[$key] = $value;

        $this->setDatatablePreferences($identifier, $preferences);
    }

    /**
     * Detect browser preferences from request headers.
     * Only detects preferences that are not already set.
     *
     * @return array<string, mixed>
     */
    private function detectBrowserPreferences(Request $request): array
    {
        $detected = [];

        // Detect language from Accept-Language header
        $detectedLocale = $this->detectBrowserLanguage($request);
        if ($detectedLocale !== null) {
            $detected[FrontendPreferences::KEY_LOCALE] = $detectedLocale;
        }

        // Theme preference defaults to "light" - no automatic detection

        return $detected;
    }

    /**
     * Detect browser language from Accept-Language header.
     * Returns a valid locale code or null if detection fails.
     */
    private function detectBrowserLanguage(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        if (empty($acceptLanguage)) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,fr;q=0.8")
        $languages = [];
        $parts = \explode(',', $acceptLanguage);

        foreach ($parts as $part) {
            $part = \trim($part);
            if (empty($part)) {
                continue;
            }

            // Extract language code (e.g., "en-US" or "en")
            if (\str_contains($part, ';')) {
                [$lang, $q] = \explode(';', $part, 2);
                $lang = \trim($lang);
                $q = \trim($q);

                // Extract quality value (e.g., "q=0.9")
                if (\preg_match('/q=([\d.]+)/', $q, $matches)) {
                    $quality = (float) $matches[1];
                } else {
                    $quality = 1.0; // Default quality if not specified
                }
            } else {
                $lang = \trim($part);
                $quality = 1.0; // First language has highest priority
            }

            if (! empty($lang)) {
                $languages[] = [
                    'code' => $lang,
                    'quality' => $quality,
                ];
            }
        }

        // Sort by quality (highest first)
        usort($languages, fn ($a, $b) => $b['quality'] <=> $a['quality']);

        // Try to match each language against supported locales
        $supportedLocales = $this->i18nService->getSupportedLocales();

        foreach ($languages as $lang) {
            $code = $lang['code'];

            // Try exact match first (e.g., "en_US")
            $normalized = \str_replace('-', '_', $code);
            if (isset($supportedLocales[$normalized])) {
                return $normalized;
            }

            // Try language code only (e.g., "en" from "en-US")
            $langOnly = \explode('_', $normalized)[0];
            foreach ($supportedLocales as $locale => $metadata) {
                $localeLang = \explode('_', $locale)[0];
                if ($localeLang === $langOnly) {
                    return $locale;
                }
            }
        }

        return null;
    }
}
