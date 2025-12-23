<?php

declare(strict_types=1);

namespace App\Services\DataTable\Contracts;

/**
 * Contract for filter options provider classes
 *
 * Defines the structure for providing filter options
 * for select and multiselect filter types.
 */
interface OptionsProviderInterface
{
    /**
     * Get options array for select filters
     *
     * @return array<int, array{value: string|int, label: string}>
     */
    public function getOptions(): array;
}
