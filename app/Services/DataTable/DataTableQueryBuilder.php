<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
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
    public function build(
        Builder $query,
        array $columns,
        array $filters,
        array $filterValues,
        string $search,
        ?string $sortBy,
        string $sortDirection,
        int $perPage,
    ): LengthAwarePaginator {
        // Auto-join relationships from columns
        $this->applyAutoJoins($query, $columns);

        // Apply search
        if ($search !== '') {
            $this->applySearch($query, $columns, $search);
        }

        // Apply filters
        $this->applyFilters($query, $filters, $filterValues);

        // Apply sorting
        if ($sortBy !== null && $sortBy !== '') {
            $this->applySorting($query, $columns, $sortBy, $sortDirection);
        }

        // Paginate
        return $query->paginate($perPage);
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

        match ($relationType) {
            'BelongsTo' => $this->applyBelongsToJoin($query, $relation, $parentTable, $relatedTable, $alias),
            'HasOne', 'HasMany' => $this->applyHasJoin($query, $relation, $parentTable, $relatedTable, $alias),
            'BelongsToMany' => $this->applyBelongsToManyJoin($query, $relation, $parentTable, $relatedTable, $alias),
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
    private function applyBelongsToJoin(Builder $query, $relation, string $parentTable, string $relatedTable, string $alias): void
    {
        $foreignKey = $relation->getForeignKeyName();
        $ownerKey = $relation->getOwnerKeyName();

        $tableClause = $alias === $relatedTable ? $relatedTable : "{$relatedTable} as {$alias}";

        $query->leftJoin(
            $tableClause,
            "{$parentTable}.{$foreignKey}",
            '=',
            "{$alias}.{$ownerKey}",
        );
    }

    /**
     * Apply HasOne/HasMany join
     */
    private function applyHasJoin(Builder $query, $relation, string $parentTable, string $relatedTable, string $alias): void
    {
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getLocalKeyName();

        $tableClause = $alias === $relatedTable ? $relatedTable : "{$relatedTable} as {$alias}";

        $query->leftJoin(
            $tableClause,
            "{$parentTable}.{$localKey}",
            '=',
            "{$alias}.{$foreignKey}",
        );
    }

    /**
     * Apply BelongsToMany join (through pivot table)
     */
    private function applyBelongsToManyJoin(Builder $query, $relation, string $parentTable, string $relatedTable, string $alias): void
    {
        $pivotTable = $relation->getTable();
        $foreignPivotKey = $relation->getForeignPivotKeyName();
        $relatedPivotKey = $relation->getRelatedPivotKeyName();
        $parentKey = $relation->getParentKeyName();
        $relatedKey = $relation->getRelatedKeyName();

        // Join pivot table
        $query->leftJoin(
            $pivotTable,
            "{$parentTable}.{$parentKey}",
            '=',
            "{$pivotTable}.{$foreignPivotKey}",
        );

        $tableClause = $alias === $relatedTable ? $relatedTable : "{$relatedTable} as {$alias}";

        // Join related table
        $query->leftJoin(
            $tableClause,
            "{$pivotTable}.{$relatedPivotKey}",
            '=',
            "{$alias}.{$relatedKey}",
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
     *
     * @param  array<int, Column>  $columns
     */
    private function applySorting(Builder $query, array $columns, string $sortBy, string $sortDirection): void
    {
        // Find the column
        $column = collect($columns)->first(fn (Column $col) => $col->getField() === $sortBy);

        if ($column === null) {
            return;
        }

        // Check for custom sort callback
        $sortCallback = $column->getSortCallback();
        if ($sortCallback !== null) {
            ($sortCallback)($query, $sortDirection);

            return;
        }

        // Default sort behavior
        if ($column->hasRelationship()) {
            $parsed = $column->parseRelationship();
            $table = $this->getTableForRelationship($query, $parsed['relationships']);
            $query->orderBy("{$table}.{$parsed['column']}", $sortDirection);
        } else {
            $table = $query->getModel()->getTable();
            $query->orderBy("{$table}.{$sortBy}", $sortDirection);
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
