<?php

declare(strict_types=1);

namespace App\Services\FrontendPreferences\Stores;

use App\Constants\Preferences\FrontendPreferences;
use App\Services\FrontendPreferences\Contracts\PreferencesStore;
use Illuminate\Session\Store;

class SessionPreferencesStore implements PreferencesStore
{
    public function __construct(
        private readonly Store $session,
    ) {}

    /**
     * Get all preferences.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->session->get(FrontendPreferences::SESSION_KEY, []);
    }

    /**
     * Get a preference value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $preferences = $this->all();

        return $preferences[$key] ?? $default;
    }

    /**
     * Set a preference value.
     */
    public function set(string $key, mixed $value): void
    {
        $preferences = $this->all();
        $preferences[$key] = $value;
        $this->session->put(FrontendPreferences::SESSION_KEY, $preferences);
    }

    /**
     * Set multiple preferences at once.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(array $preferences): void
    {
        $existing = $this->all();
        $merged = \array_merge($existing, $preferences);
        $this->session->put(FrontendPreferences::SESSION_KEY, $merged);
    }

    /**
     * Check if a preference exists.
     */
    public function has(string $key): bool
    {
        $preferences = $this->all();

        return isset($preferences[$key]);
    }

    /**
     * Remove a preference.
     */
    public function forget(string $key): void
    {
        $preferences = $this->all();
        unset($preferences[$key]);
        $this->session->put(FrontendPreferences::SESSION_KEY, $preferences);
    }

    /**
     * Clear all preferences.
     */
    public function clear(): void
    {
        $this->session->forget(FrontendPreferences::SESSION_KEY);
    }
}
