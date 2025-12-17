<?php

declare(strict_types=1);

namespace App\Services\FrontendPreferences\Stores;

use App\Models\User;
use App\Services\FrontendPreferences\Contracts\PreferencesStore;

class UserJsonPreferencesStore implements PreferencesStore
{
    public function __construct(
        private readonly User $user
    ) {}

    /**
     * Get all preferences.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->user->frontend_preferences ?? [];
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
        $this->user->frontend_preferences = $preferences;
        $this->user->save();
    }

    /**
     * Set multiple preferences at once.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(array $preferences): void
    {
        $existing = $this->all();
        $merged = array_merge($existing, $preferences);
        $this->user->frontend_preferences = $merged;
        $this->user->save();
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
        $this->user->frontend_preferences = $preferences;
        $this->user->save();
    }

    /**
     * Clear all preferences.
     */
    public function clear(): void
    {
        $this->user->frontend_preferences = null;
        $this->user->save();
    }
}
