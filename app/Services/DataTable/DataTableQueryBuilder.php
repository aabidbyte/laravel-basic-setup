<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Support\DataTable\JoinOptions;
use App\Support\DataTable\QueryOptions;
use App\Support\DataTable\SortOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * DataTable Query Builder with automatic relationship joins
 *
 * Handles:
 * - Automatic join detection from column field names (e.g., 'address.city.name')
 * - Search across multiple columns
 * - Filtering with relationship support
 * - Sorting with relationship support
 * - Pagination
 */
class DataTableQueryBuilder
{
    /**
     * Applied joins to avoid duplicates
     *
     * @var array<string, bool>
     */
    private array $appliedJoins = [];

    /**
     * Build and execute the query
     */
    public function build(QueryOptions $options): LengthAwarePaginator
    {
        // Auto-join relationships from columns
        $this->applyAutoJoins($options->query, $options->columns);

        // Apply search
        if ($options->search !== '') {
            $this->applySearch($options->query, $options->columns, $options->search);
        }

        // Apply filters
        $this->applyFilters($options->query, $options->filters, $options->filterValues);

        // Apply sorting
        if ($options->sortBy !== null && $options->sortBy !== '') {
            $this->applySorting(new SortOptions(
                query: $options->query,
                columns: $options->columns,
                sortBy: $options->sortBy,
                sortDirection: $options->sortDirection,
            ));
        }

        // Paginate
        return $options->query->paginate($options->perPage);
    }

    /**
     * Auto-detect and apply joins from column relationships
     *
     * @param  array<int, Column>  $columns
     */
    private function applyAutoJoins(Builder $query, array $columns): void
    {
        foreach ($columns as $column) {
            if (! $column->hasRelationship()) {
                continue;
            }

            $parsed = $column->parseRelationship();
            $this->joinRelationships($query, $parsed['relationships']);
        }
    }

    /**
     * Join relationships recursively
     *
     * @param  array<int, string>  $relationships
     * @param  string  $parentTable  Parent table name
     * @param  string  $parentKey  Parent foreign key
     */
    private function joinRelationships(
        Builder $query,
        array $relationships,
        string $parentTable = '',
        string $parentKey = '',
    ): void {
        if (empty($relationships)) {
            return;
        }

        // Get model instance to access relationships
        $model = $query->getModel();

        if ($parentTable === '') {
            $parentTable = $model->getTable();
        }

        $relationshipName = array_shift($relationships);

        // Check if already joined
        $joinKey = "{$parentTable}.{$relationshipName}";
        if (isset($this->appliedJoins[$joinKey])) {
            // Continue with remaining relationships
            if (! empty($relationships)) {
                $relatedModel = $model->{$relationshipName}()->getRelated();
                $this->joinRelationships(
                    $query,
                    $relationships,
                    $relatedModel->getTable(),
                    $relationshipName,
                );
            }

            return;
        }

        // Get relationship
        if (! \method_exists($model, $relationshipName)) {
            // Relationship doesn't exist, skip
            return;
        }

        $relation = $model->{$relationshipName}();
        $relatedModel = $relation->getRelated();
        $relatedTable = $relatedModel->getTable();
        // Determine join type and keys based on relationship type
        $relationType = class_basename(get_class($relation));

        $alias = $relatedTable === $parentTable ? "{$relatedTable}_{$relationshipName}" : $relatedTable;

        $joinOptions = new JoinOptions(
            query: $query,
            relation: $relation,
            parentTable: $parentTable,
            relatedTable: $relatedTable,
            alias: $alias,
        );

        match ($relationType) {
            'BelongsTo' => $this->applyBelongsToJoin($joinOptions),
            'HasOne', 'HasMany' => $this->applyHasJoin($joinOptions),
            'BelongsToMany' => $this->applyBelongsToManyJoin($joinOptions),
            default => null,
        };

        $this->appliedJoins[$joinKey] = true;

        // Continue with remaining relationships
        if (! empty($relationships)) {
            $this->joinRelationships(
                $query,
                $relationships,
                $alias,
                $relationshipName,
            );
        }
    }

    /**
     * Apply BelongsTo join
     */
    /**
     * Apply BelongsTo join
     */
    private function applyBelongsToJoin(JoinOptions $options): void
    {
        $foreignKey = $options->relation->getForeignKeyName();
        $ownerKey = $options->relation->getOwnerKeyName();

        $tableClause = $options->alias === $options->relatedTable ? $options->relatedTable : "{$options->relatedTable} as {$options->alias}";

        $options->query->leftJoin(
            $tableClause,
            "{$options->parentTable}.{$foreignKey}",
            '=',
            "{$options->alias}.{$ownerKey}",
        );
    }

    /**
     * Apply HasOne/HasMany join
     */
    private function applyHasJoin(JoinOptions $options): void
    {
        $foreignKey = $options->relation->getForeignKeyName();
        $localKey = $options->relation->getLocalKeyName();

        $tableClause = $options->alias === $options->relatedTable ? $options->relatedTable : "{$options->relatedTable} as {$options->alias}";

        $options->query->leftJoin(
            $tableClause,
            "{$options->parentTable}.{$localKey}",
            '=',
            "{$options->alias}.{$foreignKey}",
        );
    }

