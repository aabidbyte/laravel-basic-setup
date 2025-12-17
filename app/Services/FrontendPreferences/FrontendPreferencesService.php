<?php

declare(strict_types=1);

namespace App\Services\FrontendPreferences;

use App\Constants\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\Contracts\PreferencesStore;
use App\Services\FrontendPreferences\Stores\SessionPreferencesStore;
use App\Services\FrontendPreferences\Stores\UserJsonPreferencesStore;
use App\Services\I18nService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\Store as SessionStore;

class FrontendPreferencesService
{
    private ?PreferencesStore $persistentStore = null;

    private ?PreferencesStore $cacheStore = null;

    private bool $loaded = false;

    public function __construct(
        private readonly SessionStore $session,
        private readonly I18nService $i18nService
    ) {}

    /**
     * Get the persistent store (user DB or session for guests).
     */
    private function getPersistentStore(): PreferencesStore
    {
        if ($this->persistentStore !== null) {
            return $this->persistentStore;
        }

        $user = auth()->user();

        if ($user instanceof Authenticatable) {
            $this->persistentStore = new UserJsonPreferencesStore($user);
        } else {
            $this->persistentStore = new SessionPreferencesStore($this->session);
        }

        return $this->persistentStore;
    }

    /**
     * Get the cache store (always session).
     */
    private function getCacheStore(): PreferencesStore
    {
        if ($this->cacheStore === null) {
            $this->cacheStore = new SessionPreferencesStore($this->session);
        }

        return $this->cacheStore;
    }

    /**
     * Ensure preferences are loaded into cache.
     */
    private function ensureLoaded(): void
    {
        $cache = $this->getCacheStore();

        // If cache is empty or we haven't loaded yet, load from persistent store
        if (empty($cache->all()) || ! $this->loaded) {
            $persistent = $this->getPersistentStore();

            // Always load from persistent store and merge with defaults
            // This ensures we have the latest data from DB (for authenticated users) or session (for guests)
            $persistentPrefs = $persistent->all();
            $defaults = FrontendPreferences::getDefaults();
            $merged = array_merge($defaults, $persistentPrefs);
            $cache->setMany($merged);

            $this->loaded = true;
        }
    }

    /**
     * Get a preference value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureLoaded();

        return $this->getCacheStore()->get($key, $default);
    }

    /**
     * Set a preference value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureLoaded();

        // Update persistent store
        $this->getPersistentStore()->set($key, $value);

        // Update cache store
        $this->getCacheStore()->set($key, $value);

        // Reset loaded flag to ensure fresh data is loaded on next request
        // This is important for singleton instances that persist across requests
        $this->loaded = false;
    }

    /**
     * Set multiple preferences at once.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(array $preferences): void
    {
        $this->ensureLoaded();

        // Update persistent store
        $this->getPersistentStore()->setMany($preferences);

        // Update cache store
        $this->getCacheStore()->setMany($preferences);

        // Reset loaded flag to ensure fresh data is loaded on next request
        // This is important for singleton instances that persist across requests
        $this->loaded = false;
    }

    /**
     * Get all preferences.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $this->ensureLoaded();

        return $this->getCacheStore()->all();
    }

    /**
     * Refresh preferences from persistent store (clears cache and reloads).
     */
    public function refresh(): void
    {
        $this->getCacheStore()->clear();

        // Refresh authenticated user if present
        $user = auth()->user();
        if ($user instanceof User) {
            $user->refresh();
        }

        $this->persistentStore = null; // Reset to get fresh user instance
        $this->loaded = false;
        $this->ensureLoaded();
    }

    /**
     * Get the locale preference.
     */
    public function getLocale(): string
    {
        $locale = $this->get(FrontendPreferences::KEY_LOCALE, FrontendPreferences::DEFAULT_LOCALE);

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
    public function getTheme(): string
    {
        $theme = $this->get(FrontendPreferences::KEY_THEME, FrontendPreferences::DEFAULT_THEME);

        return FrontendPreferences::isValidTheme($theme) ? $theme : FrontendPreferences::DEFAULT_THEME;
    }

    /**
     * Set the theme preference.
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
        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (\Exception $e) {
            return FrontendPreferences::DEFAULT_TIMEZONE;
        }
    }

    /**
     * Set the timezone preference.
     * Note: Timezone preference is for display purposes only.
     * All dates/times are stored in the database using the application timezone from config/app.php.
     */
    public function setTimezone(string $timezone): void
    {
        // Validate timezone
        try {
            new \DateTimeZone($timezone);
            $this->set(FrontendPreferences::KEY_TIMEZONE, $timezone);
        } catch (\Exception $e) {
            $this->set(FrontendPreferences::KEY_TIMEZONE, FrontendPreferences::DEFAULT_TIMEZONE);
        }
    }
}
