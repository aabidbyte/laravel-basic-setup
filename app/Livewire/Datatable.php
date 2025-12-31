<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Concerns\DataTable\HasActions;
use App\Livewire\Concerns\DataTable\HasFilters;
use App\Livewire\Concerns\DataTable\HasPagination;
use App\Livewire\Concerns\DataTable\HasPreferences;
use App\Livewire\Concerns\DataTable\HasQueryParameters;
use App\Livewire\Concerns\DataTable\HasRendering;
use App\Livewire\Concerns\DataTable\HasSelection;
use App\Livewire\Concerns\DataTable\HasSorting;
use App\Services\DataTable\DataTableQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Base DataTable Component
 *
 * Provides self-rendering datatable functionality with shared template.
 * Individual datatables only need to provide configuration (columns, filters, actions).
 */
abstract class Datatable extends Component
{
    use HasActions;
    use HasFilters;
    use HasPagination;
    use HasPreferences;
    use HasQueryParameters;
    use HasRendering;
    use HasSelection;
    use HasSorting;

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
     *
     * @param  string  $uuid  Row UUID
     */
    public function rowClick(string $uuid): void
    {
        // Override in component if needed
    }

    /**
     * Determine if the row is clickable
     *
     * @param  mixed  $row
     */
    public function rowsAreClickable(): bool
    {
        if (isset($this->rowsClickable)) {
            return $this->rowsClickable;
        }

        $reflector = new \ReflectionMethod($this, 'rowClick');

        $this->rowsClickable = $reflector->getDeclaringClass()->getName() !== self::class;

        return $this->rowsClickable;
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
