<?php

declare(strict_types=1);

namespace App\Services\DataTable\Stores;

use App\Constants\DataTable\DataTable as DataTableConstants;
use App\Models\User;
use App\Services\DataTable\Contracts\DataTablePreferencesStore;

class UserJsonDataTablePreferencesStore implements DataTablePreferencesStore
{
    public function __construct(
        private readonly User $user
    ) {}

    /**
     * Get all preferences for an entity from user's frontend_preferences JSON column.
     *
     * @return array<string, mixed>
     */
    public function all(string $entityKey): array
    {
        $userPrefKey = DataTableConstants::getUserPreferenceKey($entityKey);
        $preferences = $this->user->frontend_preferences ?? [];

        return $preferences[$userPrefKey] ?? [];
    }

    /**
     * Get a preference value by key for an entity.
     */
    public function get(string $entityKey, string $key, mixed $default = null): mixed
    {
        $preferences = $this->all($entityKey);

        return $preferences[$key] ?? $default;
    }

    /**
     * Set a preference value for an entity.
     */
    public function set(string $entityKey, string $key, mixed $value): void
    {
        $userPrefKey = DataTableConstants::getUserPreferenceKey($entityKey);
        $preferences = $this->user->frontend_preferences ?? [];
        $entityPrefs = $preferences[$userPrefKey] ?? [];
        $entityPrefs[$key] = $value;
        $preferences[$userPrefKey] = $entityPrefs;
        $this->user->frontend_preferences = $preferences;
        $this->user->save();
    }

    /**
     * Set multiple preferences at once for an entity.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(string $entityKey, array $preferences): void
    {
        $userPrefKey = DataTableConstants::getUserPreferenceKey($entityKey);
        $userPrefs = $this->user->frontend_preferences ?? [];
        $existing = $userPrefs[$userPrefKey] ?? [];
        $merged = array_merge($existing, $preferences);
        $userPrefs[$userPrefKey] = $merged;
        $this->user->frontend_preferences = $userPrefs;
        $this->user->save();
    }

    /**
     * Check if a preference exists for an entity.
     */
    public function has(string $entityKey, string $key): bool
    {
        $preferences = $this->all($entityKey);

        return isset($preferences[$key]);
    }

    /**
     * Remove a preference for an entity.
     */
    public function forget(string $entityKey, string $key): void
    {
        $userPrefKey = DataTableConstants::getUserPreferenceKey($entityKey);
        $preferences = $this->user->frontend_preferences ?? [];
        $entityPrefs = $preferences[$userPrefKey] ?? [];
        unset($entityPrefs[$key]);
        $preferences[$userPrefKey] = $entityPrefs;
        $this->user->frontend_preferences = $preferences;
        $this->user->save();
    }

    /**
     * Clear all preferences for an entity.
     */
    public function clear(string $entityKey): void
    {
        $userPrefKey = DataTableConstants::getUserPreferenceKey($entityKey);
        $preferences = $this->user->frontend_preferences ?? [];
        unset($preferences[$userPrefKey]);
        $this->user->frontend_preferences = $preferences;
        $this->user->save();
    }
}
