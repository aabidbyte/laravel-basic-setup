<?php

declare(strict_types=1);

namespace App\Services\DataTable\Services;

use App\Services\DataTable\DataTablePreferencesService;
use App\Services\DataTable\DataTableRequest;

/**
 * Service for managing DataTable session state
 * Uses DataTablePreferencesService to store preferences in session and user's frontend_preferences
 */
class SessionService
{
    public function __construct(
        private readonly DataTablePreferencesService $preferencesService
    ) {}

    /**
     * Store all DataTable preferences (filters, per_page, sort, etc.) in session and user preferences
     */
    public function storeAppliedFilters(DataTableRequest $request): void
    {
        $entityKey = $request->getEntityKey();
        $httpRequest = $request->getRequest();

        $preferences = [
            'search' => $httpRequest->input('search'),
            'filters' => array_filter($httpRequest->only(
                array_keys($request->getConfig()->getFilterableFields())
            )),
            'sort' => $this->getSortData($httpRequest),
            'per_page' => $httpRequest->input('per_page'),
            'timestamp' => now()->toISOString(),
        ];

        // Store all preferences using the preferences service
        // This will automatically save to DB for authenticated users and sync to session
        $this->preferencesService->setMany($entityKey, array_filter($preferences));
    }

    /**
     * Get applied filters from session/user preferences
     *
     * @return array<string, mixed>
     */
    public function getAppliedFilters(string $entityKey): array
    {
        return $this->preferencesService->all($entityKey);
    }

    /**
     * Clear applied filters from session and user preferences
     */
    public function clearAppliedFilters(string $entityKey): void
    {
        $this->preferencesService->clear($entityKey);
    }

    /**
     * Get sort data from request (single column sorting only)
     *
     * @return array{column: string, direction: string}|null
     */
    private function getSortData($httpRequest): ?array
    {
        $sortColumn = $httpRequest->input('sort_column');
        if ($sortColumn) {
            return [
                'column' => $sortColumn,
                'direction' => $httpRequest->input('sort_direction', 'asc'),
            ];
        }

        return null;
    }
}
