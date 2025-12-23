<?php

declare(strict_types=1);

namespace App\Livewire\DataTable;

use App\Services\DataTable\Contracts\DataTableBuilderInterface;
use App\Services\DataTable\Contracts\DataTableConfigInterface;
use App\Services\DataTable\Contracts\TransformerInterface;
use App\Services\DataTable\DataTablePreferencesService;
use App\Services\DataTable\DataTableRequest;
use App\Services\DataTable\Dsl\DataTableDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Base Livewire component for DataTable functionality
 *
 * This abstract class provides the foundation for entity-specific DataTable components.
 * It integrates with the DataTable service layer to provide search, filtering, sorting,
 * pagination, and bulk actions.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
abstract class BaseDataTableComponent extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url(as: 'search', history: true, keep: false)]
    public string $search = '';

    #[Url(as: 'sort', history: true, keep: false)]
    public string $sortBy = '';

    #[Url(as: 'direction', history: true, keep: false)]
    public string $sortDirection = 'asc';

    #[Url(as: 'per_page', history: true, keep: false)]
    public int $perPage = 15;

    public array $filters = [];

    public array $selected = [];

    public bool $selectPage = false;

    public bool $selectAll = false;

    /**
     * Modal state for row actions
     */
    public ?string $openRowActionModal = null;

    public ?string $openRowActionUuid = null;

    /**
     * Modal state for bulk actions
     */
    public ?string $openBulkActionModal = null;

    /**
     * Get the DataTable configuration instance
     */
    abstract protected function getConfig(): DataTableConfigInterface;

    /**
     * Get the DataTable definition (optional - if model uses HasDataTable)
     * Return null if not using DSL
     */
    protected function getDefinition(): ?DataTableDefinition
    {
        return null;
    }

    /**
     * Get the model class name (for action execution)
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the base Eloquent query builder
     *
     * @return Builder<TModel>
     */
    abstract protected function getBaseQuery(): Builder;

    /**
     * Get the transformer instance
     */
    abstract protected function getTransformer(): TransformerInterface;

    /**
     * Mount the component and authorize access
     */
    public function mount(): void
    {
        $this->authorizeAccess();
        $preferencesService = app(DataTablePreferencesService::class);
        $this->loadPreferences($preferencesService);
        $this->initializeDefaults();
    }

    /**
     * Authorize access to the DataTable
     * Override this method in child classes to add authorization logic
     */
    protected function authorizeAccess(): void
    {
        // Override in child classes if needed
    }

    /**
     * Load preferences from session/user preferences
     */
    protected function loadPreferences(?DataTablePreferencesService $preferencesService = null): void
    {
        if ($preferencesService === null) {
            $preferencesService = app(DataTablePreferencesService::class);
        }

        $entityKey = $this->getConfig()->getEntityKey();
        $preferences = $preferencesService->all($entityKey);

        // Load saved preferences if available
        if (! empty($preferences)) {
            $this->search = $preferences['search'] ?? $this->search;
            $this->perPage = $preferences['per_page'] ?? $this->perPage;

            if (isset($preferences['sort'])) {
                $this->sortBy = $preferences['sort']['column'] ?? $this->sortBy;
                $this->sortDirection = $preferences['sort']['direction'] ?? $this->sortDirection;
            }

            if (isset($preferences['filters']) && is_array($preferences['filters'])) {
                $this->filters = $preferences['filters'];
            }
        }
    }

    /**
     * Initialize default values from config
     */
    protected function initializeDefaults(): void
    {
        $config = $this->getConfig();
        $defaultSort = $config->getDefaultSort();

        if ($defaultSort && empty($this->sortBy)) {
            $this->sortBy = $defaultSort['column'] ?? '';
            $this->sortDirection = $defaultSort['direction'] ?? 'asc';
        }
    }

    /**
     * Get paginated and transformed rows
     */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $config = $this->getConfig();
        $baseQuery = $this->getBaseQuery();
        $transformer = $this->getTransformer();

        // Create a request object from Livewire properties
        $httpRequest = $this->createRequestFromProperties();

        // Create DataTable request
        $request = new DataTableRequest(
            $config,
            $baseQuery,
            $transformer,
            $httpRequest
        );

        // Build DataTable response using the builder
        $builder = app(DataTableBuilderInterface::class);
        $response = $builder->build($request);

        // Get paginated data
        $meta = $response->getMeta();
        $data = collect($response->getData());

        // Create a LengthAwarePaginator from the response
        return new LengthAwarePaginator(
            $data,
            $meta['total'],
            $meta['per_page'],
            $meta['current_page'],
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get DataTable statistics
     */
    #[Computed]
    public function stats(): ?array
    {
        $config = $this->getConfig();

        if (! $config->includeStats()) {
            return null;
        }

        $baseQuery = $this->getBaseQuery();
        $transformer = $this->getTransformer();

        $httpRequest = $this->createRequestFromProperties();

        $request = new DataTableRequest(
            $config,
            $baseQuery,
            $transformer,
            $httpRequest
        );

        $builder = app(DataTableBuilderInterface::class);
        $response = $builder->build($request);

        return $response->getStats();
    }

    /**
     * Get DataTable configuration for frontend
     */
    #[Computed]
    public function datatableConfig(): ?array
    {
        $config = $this->getConfig();

        if (! $config->includeConfig()) {
            return null;
        }

        $baseQuery = $this->getBaseQuery();
        $transformer = $this->getTransformer();

        $httpRequest = $this->createRequestFromProperties();

        $request = new DataTableRequest(
            $config,
            $baseQuery,
            $transformer,
            $httpRequest
        );

        $builder = app(DataTableBuilderInterface::class);
        $response = $builder->build($request);

        return $response->getConfig();
    }

    /**
     * Create HTTP request from Livewire properties
     */
    protected function createRequestFromProperties(): \Illuminate\Http\Request
    {
        $request = request()->duplicate();
        $request->merge([
            'search' => $this->search,
            'sort_column' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'per_page' => $this->perPage,
            'page' => $this->getPage(),
            'filters' => $this->filters,
        ]);

        return $request;
    }

    /**
     * Reset page when search changes
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
        $this->savePreferences();
    }

    /**
     * Reset page when filters change
     */
    public function updatedFilters(): void
    {
        $this->executeFilterClosures();
        $this->resetPage();
        $this->clearSelection();
        $this->savePreferences();
    }

    /**
     * Execute filter closures for changed filters
     */
    protected function executeFilterClosures(): void
    {
        $definition = $this->getDefinition();

        if ($definition === null) {
            return;
        }

        foreach ($this->filters as $key => $value) {
            $filter = $definition->getFilter($key);

            if ($filter === null) {
                continue;
            }

            $execute = $filter->getExecute();

            if ($execute !== null) {
                try {
                    ($execute)($value, $key);
                } catch (\Exception $e) {
                    // Log error but don't break the filter flow
                    Log::error('Filter execute closure failed', [
                        'filter' => $key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Reset page when per_page changes
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
        $this->savePreferences();
    }

    /**
     * Save preferences when sort changes
     */
    public function sortBy(string $key): void
    {
        if ($this->sortBy === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
        $this->savePreferences();
    }

    /**
     * Save current preferences to session/user preferences
     */
    protected function savePreferences(): void
    {
        $preferencesService = app(DataTablePreferencesService::class);
        $entityKey = $this->getConfig()->getEntityKey();

        $preferences = [];

        // Only include non-empty values
        if (! empty($this->search)) {
            $preferences['search'] = $this->search;
        }

        if ($this->perPage > 0) {
            $preferences['per_page'] = $this->perPage;
        }

        if (! empty($this->sortBy)) {
            $preferences['sort'] = [
                'column' => $this->sortBy,
                'direction' => $this->sortDirection,
            ];
        }

        if (! empty($this->filters)) {
            $preferences['filters'] = $this->filters;
        }

        if (! empty($preferences)) {
            $preferencesService->setMany($entityKey, $preferences);
        }
    }

    /**
     * Get headers configuration for table
     * Uses DSL definition if available, otherwise falls back to legacy method
     *
     * @return array<int, array<string, mixed>>
     */
    public function getHeaders(): array
    {
        $definition = $this->getDefinition();
        if ($definition !== null) {
            $viewData = $definition->toArrayForView();

            return $viewData['headers'];
        }

        // Legacy: use columns configuration
        return $this->getColumns();
    }

    /**
     * Get columns configuration for table
     * Uses DSL definition if available, otherwise falls back to legacy method
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        $definition = $this->getDefinition();
        if ($definition !== null) {
            $viewData = $definition->toArrayForView();
            $headers = $viewData['headers'];

            // Extract columns from headers
            return array_map(function ($header) {
                return $header['column'] ?? [];
            }, $headers);
        }

        return [];
    }

    /**
     * Get row actions configuration
     * Uses DSL definition if available, otherwise falls back to legacy method
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRowActions(): array
    {
        $definition = $this->getDefinition();
        if ($definition !== null) {
            $viewData = $definition->toArrayForView();

            return $viewData['rowActions'];
        }

        return [];
    }

    /**
     * Get bulk actions configuration
     * Uses DSL definition if available, otherwise falls back to config
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBulkActions(): array
    {
        $definition = $this->getDefinition();
        if ($definition !== null) {
            $viewData = $definition->toArrayForView();

            return $viewData['bulkActions'];
        }

        // Legacy: use config
        $config = $this->getConfig();

        return $config->getBulkActions();
    }

    /**
     * Get filters configuration
     * Uses DSL definition if available
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFilters(): array
    {
        $definition = $this->getDefinition();
        if ($definition !== null) {
            $viewData = $definition->toArrayForView();

            return $viewData['filters'];
        }

        // Legacy: use config
        $config = $this->getConfig();

        return array_map(function ($key, $filter) {
            return array_merge(['key' => $key], $filter);
        }, array_keys($config->getFilterableFields()), $config->getFilterableFields());
    }

    /**
     * Toggle select all on current page
     */
    public function toggleSelectPage(): void
    {
        $this->selectPage = ! $this->selectPage;

        if ($this->selectPage) {
            // Get current page UUIDs and merge with existing selection
            $currentPageUuids = $this->rows->pluck('uuid')->filter()->toArray();
            $this->selected = array_values(array_unique(array_merge($this->selected, $currentPageUuids)));
        } else {
            // Remove current page items from selection
            $currentPageUuids = $this->rows->pluck('uuid')->filter()->toArray();
            $this->selected = array_values(array_diff($this->selected, $currentPageUuids));
        }
    }

    /**
     * Toggle select all across all pages
     */
    public function toggleSelectAll(): void
    {
        $this->selectAll = ! $this->selectAll;
        $this->selectPage = false;

        if ($this->selectAll) {
            // Get all UUIDs matching current filters
            $config = $this->getConfig();
            $baseQuery = $this->getBaseQuery();
            $transformer = $this->getTransformer();

            $httpRequest = $this->createRequestFromProperties();
            // Remove pagination to get all results
            $httpRequest->merge(['per_page' => 10000, 'page' => 1]);

            $request = new DataTableRequest(
                $config,
                $baseQuery,
                $transformer,
                $httpRequest
            );

            $builder = app(DataTableBuilderInterface::class);
            $response = $builder->build($request);

            // Get all UUIDs from the data
            $this->selected = collect($response->getData())->pluck('uuid')->filter()->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Clear selection
     */
    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
    }

    /**
     * Get table data for unified component
     * Returns array with all data needed for the datatable component
     *
     * @return array{rows: array, headers: array, columns: array, actionsPerRow: array, bulkActions: array, selected: array, sortBy: string|null, sortDirection: string, paginator: LengthAwarePaginator|null}
     */
    #[Computed]
    public function tableData(): array
    {
        return [
            'rows' => $this->rows->items(),
            'headers' => $this->getHeaders(),
            'columns' => $this->getColumns(),
            'actionsPerRow' => $this->getRowActions(),
            'bulkActions' => $this->getBulkActions(),
            'selected' => $this->selected,
            'sortBy' => $this->sortBy ?: null,
            'sortDirection' => $this->sortDirection,
            'paginator' => $this->rows,
        ];
    }

    /**
     * Run row action (execute immediately or open modal)
     */
    public function runRowAction(string $actionKey, string $rowUuid): void
    {
        $definition = $this->getDefinition();
        if ($definition === null) {
            // Legacy: call handleRowAction
            $this->handleRowAction($actionKey, $rowUuid);

            return;
        }

        $action = $definition->getRowAction($actionKey);
        if ($action === null) {
            return;
        }

        // Check if action has modal
        $modal = $action->getModal();
        if ($modal !== null) {
            // Open modal instead of executing
            $this->openRowActionModal = $actionKey;
            $this->openRowActionUuid = $rowUuid;

            return;
        }

        // Check for special action keys that should be handled by child components
        if (in_array($actionKey, ['view', 'edit'], true)) {
            // Let child component handle these via handleRowAction
            $this->handleRowAction($actionKey, $rowUuid);

            return;
        }

        // Execute immediately
        $execute = $action->getExecute();
        if ($execute !== null) {
            $modelClass = $this->getModelClass();
            $model = $modelClass::where('uuid', $rowUuid)->first();
            if ($model !== null) {
                $execute($model);
                $this->dispatch('$refresh');
            }
        }
    }

    /**
     * Open row action modal
     */
    public function openRowActionModal(string $actionKey, string $rowUuid): void
    {
        $this->openRowActionModal = $actionKey;
        $this->openRowActionUuid = $rowUuid;
    }

    /**
     * Close row action modal
     */
    public function closeRowActionModal(): void
    {
        $this->openRowActionModal = null;
        $this->openRowActionUuid = null;
    }

    /**
     * Execute row action from modal (after confirmation)
     */
    public function executeRowActionFromModal(): void
    {
        if ($this->openRowActionModal === null || $this->openRowActionUuid === null) {
            return;
        }

        $actionKey = $this->openRowActionModal;
        $rowUuid = $this->openRowActionUuid;

        $definition = $this->getDefinition();
        if ($definition === null) {
            return;
        }

        $action = $definition->getRowAction($actionKey);
        if ($action === null) {
            return;
        }

        $execute = $action->getExecute();
        if ($execute !== null) {
            $modelClass = $this->getModelClass();
            $model = $modelClass::where('uuid', $rowUuid)->first();
            if ($model !== null) {
                $execute($model);
                $this->dispatch('$refresh');
            }
        }

        $this->closeRowActionModal();
    }

    /**
     * Run bulk action (execute immediately or open modal)
     */
    public function runBulkAction(string $actionKey): void
    {
        if (empty($this->selected)) {
            return;
        }

        $definition = $this->getDefinition();
        if ($definition === null) {
            // Legacy: call handleBulkAction
            $this->handleBulkAction($actionKey);

            return;
        }

        $action = $definition->getBulkAction($actionKey);
        if ($action === null) {
            return;
        }

        // Check if action has modal
        $modal = $action->getModal();
        if ($modal !== null) {
            // Open modal instead of executing
            $this->openBulkActionModal = $actionKey;

            return;
        }

        // Execute immediately
        $execute = $action->getExecute();
        if ($execute !== null) {
            $modelClass = $this->getModelClass();
            $models = $modelClass::whereIn('uuid', $this->selected)->get();
            if ($models->isNotEmpty()) {
                $execute($models);
                $this->dispatch('$refresh');
            }
        }

        $this->clearSelection();
    }

    /**
     * Open bulk action modal
     */
    public function openBulkActionModal(string $actionKey): void
    {
        $this->openBulkActionModal = $actionKey;
    }

    /**
     * Close bulk action modal
     */
    public function closeBulkActionModal(): void
    {
        $this->openBulkActionModal = null;
    }

    /**
     * Execute bulk action from modal (after confirmation)
     */
    public function executeBulkActionFromModal(): void
    {
        if ($this->openBulkActionModal === null || empty($this->selected)) {
            return;
        }

        $actionKey = $this->openBulkActionModal;

        $definition = $this->getDefinition();
        if ($definition === null) {
            return;
        }

        $action = $definition->getBulkAction($actionKey);
        if ($action === null) {
            return;
        }

        $execute = $action->getExecute();
        if ($execute !== null) {
            $modelClass = $this->getModelClass();
            $models = $modelClass::whereIn('uuid', $this->selected)->get();
            if ($models->isNotEmpty()) {
                $execute($models);
                $this->dispatch('$refresh');
            }
        }

        $this->closeBulkActionModal();
        $this->clearSelection();
    }

    /**
     * Handle row action (legacy method - override in child classes)
     */
    public function handleRowAction(string $action, string $uuid): void
    {
        // Override in child classes
    }

    /**
     * Handle bulk action (legacy method - override in child classes)
     */
    public function handleBulkAction(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Override in child classes
        $this->clearSelection();
    }

    /**
     * Handle row click
     */
    public function rowClicked(string $uuid): void
    {
        // Override in child classes
    }
}
