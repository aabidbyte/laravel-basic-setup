<?php

declare(strict_types=1);

namespace App\Support\DataTable;

use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Data Object for building DataTable queries.
 */
readonly class QueryOptions
{
    /**
     * Create a new QueryOptions instance.
     *
     * @param  Builder  $query  Base query
     * @param  array<int, Column>  $columns  Column definitions
     * @param  array<int, Filter>  $filters  Filter definitions
     * @param  array<string, mixed>  $filterValues  Current filter values
     * @param  string  $search  Search term
     * @param  string|null  $sortBy  Sort column
     * @param  string  $sortDirection  Sort direction (asc/desc)
     * @param  int  $perPage  Items per page
     */
    public function __construct(
        public Builder $query,
        public array $columns,
        public array $filters,
        public array $filterValues,
        public string $search,
        public ?string $sortBy,
        public string $sortDirection,
        public int $perPage,
    ) {}
}
