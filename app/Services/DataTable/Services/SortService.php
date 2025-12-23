<?php

declare(strict_types=1);

namespace App\Services\DataTable\Services;

use App\Services\DataTable\DataTableRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for applying sorting to DataTable queries
 */
class SortService
{
    /**
     * Apply single column sorting to query
     */
    public function apply(Builder $query, DataTableRequest $request): Builder
    {
        $httpRequest = $request->getRequest();
        $sortableFields = $request->getConfig()->getSortableFields();

        $sortColumn = $httpRequest->input('sort_column');
        $sortDirection = $httpRequest->input('sort_direction', 'asc');

        // If no sort column is specified, apply default sort from config
        if (! $sortColumn) {
            $defaultSort = $request->getConfig()->getDefaultSort();
            if ($defaultSort && isset($defaultSort['column']) && isset($defaultSort['direction'])) {
                $sortColumn = $defaultSort['column'];
                $sortDirection = $defaultSort['direction'];
            } else {
                return $query;
            }
        }

        if (! $this->isSortableField($sortColumn, $sortableFields)) {
            return $query;
        }

        // Validate and normalize sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        // Apply optimized single sort
        return $this->applyOptimizedSort($query, $sortColumn, $sortDirection);
    }

    /**
     * Check if field is sortable (supports relation fields)
     */
    private function isSortableField(string $column, array $sortableFields): bool
    {
        // Direct field match
        return in_array($column, array_keys($sortableFields));
    }

    /**
     * Apply optimized single sort to query
     */
    private function applyOptimizedSort(Builder $query, string $column, string $direction): Builder
    {
        // Handle relation fields (e.g., 'team.name', 'role.name')
        if (str_contains($column, '.')) {
            return $this->applyRelationSort($query, $column, $direction);
        }

        // Regular field sorting
        return $query->orderBy($column, $direction);
    }

    /**
     * Apply relation sorting
     */
    private function applyRelationSort(Builder $query, string $relationPath, string $direction): Builder
    {
        $model = $query->getModel();
        $pathParts = explode('.', $relationPath);
        $relationName = $pathParts[0];
        $fieldName = $pathParts[1];

        // Check if relation exists
        if (! method_exists($model, $relationName)) {
            return $query;
        }

        $relation = $model->$relationName();
        $relatedModel = $relation->getRelated();
        $relatedTable = $relatedModel->getTable();
        $mainTable = $model->getTable();

        // Handle BelongsTo relationships
        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
            $foreignKey = $relation->getForeignKeyName();
            $ownerKey = $relation->getOwnerKeyName();

            // Use LEFT JOIN to include records without related data
            $query->leftJoin($relatedTable, "{$mainTable}.{$foreignKey}", '=', "{$relatedTable}.{$ownerKey}")
                ->select("{$mainTable}.*")
                ->orderBy("{$relatedTable}.{$fieldName}", $direction);
        } else {
            // For HasOne/HasMany, use subquery approach
            $localKey = $relation->getLocalKeyName();
            $foreignKey = $relation->getForeignKeyName();

            $subquery = $relatedModel->newQuery()
                ->select($fieldName)
                ->whereColumn($foreignKey, "{$mainTable}.{$localKey}")
                ->orderBy($fieldName, $direction)
                ->limit(1);

            $alias = "{$relatedTable}_{$fieldName}_sort";
            $query->addSelect([$alias => $subquery])
                ->orderBy($alias, $direction);
        }

        return $query;
    }
}
