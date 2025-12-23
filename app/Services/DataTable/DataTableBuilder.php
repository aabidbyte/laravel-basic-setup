<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Services\DataTable\Contracts\DataTableBuilderInterface;
use App\Services\DataTable\Services\FilterService;
use App\Services\DataTable\Services\SearchService;
use App\Services\DataTable\Services\SessionService;
use App\Services\DataTable\Services\SortService;
use App\Services\DataTable\Services\StatsService;

/**
 * Builder for DataTable responses
 *
 * Orchestrates the building of DataTable responses by applying
 * search, filters, sorting, and optionally calculating statistics.
 */
class DataTableBuilder implements DataTableBuilderInterface
{
    public function __construct(
        private SearchService $searchService,
        private FilterService $filterService,
        private SortService $sortService,
        private StatsService $statsService,
        private SessionService $sessionService
    ) {}

    /**
     * Build complete DataTable response
     */
    public function build(DataTableRequest $request): DataTableResponse
    {
        $config = $request->getConfig();
        $query = $request->getBaseQuery();
        $httpRequest = $request->getRequest();

        // Apply search, filters, and sorting
        $this->searchService->apply($query, $request);
        $this->filterService->apply($query, $request);
        $this->sortService->apply($query, $request);

        // Store session state
        $this->sessionService->storeAppliedFilters($request);

        // Build response
        $response = new DataTableResponse;
        $response->setViewName($request->getViewName() ?? '');
        $response->setRequest($request->getRequest());

        // Always include data and meta
        $this->buildDataResponse($response, $query, $request);

        // Conditional includes based on config
        if ($config->includeStats()) {
            $stats = $this->statsService->calculate($query, $request);
            $response->setStats($stats);
        }

        if ($config->includeConfig()) {
            $configResponse = $this->buildConfigResponse($config, $httpRequest);
            $response->setConfig($configResponse);
        }

        if ($config->includeFilterState()) {
            $filterState = $this->sessionService->getAppliedFilters($request->getEntityKey());
            $response->setFilterState($filterState);
        }

        return $response;
    }

    /**
     * Build data response with pagination
     */
    private function buildDataResponse(DataTableResponse $response, $query, DataTableRequest $request): void
    {
        $perPage = $request->getRequest()->input('per_page', 10);
        $paginatedData = $query->paginate($perPage);

        $transformer = $request->getTransformer();
        $transformedData = $paginatedData->through(fn ($item) => $transformer->transform($item));

        $response->setData($transformedData->items());
        $response->setMeta([
            'current_page' => $paginatedData->currentPage(),
            'last_page' => $paginatedData->lastPage(),
            'from' => $paginatedData->firstItem(),
            'to' => $paginatedData->lastItem(),
            'total' => $paginatedData->total(),
            'per_page' => $paginatedData->perPage(),
            'links' => $paginatedData->linkCollection()->toArray(),
        ]);
    }

    /**
     * Build config response for frontend
     *
     * @return array<string, mixed>
     */
    private function buildConfigResponse($config, $request): array
    {
        // Get current filters from request for dependent filtering
        $currentFilters = $request->input('filters', []);

        return [
            'searchable_fields' => $config->getSearchableFields(),
            'filterable_fields' => $this->filterService->buildFilterOptions($config->getFilterableFields()),
            'sortable_fields' => $config->getSortableFields(),
            'bulk_actions' => $config->getBulkActions(),
            'has_search' => ! empty($config->getSearchableFields()),
            'has_filters' => ! empty($config->getFilterableFields()),
            'has_bulk_actions' => ! empty($config->getBulkActions()),
        ];
    }
}
