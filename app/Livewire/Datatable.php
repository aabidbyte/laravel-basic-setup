<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Constants\DataTable\DataTable as DataTableConstants;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\DataTable\DataTableQueryBuilder;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Base DataTable Component
 *
 * Provides self-rendering datatable functionality with shared template.
 * Individual datatables only need to provide configuration (columns, filters, actions).
 */
abstract class Datatable extends Component
{
    use WithPagination;

    /**
     * Search term
     */
    public string $search = '';

    /**
     * Sort column
     */
    public string $sortBy = '';

    /**
     * Sort direction
     */
    public string $sortDirection = 'asc';

    /**
     * Items per page
     */
    public int $perPage = 15;

    /**
     * Filter values
     *
     * @var array<string, mixed>
     */
    public array $filters = [];

    /**
     * Selected row UUIDs
     *
     * @var array<int, string>
     */
    public array $selected = [];

    /**
     * Track which parameters were loaded from query string
     *
     * @var array<string, bool>
     */
    protected array $queryStringLoaded = [];

    /**
     * Get current page UUIDs
     *
     * @return array<int, string>
     */
    #[Computed]
    public function currentPageUuids(): array
    {
        return $this->rows->pluck('uuid')->filter()->toArray();
    }

    /**
     * Get the base query
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Get the datatable identifier (full class name).
     */
    protected function getDatatableIdentifier(): string
    {
        return static::class;
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
        // parse_str() converts filters[role]=value into nested array: ["filters" => ["role" => "value"]]
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
     * Load preferences from FrontendPreferencesService.
     * Only loads preferences if not already set by query string parameters.
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
     * Save current state to FrontendPreferencesService (excludes search term).
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

    /**
     * Get column definitions
     *
     * @return array<int, Column>
     */
    abstract protected function columns(): array;

    /**
     * Get filter definitions (optional)
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [];
    }

    /**
     * Get row action definitions (optional)
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        return [];
    }

    /**
     * Get bulk action definitions (optional)
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [];
    }

    /**
     * Mount the component and load query string parameters and preferences.
     * Query string parameters take precedence over saved preferences.
     * If query string parameters are present, they are saved as new preferences.
     * Child classes that override mount() should call parent::mount() first.
     */
    public function mount(): void
    {
        // Load query string parameters first (they take precedence)
        $this->loadQueryStringParameters();

        // If any query string parameters were loaded, save them as preferences
        if (! empty($this->queryStringLoaded)) {
            $this->savePreferences();
            // Clean URL after processing query parameters
            $this->cleanUrlQueryParameters();
        } else {
            // Only load saved preferences if no query string parameters were provided
            $this->loadPreferences();
        }
    }

    /**
     * Clean URL query parameters after they are processed.
     * This removes query parameters from the browser URL for a cleaner appearance.
     */
    protected function cleanUrlQueryParameters(): void
    {
        if (! empty($this->queryStringLoaded)) {
            // Dispatch event to clean URL on the frontend
            $this->dispatch('datatable-clean-url');
        }
    }

    /**
     * Handle row click (optional)
     *
     * @param  string  $uuid  Row UUID
     */
    public function rowClicked(string $uuid): void
    {
        // Override in component if needed
    }

    /**
     * Get paginated rows
     */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $queryBuilder = new DataTableQueryBuilder;

        return $queryBuilder->build(
            query: $this->baseQuery(),
            columns: $this->columns(),
            filters: $this->getFilterDefinitions(),
            filterValues: $this->filters,
            search: $this->search,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage
        );
    }

    /**
     * Get share URL with all current query parameters
     *
     * @param  int|null  $page  Optional page number (if not provided, uses current page from paginator)
     */
    public function getShareUrl(?int $page = null): string
    {
        // Get the actual page URL (not Livewire update URL)
        // Try to get the referer first (original page URL), otherwise fall back to current URL
        $referer = request()->header('Referer');
        $currentUrl = url()->current();

        // If current URL is a Livewire update URL, use referer instead
        // Livewire update URLs look like: /livewire-{hash}/update
        if (preg_match('#/livewire-[^/]+/update#', $currentUrl)) {
            if ($referer) {
                // Extract just the path from referer (without query params, we'll add our own)
                $parsedReferer = parse_url($referer);
                $url = ($parsedReferer['scheme'] ?? 'http').'://'
                    .($parsedReferer['host'] ?? '')
                    .($parsedReferer['path'] ?? '/');
            } else {
                // Fallback: use current URL but JavaScript will fix it
                $url = $currentUrl;
            }
        } else {
            // Not a Livewire update URL, use current URL
            $url = $currentUrl;
        }

        // Build query parameters from all Livewire URL properties
        $queryParams = [];

        // Add search if not empty
        if (! empty($this->search)) {
            $queryParams[DataTableConstants::QUERY_PARAM_SEARCH] = $this->search;
        }

        // Add sort if not empty
        if (! empty($this->sortBy)) {
            $queryParams[DataTableConstants::QUERY_PARAM_SORT] = $this->sortBy;
        }

        // Add sort direction if not default
        if ($this->sortDirection !== 'asc') {
            $queryParams[DataTableConstants::QUERY_PARAM_DIRECTION] = $this->sortDirection;
        }

        // Add per page if not default
        if ($this->perPage !== 15) {
            $queryParams[DataTableConstants::QUERY_PARAM_PER_PAGE] = $this->perPage;
        }

        // Add filters
        foreach ($this->filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $queryParams[DataTableConstants::QUERY_PARAM_FILTERS."[{$key}]"] = $value;
            }
        }

