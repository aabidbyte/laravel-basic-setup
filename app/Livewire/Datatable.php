<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Concerns\DataTable\HasDatatableLivewireActions;
use App\Livewire\Concerns\DataTable\HasDatatableLivewireFilters;
use App\Livewire\Concerns\DataTable\HasDatatableLivewirePagination;
use App\Livewire\Concerns\DataTable\HasDatatableLivewirePreferences;
use App\Livewire\Concerns\DataTable\HasDatatableLivewireQueryParameters;
use App\Livewire\Concerns\DataTable\HasDatatableLivewireRendering;
use App\Livewire\Concerns\DataTable\HasDatatableLivewireSelection;
use App\Livewire\Concerns\DataTable\HasDatatableLivewireSorting;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\DataTableQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use ReflectionMethod;

/**
 * Base DataTable Component
 *
 * Provides a shared, trait-based implementation for all datatables in the application.
 * Handles sorting, searching, filtering, pagination, and row actions.
 */
abstract class Datatable extends Component
{
    use HasDatatableLivewireActions;
    use HasDatatableLivewireFilters;
    use HasDatatableLivewirePagination;
    use HasDatatableLivewirePreferences;
    use HasDatatableLivewireQueryParameters;
    use HasDatatableLivewireRendering;
    use HasDatatableLivewireSelection;
    use HasDatatableLivewireSorting;

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
     * Mount the component and load query string parameters and preferences.
     * Query string parameters take precedence over saved preferences.
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
    public function rowClickOpensModal(): bool
    {
        if (! $this->rowsAreClickable()) {
            return false;
        }

        // Check if any rows exist to test with
        $sampleRow = $this->baseQuery()->first();
        if (! $sampleRow) {
            return false;
        }

        // Get the action from rowClick and check if it has a modal
        $action = $this->rowClick($sampleRow->uuid);

        return $action?->getModal() !== null;
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
            perPage: $this->perPage,
        );
    }

    /**
     * Apply changes to the table state (reset page, save preferences, refresh cache)
     */
    public function applyChanges(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if (method_exists($this, 'savePreferences')) {
            $this->savePreferences();
        }

        $this->refreshTable();
    }

    /**
     * Refresh the table by clearing the cache and scrolling to top
     */
    public function refreshTable(): void
    {
        unset($this->rows);
        $this->dispatch("datatable:scroll-to-top:{$this->getId()}");
    }

    /**
     * Render the component
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.datatable');
    }
}
