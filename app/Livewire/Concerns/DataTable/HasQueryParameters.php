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
 * @method void setPage(int $page)
 * @method void getPage()
 */
trait HasQueryParameters
{
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
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_SEARCH]) && $queryParams[DataTableConstants::QUERY_PARAM_SEARCH] !== null && $queryParams[DataTableConstants::QUERY_PARAM_SEARCH] !== '') {
            $this->search = (string) $queryParams[DataTableConstants::QUERY_PARAM_SEARCH];
            $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_SEARCH] = true;
        }

        // Load sort column
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_SORT]) && $queryParams[DataTableConstants::QUERY_PARAM_SORT] !== null && $queryParams[DataTableConstants::QUERY_PARAM_SORT] !== '') {
            $this->sortBy = (string) $queryParams[DataTableConstants::QUERY_PARAM_SORT];
            $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_SORT] = true;
        }

        // Load sort direction
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_DIRECTION]) && $queryParams[DataTableConstants::QUERY_PARAM_DIRECTION] !== null) {
            $direction = (string) $queryParams[DataTableConstants::QUERY_PARAM_DIRECTION];
            if (in_array($direction, ['asc', 'desc'], true)) {
                $this->sortDirection = $direction;
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_DIRECTION] = true;
            }
        }

        // Load per page
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_PER_PAGE]) && $queryParams[DataTableConstants::QUERY_PARAM_PER_PAGE] !== null) {
            $perPage = (int) $queryParams[DataTableConstants::QUERY_PARAM_PER_PAGE];
            if ($perPage > 0) {
                $this->perPage = $perPage;
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_PER_PAGE] = true;
            }
        }

        // Load page number
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_PAGE]) && $queryParams[DataTableConstants::QUERY_PARAM_PAGE] !== null) {
            $page = (int) $queryParams[DataTableConstants::QUERY_PARAM_PAGE];
            if ($page > 0) {
                $this->setPage($page);
                $this->queryStringLoaded[DataTableConstants::QUERY_PARAM_PAGE] = true;
            }
        }

        // Load filters (handles filters[key]=value format)
        $filters = [];

        // Check if filters exist as a nested array (from parse_str)
        if (isset($queryParams[DataTableConstants::QUERY_PARAM_FILTERS]) && is_array($queryParams[DataTableConstants::QUERY_PARAM_FILTERS])) {
            $filters = $queryParams[DataTableConstants::QUERY_PARAM_FILTERS];
        } else {
            // Fallback: check for filters[key] format in flat array
            $filtersPrefix = DataTableConstants::QUERY_PARAM_FILTERS.'[';
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
            $queryParams[DataTableConstants::QUERY_PARAM_SEARCH] = $this->search;
        }

        if (! empty($this->sortBy)) {
            $queryParams[DataTableConstants::QUERY_PARAM_SORT] = $this->sortBy;
        }

        if ($this->sortDirection !== 'asc') {
            $queryParams[DataTableConstants::QUERY_PARAM_DIRECTION] = $this->sortDirection;
        }

        if ($this->perPage !== 15) {
            $queryParams[DataTableConstants::QUERY_PARAM_PER_PAGE] = $this->perPage;
        }

        foreach ($this->filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $queryParams[DataTableConstants::QUERY_PARAM_FILTERS."[{$key}]"] = $value;
            }
        }

        $currentPage = $page ?? ($this->rows->currentPage() ?? $this->getPage());
        if ($currentPage > 1) {
            $queryParams[DataTableConstants::QUERY_PARAM_PAGE] = $currentPage;
        }

        $queryString = ! empty($queryParams) ? '?'.http_build_query($queryParams) : '';

        return $url.$queryString;
    }

    /**
     * Clean URL query parameters after they are processed.
     */
    protected function cleanUrlQueryParameters(): void
    {
        if (! empty($this->queryStringLoaded)) {
            $this->dispatch("datatable:clean-url:{$this->getId()}");
        }
    }
}
