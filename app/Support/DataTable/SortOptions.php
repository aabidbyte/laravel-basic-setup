<?php

declare(strict_types=1);

namespace App\Support\DataTable;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data Object for applying sorting.
 */
readonly class SortOptions
{
    /**
     * Create a new SortOptions instance.
     *
     * @param  Builder  $query  The query builder
     * @param  array  $columns  The column definitions
     * @param  string  $sortBy  The column to sort by
     * @param  string  $sortDirection  The sort direction
     */
    public function __construct(
        public Builder $query,
        public array $columns,
        public string $sortBy,
        public string $sortDirection,
    ) {}
}
