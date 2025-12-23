<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Constants\DataTableUi;
use App\Services\DataTable\Ui\DataTableComponentRegistry;
use App\Services\DataTable\Ui\DataTableFilterComponentRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datatable extends Component
{
    private DataTableComponentRegistry $componentRegistry;

    private DataTableFilterComponentRegistry $filterRegistry;

    /**
     * Create a new component instance.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $headers
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<string, mixed>>  $actionsPerRow
     * @param  array<int, array<string, mixed>>  $bulkActions
     * @param  array<int, array<string, mixed>>  $filters
     * @param  array<string, mixed>  $filterValues
     * @param  array<string>  $selected
     */
    public function __construct(
        public array $rows = [],
        public array $headers = [],
        public array $columns = [],
        public array $actionsPerRow = [],
        public array $bulkActions = [],
        public array $filters = [],
        public array $filterValues = [],
        public array $selected = [],
        public ?string $rowClick = null,
        public ?string $sortBy = null,
        public string $sortDirection = 'asc',
        public bool $showBulk = true,
        public bool $selectPage = false,
        public bool $selectAll = false,
        public bool $showSearch = true,
        public ?string $searchPlaceholder = null,
        public ?LengthAwarePaginator $paginator = null,
        public ?string $emptyMessage = null,
        public string $emptyIcon = 'user-group',
        public string $class = '',
        public ?string $openRowActionModal = null,
        public ?string $openRowActionUuid = null,
        public ?string $openBulkActionModal = null,
    ) {
        $this->componentRegistry = app(DataTableComponentRegistry::class);
        $this->filterRegistry = app(DataTableFilterComponentRegistry::class);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.datatable');
    }

    /**
     * Calculate total columns count (bulk checkbox + data columns + actions)
     */
    public function getColumnsCount(): int
    {
        return count($this->columns) + ($this->showBulk ? 1 : 0) + ($this->hasActionsPerRow() ? 1 : 0);
    }

    /**
     * Check if there are actions per row
     */
    public function hasActionsPerRow(): bool
    {
        return count($this->actionsPerRow) > 0;
    }

    /**
     * Get bulk actions count
     */
    public function getBulkActionsCount(): int
    {
        return count($this->bulkActions);
    }

    /**
     * Check if bulk actions should be shown in dropdown (if > 3)
     */
    public function showBulkActionsDropdown(): bool
    {
        return $this->getBulkActionsCount() > 3;
    }

    /**
     * Check if filters exist
     */
    public function hasFilters(): bool
    {
        return ! empty($this->filters);
    }

    /**
     * Check if any rows are selected
     */
    public function hasSelected(): bool
    {
        return count($this->selected) > 0;
    }

    /**
     * Check if bulk actions bar should be shown
     */
    public function showBulkBar(): bool
    {
        return $this->showBulk && $this->hasSelected();
    }

    /**
     * Check if paginator has pages
     */
    public function hasPaginator(): bool
    {
        return $this->paginator !== null && $this->paginator->hasPages();
    }

    /**
     * Get search placeholder text
     */
    public function getSearchPlaceholder(): string
    {
        return $this->searchPlaceholder ?? __(DataTableUi::TRANSLATION_SEARCH_PLACEHOLDER);
    }

    /**
     * Process a filter and return component name and safe attributes
     *
     * @param  array<string, mixed>  $filter
     * @return array{component: string|null, safeAttributes: array<string, mixed>}
     */
    public function processFilter(array $filter): array
    {
        $filterType = $filter['type'] ?? null;
        $componentName = $filter['component'] ?? null;

        if (! $componentName && $filterType) {
            try {
                $componentName = $this->filterRegistry->getComponent($filterType);
            } catch (\Exception $e) {
                // Fallback to select if type not found
                $componentName = DataTableUi::COMPONENT_FILTER_SELECT;
            }
        }

        // Only merge safe HTML attributes (scalar values only), exclude internal filter config keys
        $safeAttributes = collect($filter)
            ->except(['type', 'component', 'key'])
            ->filter(fn ($value) => is_scalar($value))
            ->toArray();

        return [
            'component' => $componentName,
            'safeAttributes' => $safeAttributes,
        ];
    }

    /**
     * Generate modal state ID for Alpine.js
     *
     * @param  string  $type  'row' or 'bulk'
     */
    public function getModalStateId(string $actionKey, ?string $rowUuid = null, string $type = 'row'): string
    {
        if ($type === 'row' && $rowUuid) {
            return 'rowActionModalOpen_'.str_replace('-', '_', $actionKey.'_'.$rowUuid);
        }

        return 'bulkActionModalOpen_'.str_replace('-', '_', $actionKey);
    }

    /**
     * Find action by key
     *
     * @param  string  $type  'row' or 'bulk'
     * @return array<string, mixed>|null
     */
    public function findActionByKey(string $key, string $type = 'row'): ?array
    {
        $actions = $type === 'row' ? $this->actionsPerRow : $this->bulkActions;

        return collect($actions)->firstWhere('key', $key);
    }

    /**
     * Get modal configuration for row action
     *
     * @return array{actionKey: string, rowUuid: string|null, action: array<string, mixed>|null, modal: array<string, mixed>|null, modalStateId: string}|null
     */
    public function getRowActionModalConfig(): ?array
    {
        if (! $this->openRowActionModal) {
            return null;
        }

        $actionKey = $this->openRowActionModal;
        $rowUuid = $this->openRowActionUuid;
        $action = $this->findActionByKey($actionKey, 'row');
        $modal = $action['modal'] ?? null;

        if (! $modal || $modal['type'] !== 'confirm') {
            return null;
        }

        $modalStateId = $this->getModalStateId($actionKey, $rowUuid, 'row');

        return [
            'actionKey' => $actionKey,
            'rowUuid' => $rowUuid,
            'action' => $action,
            'modal' => $modal,
            'modalStateId' => $modalStateId,
        ];
    }

    /**
     * Get modal configuration for bulk action
     *
     * @return array{actionKey: string, action: array<string, mixed>|null, modal: array<string, mixed>|null, modalStateId: string}|null
     */
    public function getBulkActionModalConfig(): ?array
    {
        if (! $this->openBulkActionModal) {
            return null;
        }

        $actionKey = $this->openBulkActionModal;
        $action = $this->findActionByKey($actionKey, 'bulk');
        $modal = $action['modal'] ?? null;

        if (! $modal || $modal['type'] !== 'confirm') {
            return null;
        }

        $modalStateId = $this->getModalStateId($actionKey, null, 'bulk');

        return [
            'actionKey' => $actionKey,
            'action' => $action,
            'modal' => $modal,
            'modalStateId' => $modalStateId,
        ];
    }

    /**
     * Get all rows
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Get all headers
     *
     * @return array<int, array<string, mixed>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get all columns
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get all filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get all bulk actions
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    /**
     * Get all actions per row
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActionsPerRow(): array
    {
        return $this->actionsPerRow;
    }

    /**
     * Get selected row UUIDs
     *
     * @return array<string>
     */
    public function getSelected(): array
    {
        return $this->selected;
    }

    /**
     * Get sort by column
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * Get sort direction
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * Check if bulk selection is enabled
     */
    public function isShowBulk(): bool
    {
        return $this->showBulk;
    }

    /**
     * Check if select page is enabled
     */
    public function isSelectPage(): bool
    {
        return $this->selectPage;
    }

    /**
     * Check if select all is enabled
     */
    public function isSelectAll(): bool
    {
        return $this->selectAll;
    }

    /**
     * Check if search is shown
     */
    public function isShowSearch(): bool
    {
        return $this->showSearch;
    }

    /**
     * Get paginator instance
     */
    public function getPaginator(): ?LengthAwarePaginator
    {
        return $this->paginator;
    }

    /**
     * Get empty message
     */
    public function getEmptyMessage(): ?string
    {
        return $this->emptyMessage;
    }

    /**
     * Get empty icon
     */
    public function getEmptyIcon(): string
    {
        return $this->emptyIcon;
    }

    /**
     * Get component class
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get row click handler
     */
    public function getRowClick(): ?string
    {
        return $this->rowClick;
    }
}
