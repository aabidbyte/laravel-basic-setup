<?php

declare(strict_types=1);

namespace App\Services\DataTable\Contracts;

/**
 * Contract for DataTable configuration classes
 *
 * Defines the structure for entity-specific DataTable configurations,
 * including searchable fields, filterable fields, sortable fields,
 * default sorting, bulk actions, and other configuration options.
 */
interface DataTableConfigInterface
{
    /**
     * Get searchable fields for global search
     *
     * @return array<int, string> Array of field names or relation.field names
     */
    public function getSearchableFields(): array;

    /**
     * Get filterable fields configuration
     *
     * @return array<string, array{type: string, label: string, options?: array|callable, relationship?: array, depends_on?: string}>
     */
    public function getFilterableFields(): array;

    /**
     * Get sortable fields
     *
     * @return array<string, array{label: string, default_direction?: string}>
     */
    public function getSortableFields(): array;

    /**
     * Get default sort configuration
     *
     * @return array{column: string, direction: string}|null
     */
    public function getDefaultSort(): ?array;

    /**
     * Whether to include configuration in the response
     */
    public function includeConfig(): bool;

    /**
     * Whether to include filter state in the response
     */
    public function includeFilterState(): bool;

    /**
     * Get bulk actions configuration
     *
     * @return array<int, array{key: string, label: string, variant?: string, color?: string}>
     */
    public function getBulkActions(): array;

    /**
     * Get entity key for session storage and identification
     */
    public function getEntityKey(): string;

    /**
     * Get view name for the DataTable (optional, for reference)
     */
    public function getViewName(): ?string;
}
