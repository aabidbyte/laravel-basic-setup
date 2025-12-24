<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\DataTable\DataTableQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
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
    #[Url(as: 'search', history: true, keep: false)]
    public string $search = '';

    /**
     * Sort column
     */
    #[Url(as: 'sort', history: true, keep: false)]
    public string $sortBy = '';

    /**
     * Sort direction
     */
    #[Url(as: 'direction', history: true, keep: false)]
    public string $sortDirection = 'asc';

    /**
     * Items per page
     */
    #[Url(as: 'per_page', history: true, keep: false)]
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
     * Whether current page is selected
     */
    public bool $selectPage = false;

    /**
     * Get the base query
     */
    abstract protected function baseQuery(): Builder;

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
    protected function filters(): array
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
            filters: $this->filters(),
            filterValues: $this->filters,
            search: $this->search,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage
        );
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
        return collect($this->filters())
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
        $filterDefinitions = collect($this->filters());

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
                    $option = collect($filter->getOptions())->first(fn ($opt) => $opt['value'] === $value);
                    $valueLabel = $option['label'] ?? $value;
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
    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->filters = [];
        $this->resetPage();
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
    }

    /**
     * Toggle select all rows on current page
     */
    public function toggleSelectPage(): void
    {
        $this->selectPage = ! $this->selectPage;

        if ($this->selectPage) {
            // Select all UUIDs on current page
            $currentPageUuids = $this->rows->pluck('uuid')->filter()->toArray();
            $this->selected = array_values(array_unique(array_merge($this->selected, $currentPageUuids)));
        } else {
            // Deselect current page UUIDs
            $currentPageUuids = $this->rows->pluck('uuid')->filter()->toArray();
            $this->selected = array_values(array_diff($this->selected, $currentPageUuids));
        }
    }

    /**
     * Toggle select all rows (alias for backward compatibility)
     */
    public function toggleSelectAll(): void
    {
        $this->toggleSelectPage();
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
        $action = collect($this->rowActions())
            ->first(fn (Action $a) => $a->getKey() === $actionKey);

        if ($action === null || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        $model = $this->baseQuery()->where('uuid', $uuid)->first();
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
        // Find the action
        $action = collect($this->rowActions())
            ->first(fn (Action $a) => $a->getKey() === $actionKey);

        if ($action === null) {
            return;
        }

        // Get the model
        $model = $this->baseQuery()->where('uuid', $uuid)->first();
        if ($model === null) {
            return;
        }

        // Check visibility
        if (! $action->isVisible($model)) {
            return;
        }

        // Execute the action
        $execute = $action->getExecute();
        if ($execute !== null) {
            $execute($model);
            $this->dispatch('$refresh');
        }
    }

    /**
     * Get confirmation configuration for a bulk action
     *
     * @param  string  $actionKey  Action key
     * @return array<string, mixed>
     */
    public function getBulkActionConfirmation(string $actionKey): array
    {
        $action = collect($this->bulkActions())
            ->first(fn (BulkAction $a) => $a->getKey() === $actionKey);

        if ($action === null || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        $models = $this->baseQuery()->whereIn('uuid', $this->selected)->get();

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

        // Find the action
        $action = collect($this->bulkActions())
            ->first(fn (BulkAction $a) => $a->getKey() === $actionKey);

        if ($action === null) {
            return;
        }

        // Check visibility
        if (! $action->isVisible()) {
            return;
        }

        // Get the models
        $models = $this->baseQuery()->whereIn('uuid', $this->selected)->get();
        if ($models->isEmpty()) {
            return;
        }

        // Execute the action
        $execute = $action->getExecute();
        if ($execute !== null) {
            $execute($models);
            $this->dispatch('$refresh');
        }

        // Clear selection
        $this->selected = [];
    }

    /**
     * Lifecycle: Updated search
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];  // Clear selections when search changes
        $this->selectPage = false;
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Lifecycle: Updated filters
     */
    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->selected = [];  // Clear selections when filters change
        $this->selectPage = false;
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Lifecycle: Updated per page
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->selected = [];  // Clear selections when per page changes
        $this->selectPage = false;
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Lifecycle: Updated sort
     */
    public function updatedSortBy(): void
    {
        $this->selected = [];  // Clear selections when sort changes
        $this->selectPage = false;
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Lifecycle: Updated sort direction
     */
    public function updatedSortDirection(): void
    {
        $this->selected = [];  // Clear selections when sort direction changes
        $this->selectPage = false;
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Dispatch event after pagination updates
     */
    public function updatedPage(): void
    {
        $this->dispatch('datatable-updated', [
            'pageUuids' => $this->rows->pluck('uuid')->toArray(),
        ]);
    }

    /**
     * Render the datatable using shared template
     */
    public function render()
    {
        return view('components.datatable');
    }
}
