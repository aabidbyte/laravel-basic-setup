<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

use App\Constants\DataTable\DataTable as DataTableConstants;
use App\Services\FrontendPreferences\FrontendPreferencesService;

/**
 * Trait for handling DataTable user preferences logic.
 */
trait HasPreferences
{
    /**
     * Get the datatable identifier (full class name).
     */
    protected function getDatatableIdentifier(): string
    {
        return static::class;
    }

    /**
     * Load preferences from FrontendPreferencesService.
     */
    protected function loadPreferences(): void
    {
        $preferencesService = app(FrontendPreferencesService::class);
        $identifier = $this->getDatatableIdentifier();
        $request = request();

        // Load preferences only if not set by query string
        if (! ($this->queryStringLoaded[DataTableConstants::QUERY_PARAM_SORT] ?? false)) {
            $sortBy = $preferencesService->getDatatablePreference($identifier, 'sortBy', '', $request);
            if (! empty($sortBy)) {
                $this->sortBy = $sortBy;
            }
        }

        if (! ($this->queryStringLoaded[DataTableConstants::QUERY_PARAM_DIRECTION] ?? false)) {
            $sortDirection = $preferencesService->getDatatablePreference($identifier, 'sortDirection', 'asc', $request);
            if (! empty($sortDirection) && in_array($sortDirection, ['asc', 'desc'], true)) {
                $this->sortDirection = $sortDirection;
            }
        }

        if (! ($this->queryStringLoaded[DataTableConstants::QUERY_PARAM_PER_PAGE] ?? false)) {
            $perPage = $preferencesService->getDatatablePreference($identifier, 'perPage', 15, $request);
            if ($perPage > 0) {
                $this->perPage = $perPage;
            }
        }

        if (! ($this->queryStringLoaded[DataTableConstants::QUERY_PARAM_FILTERS] ?? false)) {
            $filters = $preferencesService->getDatatablePreference($identifier, 'filters', [], $request);
            if (is_array($filters) && ! empty($filters)) {
                $this->filters = $filters;
            }
        }
    }

    /**
     * Save current state to FrontendPreferencesService.
     */
    protected function savePreferences(): void
    {
        $preferencesService = app(FrontendPreferencesService::class);
        $identifier = $this->getDatatableIdentifier();

        $preferences = [
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
            'filters' => $this->filters,
        ];

        $preferencesService->setDatatablePreferences($identifier, $preferences);
    }
}
