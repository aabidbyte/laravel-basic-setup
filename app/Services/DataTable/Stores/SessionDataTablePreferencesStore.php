<?php

declare(strict_types=1);

namespace App\Services\DataTable\Stores;

use App\Constants\DataTable as DataTableConstants;
use App\Services\DataTable\Contracts\DataTablePreferencesStore;
use Illuminate\Session\Store;

class SessionDataTablePreferencesStore implements DataTablePreferencesStore
{
    public function __construct(
        private readonly Store $session
    ) {}

    /**
     * Get all preferences for an entity.
     *
     * @return array<string, mixed>
     */
    public function all(string $entityKey): array
    {
        $sessionKey = DataTableConstants::getSessionKey($entityKey);

        return $this->session->get($sessionKey, []);
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
        $sessionKey = DataTableConstants::getSessionKey($entityKey);
        $preferences = $this->all($entityKey);
        $preferences[$key] = $value;
        $this->session->put($sessionKey, $preferences);
    }

    /**
     * Set multiple preferences at once for an entity.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(string $entityKey, array $preferences): void
    {
        $sessionKey = DataTableConstants::getSessionKey($entityKey);
        $existing = $this->all($entityKey);
        $merged = array_merge($existing, $preferences);
        $this->session->put($sessionKey, $merged);
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
        $sessionKey = DataTableConstants::getSessionKey($entityKey);
        $preferences = $this->all($entityKey);
        unset($preferences[$key]);
        $this->session->put($sessionKey, $preferences);
    }

    /**
     * Clear all preferences for an entity.
     */
    public function clear(string $entityKey): void
    {
        $sessionKey = DataTableConstants::getSessionKey($entityKey);
        $this->session->forget($sessionKey);
    }
}
