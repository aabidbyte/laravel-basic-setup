<?php

declare(strict_types=1);

namespace App\Services\DataTable\Contracts;

/**
 * Contract for DataTable preferences storage
 */
interface DataTablePreferencesStore
{
    /**
     * Get all preferences for an entity.
     *
     * @return array<string, mixed>
     */
    public function all(string $entityKey): array;

    /**
     * Get a preference value by key for an entity.
     */
    public function get(string $entityKey, string $key, mixed $default = null): mixed;

    /**
     * Set a preference value for an entity.
     */
    public function set(string $entityKey, string $key, mixed $value): void;

    /**
     * Set multiple preferences at once for an entity.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(string $entityKey, array $preferences): void;

    /**
     * Check if a preference exists for an entity.
     */
    public function has(string $entityKey, string $key): bool;

    /**
     * Remove a preference for an entity.
     */
    public function forget(string $entityKey, string $key): void;

    /**
     * Clear all preferences for an entity.
     */
    public function clear(string $entityKey): void;
}
