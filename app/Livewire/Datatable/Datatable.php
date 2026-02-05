<?php

declare(strict_types=1);

namespace App\Livewire\DataTable;

use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\LivewireBaseComponent;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireActions;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireFilters;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireMemoization;
use App\Livewire\DataTable\Concerns\HasDatatableLivewirePagination;
use App\Livewire\DataTable\Concerns\HasDatatableLivewirePreferences;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireQueryParameters;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireRendering;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireSelection;
use App\Livewire\DataTable\Concerns\HasDatatableLivewireSorting;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\DataTableQueryBuilder;
use App\Support\DataTable\QueryOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use ReflectionMethod;

/**
 * Base DataTable Component
 *
 * Provides a shared, trait-based implementation for all datatables in the application.
 * Handles sorting, searching, filtering, pagination, and row actions.
 */
abstract class Datatable extends LivewireBaseComponent
{
    use HasDatatableLivewireActions;
    use HasDatatableLivewireFilters;
    use HasDatatableLivewireMemoization;
    use HasDatatableLivewirePagination;
    use HasDatatableLivewirePreferences;
    use HasDatatableLivewireQueryParameters;
    use HasDatatableLivewireRendering;
    use HasDatatableLivewireSelection;
    use HasDatatableLivewireSorting;

    /**
     * Placeholder type for lazy loading skeleton.
     */
    protected PlaceholderType $placeholderType = PlaceholderType::TABLE;

    /**
     * Number of rows for the table skeleton placeholder.
     */
    protected int $placeholderRows = 5;

    /**
     * Optional alias for namespacing query string parameters.
     * Useful when multiple datatables are on the same page.
     */
    public ?string $queryStringAlias = null;

    /**
     * Get the base query
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Get column definitions
     *
     * @return array<int, \App\Services\DataTable\Builders\Column>
     */
    abstract protected function columns(): array;

    /**
     * Track whether preferences have been initialized this request.
     */
    protected bool $preferencesInitialized = false;

    /**
     * Boot the component on every request lifecycle.
     * Loads preferences and query string parameters on initial mount only.
     * Unlike mount(), boot() is called every lifecycle and is rarely overridden.
     */
    public function boot(): void
    {
        // Only load preferences on initial mount (when dehydrated state is empty)
        // Livewire's boot() is called on every request, but we only want to load once.
        if (! $this->preferencesInitialized) {
            $this->initializePreferences();
            $this->preferencesInitialized = true;
        }
    }

    /**
     * Initialize preferences and query string parameters.
     * This is extracted to allow child classes to call it if needed.
     */
    protected function initializePreferences(): void
    {
        // 1. Load saved preferences first (defaults)
        $this->loadPreferences();

        // 2. Load query string parameters (overrides)
        $this->loadQueryStringParameters();

        // 3. If any query string parameters were loaded, save the merged state as preferences
        if (! empty($this->queryStringLoaded)) {
            $this->savePreferences();
            // Clean URL after processing query parameters
            $this->cleanUrlQueryParameters();
        }
    }

    #[Computed]
    public function datatableId(): string
    {
        return (string) $this->getId();
    }

    /**
     * Handle row click (optional)
     * Return Action for modal/route/execute, or void for custom handling
     *
     * @param  string  $uuid  Row UUID
     */
    public function rowClick(string $uuid): ?Action
    {
        return null;
    }

    /**
     * Determine if the row is clickable
     */
    /**
     * Determine if the row is clickable
     */
    #[Computed]
    public function rowsAreClickable(): bool
    {
        if (isset($this->rowsClickable)) {
            return $this->rowsClickable;
        }

        $reflector = new ReflectionMethod($this, 'rowClick');
        $this->rowsClickable = $reflector->getDeclaringClass()->getName() !== self::class;

        return $this->rowsClickable;
    }

    /**
     * Determine if row click action opens a modal (used for loading UX)
     *
     * Fetches a sample row to check if the rowClick action has hasModal set.
     * Returns false if table is empty or rowClick doesn't use a modal.
     */
    /**
     * Determine if row click action opens a modal (used for loading UX)
     *
     * Fetches a sample row to check if the rowClick action has hasModal set.
     * Returns false if table is empty or rowClick doesn't use a modal.
     */
    /**
     * Determine if row click action opens a modal (used for loading UX)
     *
     * Fetches a sample row to check if a rowClick action has hasModal set.
     * Returns false if table is empty or rowClick doesn't use a modal.
     */
    #[Computed]
    public function rowClickOpensModal(): bool
    {
        if (! $this->rowsAreClickable) {
            return false;
        }

        // Use loaded rows if available to avoid extra query
        $sampleRow = $this->rows->first();

        if (! $sampleRow) {
            return false;
        }

        // Get the action from rowClick and check if it has a modal
        $action = $this->rowClick($sampleRow->uuid);

        return $action?->getModal() !== null;
    }

    /**
     * Check if this datatable has filters defined.
     * Used by templates to conditionally render filter UI.
     */
    /**
     * Check if this datatable has filters defined.
     * Used by templates to conditionally render filter UI.
     */
    #[Computed]
    public function hasFilters(): bool
    {
        return \count($this->getFilterDefinitions()) > 0;
    }

    /**
     * Check if this datatable has bulk actions defined.
     * Used by templates to conditionally render selection checkboxes.
     */
    /**
     * Check if this datatable has bulk actions defined.
     * Used by templates to conditionally render selection checkboxes.
     */
    #[Computed]
    public function hasBulkActions(): bool
    {
        return \count($this->bulkActions()) > 0;
    }

    /**
     * Check if this datatable has row actions defined.
     * Used by templates to conditionally render the actions column.
     */
    /**
     * Check if this datatable has row actions defined.
     * Used by templates to conditionally render the actions column.
     */
    #[Computed]
    public function hasRowActions(): bool
    {
        return \count($this->rowActions()) > 0;
    }

    /**
     * Get paginated rows
     */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $queryBuilder = new DataTableQueryBuilder;

        return $queryBuilder->build(new QueryOptions(
            query: $this->baseQuery(),
            columns: $this->columns(),
            filters: $this->getFilterDefinitions(),
            filterValues: $this->filters,
            search: $this->search,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage,
        ));
    }

    /**
     * Apply changes to the table state (reset page, save preferences, refresh cache)
     */
    public function applyChanges(): void
    {
        if (\method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if (\method_exists($this, 'savePreferences')) {
            $this->savePreferences();
        }

        $this->refreshTable();
    }

    /**
     * Number of rows currently visible (for "Load More" behavior)
     */
    public int $visibleRows = 20;

    /**
     * Load more rows
     */
    public function loadMore(): void
    {
        $this->visibleRows += 20;
    }

    /**
     * Refresh the table by clearing the cache and scrolling to top
     */
    public function refreshTable(): void
    {
        unset($this->rows);
        $this->visibleRows = 20;
        $this->dispatch("datatable:scroll-to-top:{$this->getId()}");
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('components.datatable');
    }
}
