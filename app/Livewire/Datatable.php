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
     * Render the component
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.datatable');
    }
}