        // Add page number if not first page
        // Use provided page, or paginator's current page, or getPage() as fallback
        $currentPage = $page ?? ($this->rows->currentPage() ?? $this->getPage());
        if ($currentPage > 1) {
            $queryParams[DataTableConstants::QUERY_PARAM_PAGE] = $currentPage;
        }

        // Build query string from parameters
        $queryString = ! empty($queryParams) ? '?'.http_build_query($queryParams) : '';

        // Return URL with query string
        return $url.$queryString;
    }

    /**
     * Get columns for view
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return collect($this->columns())
            ->reject(fn (Column $column) => $column->isHidden())
            ->map(fn (Column $column) => $column->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Get filters for view
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFilters(): array
    {
        return collect($this->getFilterDefinitions())
            ->filter(fn (Filter $filter) => $filter->isVisible())
            ->map(fn (Filter $filter) => $filter->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Get row actions for a specific row
     *
     * @param  mixed  $row  Row model instance
     * @return array<int, array<string, mixed>>
     */
    public function getRowActionsForRow(mixed $row): array
    {
        return collect($this->rowActions())
            ->filter(fn (Action $action) => $action->isVisible($row))
            ->map(function (Action $action) use ($row) {
                $array = $action->toArray();

                // Resolve route if it's a closure
                $route = $action->getRoute();
                if ($route instanceof \Closure) {
                    $array['route'] = $route($row);
                }

                return $array;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get bulk actions for view
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBulkActions(): array
    {
        return collect($this->bulkActions())
            ->filter(fn (BulkAction $action) => $action->isVisible())
            ->map(fn (BulkAction $action) => $action->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Get active filters with their labels
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveFilters(): array
    {
        $filterDefinitions = collect($this->getFilterDefinitions());

        return collect($this->filters)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(function ($value, $key) use ($filterDefinitions) {
                $filter = $filterDefinitions->first(fn (Filter $f) => $f->getKey() === $key);

                if ($filter === null) {
                    return null;
                }

                // Get the label for the value
                $valueLabel = $value;
                if ($filter->getType() === 'select') {
                    $options = $filter->getOptions();
                    $valueLabel = $options[$value] ?? $value;
                }

                return [
                    'key' => $key,
                    'label' => $filter->getLabel(),
                    'value' => $value,
                    'valueLabel' => $valueLabel,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Render a column value
     *
     * @param  array<string, mixed>  $columnData  Column array data
     * @param  mixed  $row  Row model instance
     */
    public function renderColumn(array $columnData, mixed $row): string
    {

        // Find the Column instance
        $column = collect($this->columns())
            ->first(fn (Column $col) => $col->getField() === $columnData['field']);

        if ($column === null) {
            return '';
        }

        // Check for content callback with component type - use resolve method
        if ($column->getContentCallback() !== null && $column->getComponentType() !== null) {

            return $column->resolve($row);
        }

        // Check for label callback (non-DB column)
        $labelCallback = $column->getLabelCallback();
        if ($labelCallback !== null) {
            $value = $labelCallback($row, $column);

            return $column->isHtml() ? $value : e($value);
        }

        // Get value from row
        $field = $column->getField();
        if ($field === null) {
            return '';
        }

        // Handle relationship fields (e.g., 'address.city.name')
        if ($column->hasRelationship()) {
            $value = data_get($row, $field);
        } else {
            $value = $row->{$field} ?? '';
        }

        // Check for custom view
        $view = $column->getView();
        if ($view !== null) {
            return view($view, ['value' => $value, 'row' => $row, 'column' => $column])->render();
        }

        // Check for format callback
        $format = $column->getFormat();
        if ($format !== null) {
            $value = $format($value, $row, $column);
        }

        // Apply search highlighting if searchable and search term exists
        if ($column->isSearchable() && ! empty($this->search) && ! $column->isHtml()) {
            $value = $this->highlightSearchTerm($value, $this->search);
        }

        // Return escaped or HTML
        return $column->isHtml() ? $value : e($value);
    }

    /**
     * Highlight search term in value
     *
     * @param  mixed  $value  Value to highlight
     * @param  string  $search  Search term
     */
    protected function highlightSearchTerm(mixed $value, string $search): string
    {
        if (empty($search) || empty($value)) {
            return (string) $value;
        }

        $value = (string) $value;
        $pattern = '/('.preg_quote($search, '/').')/i';

        return preg_replace($pattern, '<mark class="bg-warning/30 px-1 rounded">$1</mark>', $value);
    }

    /**
     * Sort by column
     *
     * @param  string  $field  Field name
     */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
        $this->savePreferences();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->filters = [];
        $this->resetPage();
        $this->savePreferences();
    }

    /**
     * Remove a specific filter
     *
     * @param  string  $key  Filter key
     */
    public function removeFilter(string $key): void
    {
        unset($this->filters[$key]);
        $this->resetPage();
        $this->savePreferences();
    }

    /**
     * Toggle select all rows on current page
     */
    public function toggleSelectAll(): void
    {
        $currentPageUuids = $this->currentPageUuids;

        if ($this->isAllSelected()) {
            // Deselect current page UUIDs
            $this->selected = $this->normalizeArray(array_diff($this->selected, $currentPageUuids));
        } else {
            // Select all UUIDs on current page
            $this->selected = $this->normalizeArray(array_unique(array_merge($this->selected, $currentPageUuids)));
        }
    }

    /**
     * Check if a row is selected
     *
     * @param  string  $uuid  Row UUID
     */
    public function isSelected(string $uuid): bool
    {
        return in_array($uuid, $this->selected, true);
    }

    /**
     * Toggle selection of a single row
     *
     * @param  string  $uuid  Row UUID
     */
    public function toggleRow(string $uuid): void
    {
        if ($this->isSelected($uuid)) {
            $this->selected = $this->normalizeArray(array_filter($this->selected, fn ($id) => $id !== $uuid));
        } else {
            $this->selected[] = $uuid;
            $this->selected = $this->normalizeArray(array_unique($this->selected));
        }
    }

    /**
     * Normalize array to have sequential numeric keys
     *
     * @param  array<int, string>  $array
     * @return array<int, string>
     */
    protected function normalizeArray(array $array): array
    {
        return array_values($array);
    }

    /**
     * Clear all selections
     */
    public function clearSelection(): void
    {
        $this->selected = [];
    }

    /**
     * Get count of selected rows
     */
    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selected);
    }

    /**
     * Check if any rows are selected
     */
    #[Computed]
    public function hasSelection(): bool
    {
        return count($this->selected) > 0;
    }

    /**
     * Check if all rows on current page are selected
     */
    #[Computed]
    public function isAllSelected(): bool
    {
        // Return false if no selections
        if (empty($this->selected)) {
            return false;
        }

        $currentPageUuids = $this->currentPageUuids;

        // Return false if no rows on current page
        if (empty($currentPageUuids)) {
            return false;
        }

        // Check if all current page UUIDs are in selected array
        $intersection = array_intersect($this->selected, $currentPageUuids);

        return count($intersection) === count($currentPageUuids);
    }

    /**
     * Find a row action by key
     *
     * @param  string  $actionKey  Action key
     */
    protected function findRowAction(string $actionKey): ?Action
    {
        return collect($this->rowActions())
            ->first(fn (Action $a) => $a->getKey() === $actionKey);
    }

    /**
     * Find a bulk action by key
     *
     * @param  string  $actionKey  Action key
     */
    protected function findBulkAction(string $actionKey): ?BulkAction
    {
        return collect($this->bulkActions())
            ->first(fn (BulkAction $a) => $a->getKey() === $actionKey);
    }

    /**
     * Find a model by UUID, checking current page first to avoid DB query
     *
     * @param  string  $uuid  Row UUID
     * @return mixed|null
     */
    protected function findModelByUuid(string $uuid): mixed
    {
        // First, try to find in already-loaded current page rows
        $model = $this->rows->firstWhere('uuid', $uuid);
        if ($model !== null) {
            return $model;
        }

        // If not found in current page, query database
        return $this->baseQuery()->where('uuid', $uuid)->first();
    }

    /**
     * Get confirmation configuration for an action
     *
     * @param  string  $actionKey  Action key
     * @param  string  $uuid  Row UUID
     * @return array<string, mixed>
     */
    public function getActionConfirmation(string $actionKey, string $uuid): array
    {
        $action = $this->findRowAction($actionKey);

        if ($action === null || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        $model = $this->findModelByUuid($uuid);
        if ($model === null) {
            return ['required' => false];
        }

        $config = $action->resolveConfirmation($model);
        $config['required'] = true;

        return $config;
    }

    /**
     * Execute a row action
     *
     * @param  string  $actionKey  Action key
     * @param  string  $uuid  Row UUID
     */
    public function executeAction(string $actionKey, string $uuid): void
    {
        $action = $this->findRowAction($actionKey);
        if ($action === null) {
            return;
        }

        $model = $this->findModelByUuid($uuid);
        if ($model === null || ! $action->isVisible($model)) {
            return;
        }

        $execute = $action->getExecute();
        if ($execute !== null) {
            $execute($model);
            $this->dispatch('$refresh');
        }
    }

    /**
     * Get models by UUIDs, checking current page first to minimize DB queries
     *
     * @param  array<int, string>  $uuids  Array of UUIDs
     */
    protected function findModelsByUuids(array $uuids): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($uuids)) {
            return \Illuminate\Database\Eloquent\Collection::make();
        }

        $models = \Illuminate\Database\Eloquent\Collection::make();
        $uuidsToQuery = [];

        // First, try to find in already-loaded current page rows
        foreach ($uuids as $uuid) {
            $model = $this->rows->firstWhere('uuid', $uuid);
            if ($model !== null) {
                $models->push($model);
            } else {
                $uuidsToQuery[] = $uuid;
            }
        }

        // Query database only for UUIDs not found in current page
        if (! empty($uuidsToQuery)) {
            $queriedModels = $this->baseQuery()->whereIn('uuid', $uuidsToQuery)->get();
            $models = $models->merge($queriedModels);
        }

        return $models;
    }

    /**
     * Get confirmation configuration for a bulk action
     *
     * @param  string  $actionKey  Action key
     * @return array<string, mixed>
     */
    public function getBulkActionConfirmation(string $actionKey): array
    {
        $action = $this->findBulkAction($actionKey);

        if ($action === null || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        $models = $this->findModelsByUuids($this->selected);

        if ($models->isEmpty()) {
            return ['required' => false];
        }

        $config = $action->resolveConfirmation($models);
        $config['required'] = true;

        return $config;
    }

    /**
     * Execute a bulk action
     *
     * @param  string  $actionKey  Action key
     */
    public function executeBulkAction(string $actionKey): void
    {
        if (empty($this->selected)) {
            return;
        }

        $action = $this->findBulkAction($actionKey);
        if ($action === null || ! $action->isVisible()) {
            return;
        }

        $models = $this->findModelsByUuids($this->selected);
        if ($models->isEmpty()) {
            return;
        }

        $execute = $action->getExecute();
        if ($execute !== null) {
            $execute($models);
            $this->dispatch('$refresh');
        }

        $this->selected = [];
    }

    /**
     * Clear selections and optionally reset page
     *
     * @param  bool  $resetPage  Whether to reset page to first page
     */
    protected function clearSelections(bool $resetPage = false): void
    {
        $this->selected = [];

        if ($resetPage) {
            $this->resetPage();
        }
    }

    /**
     * Lifecycle: Updated search
     */
    public function updatedSearch(): void
    {
        $this->clearSelections(resetPage: true);
    }

    /**
     * Lifecycle: Updated filters
     */
    public function updatedFilters(): void
    {
        $this->clearSelections(resetPage: true);
        $this->savePreferences();
    }

    /**
     * Lifecycle: Updated per page
     */
    public function updatedPerPage(): void
    {
        $this->clearSelections(resetPage: true);
        $this->savePreferences();
    }

    /**
     * Lifecycle: Updated sort
     */
    public function updatedSortBy(): void
    {
        $this->clearSelections();
        $this->savePreferences();
    }

    /**
     * Lifecycle: Updated sort direction
     */
    public function updatedSortDirection(): void
    {
        $this->clearSelections();
    }

    /**
     * Lifecycle: Updated page (pagination)
     */
    public function updatedPage(): void
    {
        $this->clearSelections();
    }

    /**
     * Render the datatable using shared template
     */
    public function render()
    {
        return view('components.datatable');
    }
}