    /**
     * Apply BelongsToMany join (through pivot table)
     */
    private function applyBelongsToManyJoin(JoinOptions $options): void
    {
        $pivotTable = $options->relation->getTable();
        $foreignPivotKey = $options->relation->getForeignPivotKeyName();
        $relatedPivotKey = $options->relation->getRelatedPivotKeyName();
        $parentKey = $options->relation->getParentKeyName();
        $relatedKey = $options->relation->getRelatedKeyName();

        // Join pivot table
        $options->query->leftJoin(
            $pivotTable,
            "{$options->parentTable}.{$parentKey}",
            '=',
            "{$pivotTable}.{$foreignPivotKey}",
        );

        $tableClause = $options->alias === $options->relatedTable ? $options->relatedTable : "{$options->relatedTable} as {$options->alias}";

        // Join related table
        $options->query->leftJoin(
            $tableClause,
            "{$pivotTable}.{$relatedPivotKey}",
            '=',
            "{$options->alias}.{$relatedKey}",
        );
    }

    /**
     * Apply search across searchable columns
     *
     * @param  array<int, Column>  $columns
     */
    private function applySearch(Builder $query, array $columns, string $search): void
    {
        $baseQuery = $query; // Store reference to outer query

        $query->where(function ($q) use ($columns, $search, $baseQuery) {
            foreach ($columns as $column) {
                if (! $column->isSearchable()) {
                    continue;
                }

                // Check for custom search callback
                $searchCallback = $column->getSearchCallback();
                if ($searchCallback !== null) {
                    ($searchCallback)($q, $search);

                    continue;
                }

                // Default search behavior
                $field = $column->getField();
                if ($field === null) {
                    continue;
                }

                // Handle relationship fields
                if ($column->hasRelationship()) {
                    $parsed = $column->parseRelationship();
                    $table = $this->getTableForRelationship($baseQuery, $parsed['relationships']);
                    $q->orWhere("{$table}.{$parsed['column']}", 'LIKE', "%{$search}%");
                } else {
                    $table = $baseQuery->getModel()->getTable();
                    $q->orWhere("{$table}.{$field}", 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply filters
     *
     * @param  array<int, Filter>  $filters
     * @param  array<string, mixed>  $filterValues
     */
    private function applyFilters(Builder $query, array $filters, array $filterValues): void
    {
        foreach ($filters as $filter) {
            $key = $filter->getKey();

            if (! isset($filterValues[$key]) || $filterValues[$key] === '' || $filterValues[$key] === null) {
                continue;
            }

            $value = $filterValues[$key];

            // Check for custom execute callback
            $execute = $filter->getExecute();
            if ($execute !== null) {
                ($execute)($query, $value, $key);

                continue;
            }

            // Apply value mapping
            $valueMapping = $filter->getValueMapping();
            if ($valueMapping !== null && isset($valueMapping[$value])) {
                $value = $valueMapping[$value];
            }

            // Get field name
            $field = $filter->getFieldMapping() ?? $key;

            // Handle relationship filters
            $relationship = $filter->getRelationship();
            if ($relationship !== null) {
                $query->whereHas($relationship['name'], function ($q) use ($relationship, $value) {
                    $q->where($relationship['column'], $value);
                });

                continue;
            }

            // Handle special value mappings
            if ($value === 'not_null') {
                $query->whereNotNull($field);

                continue;
            }

            if ($value === 'null') {
                $query->whereNull($field);

                continue;
            }

            // Default filter behavior
            if (\is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Apply sorting
     */
    private function applySorting(SortOptions $options): void
    {
        // Find the column
        $column = collect($options->columns)->first(fn (Column $col) => $col->getField() === $options->sortBy);

        if ($column === null) {
            return;
        }

        // Check for custom sort callback
        $sortCallback = $column->getSortCallback();
        if ($sortCallback !== null) {
            ($sortCallback)($options->query, $options->sortDirection);

            return;
        }

        // Default sort behavior
        if ($column->hasRelationship()) {
            $parsed = $column->parseRelationship();
            $table = $this->getTableForRelationship($options->query, $parsed['relationships']);
            $options->query->orderBy("{$table}.{$parsed['column']}", $options->sortDirection);
        } else {
            $table = $options->query->getModel()->getTable();
            $options->query->orderBy("{$table}.{$options->sortBy}", $options->sortDirection);
        }
    }

    /**
     * Get table name for a relationship path
     *
     * @param  array<int, string>  $relationships
     */
    private function getTableForRelationship(Builder $query, array $relationships): string
    {
        $model = $query->getModel();

        foreach ($relationships as $relationshipName) {
            if (! \method_exists($model, $relationshipName)) {
                // Relationship doesn't exist, return base table
                return $model->getTable();
            }

            $relation = $model->{$relationshipName}();
            $model = $relation->getRelated();
        }

        return $model->getTable();
    }
}
