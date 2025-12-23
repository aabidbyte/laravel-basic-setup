<?php

declare(strict_types=1);

namespace App\Services\DataTable\Services;

use App\Services\DataTable\Contracts\OptionsProviderInterface;
use App\Services\DataTable\DataTableRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for applying filters to DataTable queries
 */
class FilterService
{
    /**
     * Apply filters to query
     * Accepts nested filter structure: ?filters[role]=admin&filters[status]=active
     */
    public function apply(Builder $query, DataTableRequest $request): Builder
    {
        $filters = $request->getRequest()->input('filters', []);
        $filterableFields = $request->getConfig()->getFilterableFields();

        foreach ($filters as $field => $value) {
            // Skip null, empty, or 'all' values
            if (! $value || $value === '' || $value === 'all' || ! isset($filterableFields[$field])) {
                continue;
            }

            $config = $filterableFields[$field];

            match ($config['type']) {
                'select' => $this->applySelectFilter($query, $field, $value, $config),
                'multiselect' => $this->applyMultiselectFilter($query, $field, $value, $config),
                'boolean' => $this->applyBooleanFilter($query, $field, $value),
                'relationship' => $this->applyRelationshipFilter($query, $field, $value, $config),
                'has_relationship' => $this->applyHasRelationshipFilter($query, $field, $value, $config),
                'date_range' => $this->applyDateRangeFilter($query, $field, $value, $config),
                default => $this->applyDefaultFilter($query, $field, $value)
            };
        }

        return $query;
    }

    /**
     * Apply multiselect filter
     */
    private function applyMultiselectFilter(Builder $query, string $field, $value, array $config): void
    {
        if (empty($value) || ! is_array($value)) {
            return;
        }

        // Handle relationship-based multiselect filters
        if (isset($config['relationship'])) {
            $query->whereHas($config['relationship']['name'], function ($q) use ($config, $value) {
                $q->whereIn($config['relationship']['column'], $value);
            });
        } else {
            // Handle direct field multiselect filters
            $actualField = $config['column'] ?? $field;
            $query->whereIn($actualField, $value);
        }
    }

    /**
     * Apply select filter
     */
    private function applySelectFilter(Builder $query, string $field, $value, array $config): void
    {
        if (isset($config['relationship'])) {
            // Special handling for Spatie roles with team context
            if ($config['relationship']['name'] === 'roles' && $this->isSpatiePermissionModel($query->getModel())) {
                $this->applySpatieRoleFilter($query, $value);
            } else {
                $query->whereHas($config['relationship']['name'], function ($q) use ($config, $value) {
                    $q->where($config['relationship']['column'], $value);
                });
            }
        } elseif (isset($config['field_mapping'])) {
            // Handle special field mapping (e.g., status -> is_active)
            $actualField = $config['field_mapping'];

            if (isset($config['value_mapping'])) {
                // Handle value mapping (e.g., 'active' -> true)
                $actualValue = $config['value_mapping'][$value] ?? $value;

                // Handle special 'not_null' and 'null' values
                if ($actualValue === 'not_null') {
                    $query->whereNotNull($actualField);
                } elseif ($actualValue === 'null') {
                    $query->whereNull($actualField);
                } elseif (is_array($actualValue)) {
                    // Check if using inverted filter (e.g., ['not' => [values]])
                    if (isset($actualValue['not'])) {
                        $query->whereNotIn($actualField, $actualValue['not']);
                    } else {
                        $query->whereIn($actualField, $actualValue);
                    }
                } else {
                    $query->where($actualField, $actualValue);
                }
            } else {
                $query->where($actualField, $value);
            }
        } elseif (isset($config['value_mapping'])) {
            // Handle value mapping without field mapping
            $actualValue = $config['value_mapping'][$value] ?? $value;
            $query->where($field, $actualValue);
        } else {
            $query->where($field, $value);
        }
    }

    /**
     * Check if model uses Spatie permission system
     */
    private function isSpatiePermissionModel($model): bool
    {
        return in_array(\Spatie\Permission\Traits\HasRoles::class, class_uses_recursive($model));
    }

    /**
     * Apply Spatie role filter using Eloquent (handles team context)
     */
    private function applySpatieRoleFilter(Builder $query, string $roleName): void
    {
        // Use direct query approach instead of whereHas relationship
        // This avoids issues with Spatie permission relationship configuration
        $query->whereIn('users.id', function ($subQuery) use ($roleName) {
            $subQuery->select('model_id')
                ->from('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('role_id', function ($roleQuery) use ($roleName) {
                    $roleQuery->select('id')
                        ->from('roles')
                        ->where('name', $roleName);
                });
        });
    }

    /**
     * Apply boolean filter
     */
    private function applyBooleanFilter(Builder $query, string $field, $value): void
    {
        $query->where($field, $value === 'true' || $value === true || $value === '1' || $value === 1);
    }

    /**
     * Apply relationship filter
     */
    private function applyRelationshipFilter(Builder $query, string $field, $value, array $config): void
    {
        $query->whereHas($config['relation'], function ($q) use ($config, $value) {
            $q->where($config['column'], $value);
        });
    }

    /**
     * Apply has relationship filter
     */
    private function applyHasRelationshipFilter(Builder $query, string $field, $value, array $config): void
    {
        if (! isset($config['relation'])) {
            return;
        }

        $relation = $config['relation'];

        // Check if filtering for "has" or "doesn't have"
        if ($value === 'true' || $value === true || $value === '1' || $value === 1) {
            $query->has($relation);
        } elseif ($value === 'false' || $value === false || $value === '0' || $value === 0) {
            $query->doesntHave($relation);
        }
    }

    /**
     * Apply date range filter
     */
    private function applyDateRangeFilter(Builder $query, string $field, $value, array $config): void
    {
        // Handle date range filter - value should be an array with 'from' and/or 'to' keys
        if (! is_array($value)) {
            return;
        }

        // Get the actual column to filter on (defaults to the field name)
        $column = $config['column'] ?? $field;

        // Apply 'from' date filter (greater than or equal to)
        if (isset($value['from']) && ! empty($value['from'])) {
            $query->whereDate($column, '>=', $value['from']);
        }

        // Apply 'to' date filter (less than or equal to)
        if (isset($value['to']) && ! empty($value['to'])) {
            $query->whereDate($column, '<=', $value['to']);
        }
    }

    /**
     * Apply default filter (LIKE search)
     */
    private function applyDefaultFilter(Builder $query, string $field, $value): void
    {
        $query->where($field, 'like', "%{$value}%");
    }

    /**
     * Build filter options for frontend
     *
     * @return array<string, array>
     */
    public function buildFilterOptions(array $filterableFields): array
    {
        $options = [];

        foreach ($filterableFields as $field => $config) {
            if (isset($config['options_provider'])) {
                $providerClass = $config['options_provider'];

                // Check if provider needs context
                if (isset($config['options_provider_context'])) {
                    $provider = new $providerClass($config['options_provider_context']);
                } else {
                    $provider = app($providerClass);
                }

                if ($provider instanceof OptionsProviderInterface) {
                    $config['options'] = $provider->getOptions();
                }
            }

            $options[$field] = $config;
        }

        return $options;
    }
}
