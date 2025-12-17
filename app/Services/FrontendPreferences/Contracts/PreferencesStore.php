<?php

declare(strict_types=1);

namespace App\Services\FrontendPreferences\Contracts;

interface PreferencesStore
{
    /**
     * Get all preferences.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Get a preference value by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a preference value.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Set multiple preferences at once.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(array $preferences): void;

    /**
     * Check if a preference exists.
     */
    public function has(string $key): bool;

    /**
     * Remove a preference.
     */
    public function forget(string $key): void;

    /**
     * Clear all preferences.
     */
    public function clear(): void;
}
