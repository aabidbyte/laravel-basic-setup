<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

use App\Constants\DataTable\DataTable as DataTableConstants;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait for handling DataTable URL query parameters logic.
 *
 * @property string $sortBy
 * @property string $sortDirection
 * @property int $perPage
 * @property array $filters
 * @property LengthAwarePaginator $rows
 *
 * @method void setPage(int $page)
 * @method void getPage()
 */
trait HasDatatableLivewireQueryParameters
{
    /**
     * Hook called when search term is updated - resets pagination
     */
    public function updatedSearch(): void
    {
        if (method_exists($this, 'applyChanges')) {
            $this->applyChanges();
        }
    }
    /**
     * Search term
     */
    public string $search = '';

    /**
     * Track which parameters were loaded from query string
     *
     * @var array<string, bool>
     */
    protected array $queryStringLoaded = [];

    /**
     * Get the query parameter name, optionally prefixed with alias.
     */
    protected function getQueryParamName(string $key): string
    {
        if ($this->queryStringAlias) {
            return "{$this->queryStringAlias}_{$key}";
        }
        return $key;
    }

    /**
     * Load query string parameters from request.
     * Query string parameters take precedence over saved preferences.
     */
    protected function loadQueryStringParameters(): void
    {
        $request = request();

        // Get the original request URL (check referer for Livewire update requests)
        $referer = $request->header('Referer');
        $currentUrl = $request->fullUrl();

        // Parse query parameters - prioritize referer for Livewire update requests
        $queryParams = [];

        // If this is a Livewire update request, get query string from referer
        if ($referer && preg_match('#/livewire-[^/]+/update#', $currentUrl)) {
            $parsedReferer = parse_url($referer);
            if (isset($parsedReferer['query'])) {
                parse_str($parsedReferer['query'], $queryParams);
            }
        } else {
            // For regular requests, use current request query parameters
            $queryParams = $request->query();
        }

        // Load search term
        $searchKey = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_SEARCH);
        if (isset($queryParams[$searchKey]) && $queryParams[$searchKey] !== null && $queryParams[$searchKey] !== '') {
            $this->search = (string) $queryParams[$searchKey];
            $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_SEARCH] = true;
        }

        // Load sort column
        $sortKey = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_SORT);
        if (isset($queryParams[$sortKey]) && $queryParams[$sortKey] !== null && $queryParams[$sortKey] !== '') {
            $this->sortBy = (string) $queryParams[$sortKey];
            $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_SORT] = true;
        }

        // Load sort direction
        $directionKey = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_DIRECTION);
        if (isset($queryParams[$directionKey]) && $queryParams[$directionKey] !== null) {
            $direction = (string) $queryParams[$directionKey];
            if (in_array($direction, ['asc', 'desc'], true)) {
                $this->sortDirection = $direction;
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_DIRECTION] = true;
            }
        }

        // Load per page
        $perPageKey = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_PER_PAGE);
        if (isset($queryParams[$perPageKey]) && $queryParams[$perPageKey] !== null) {
            $perPage = (int) $queryParams[$perPageKey];
            if ($perPage > 0) {
                $this->perPage = $perPage;
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_PER_PAGE] = true;
            }
        }

        // Load page number
        $pageKey = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_PAGE);
        if (isset($queryParams[$pageKey]) && $queryParams[$pageKey] !== null) {
            $page = (int) $queryParams[$pageKey];
            if ($page > 0) {
                $this->setPage($page);
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_PAGE] = true;
            }
        }

        // Load filters (handles filters[key]=value format)
        $filters = [];
        $filtersParam = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_FILTERS);

        // Check if filters exist as a nested array (from parse_str)
        if (isset($queryParams[$filtersParam]) && is_array($queryParams[$filtersParam])) {
            $filters = $queryParams[$filtersParam];
        } else {
            // Fallback: check for filters[key] format in flat array
            $filtersPrefix = $filtersParam.'[';
            foreach ($queryParams as $key => $value) {
                if (str_starts_with($key, $filtersPrefix) && str_ends_with($key, ']')) {
                    // Extract filter key from filters[key] format
                    $filterKey = substr($key, strlen($filtersPrefix), -1); // Remove "filters[" and "]"
                    if ($value !== null && $value !== '') {
                        $filters[$filterKey] = $value;
                    }
                }
            }
        }

        // Filter out empty values and apply
        $filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        if (! empty($filters)) {
            $this->filters = $filters;
            $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_FILTERS] = true;
        }
    }

    /**
     * Get share URL with all current query parameters
     *
     * @param  int|null  $page  Optional page number
     */
    public function getShareUrl(?int $page = null): string
    {
        $referer = request()->header('Referer');
        $currentUrl = url()->current();

        if (preg_match('#/livewire-[^/]+/update#', $currentUrl)) {
            if ($referer) {
                $parsedReferer = parse_url($referer);
                $url = ($parsedReferer['scheme'] ?? 'http').'://'
                    .($parsedReferer['host'] ?? '')
                    .($parsedReferer['path'] ?? '/');
            } else {
                $url = $currentUrl;
            }
        } else {
            $url = $currentUrl;
        }

        $queryParams = [];

        if (! empty($this->search)) {
            $queryParams[$this->getQueryParamName(DataTableConstants::QUERY_PARAM_SEARCH)] = $this->search;
        }

        if (! empty($this->sortBy)) {
            $queryParams[$this->getQueryParamName(DataTableConstants::QUERY_PARAM_SORT)] = $this->sortBy;
        }

        if ($this->sortDirection !== 'asc') {
            $queryParams[$this->getQueryParamName(DataTableConstants::QUERY_PARAM_DIRECTION)] = $this->sortDirection;
        }

        if ($this->perPage !== 15) {
            $queryParams[$this->getQueryParamName(DataTableConstants::QUERY_PARAM_PER_PAGE)] = $this->perPage;
        }

        $filtersParam = $this->getQueryParamName(DataTableConstants::QUERY_PARAM_FILTERS);
        foreach ($this->filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $queryParams[$filtersParam."[{$key}]"] = $value;
            }
        }

        $currentPage = $page ?? ($this->rows->currentPage() ?? $this->getPage());
        if ($currentPage > 1) {
            $queryParams[$this->getQueryParamName(DataTableConstants::QUERY_PARAM_PAGE)] = $currentPage;
        }

        $queryString = ! empty($queryParams) ? '?'.http_build_query($queryParams) : '';

        return $url.$queryString;
    }

    /**
     * Clean URL query parameters after they are processed.
     */
    /**
     * Clean URL query parameters after they are processed.
     */
    protected function cleanUrlQueryParameters(): void
    {
        if (! empty($this->queryStringLoaded)) {
            $this->dispatch("datatable:clean-url:{$this->getId()}");
        }
    }

    /**
     * Define query string parameters for Livewire
     */
    public function queryString(): array
    {
        return [];
    }
}
