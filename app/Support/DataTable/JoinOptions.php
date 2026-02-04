<?php

declare(strict_types=1);

namespace App\Support\DataTable;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data Object for applying joins.
 */
readonly class JoinOptions
{
    /**
     * Create a new JoinOptions instance.
     *
     * @param  Builder  $query  The query builder
     * @param  mixed  $relation  The relationship object
     * @param  string  $parentTable  Parent table name/alias
     * @param  string  $relatedTable  Related table name
     * @param  string  $alias  Table alias
     */
    public function __construct(
        public Builder $query,
        public mixed $relation,
        public string $parentTable,
        public string $relatedTable,
        public string $alias,
    ) {}
}
