<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\DataTable\View\DataTableViewData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datatable extends Component
{
    public DataTableViewData $viewData;

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
        $this->viewData = new DataTableViewData(
            rows: $this->rows,
            headers: $this->headers,
            columns: $this->columns,
            actionsPerRow: $this->actionsPerRow,
            bulkActions: $this->bulkActions,
            filters: $this->filters,
            selected: $this->selected,
            rowClick: $this->rowClick,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            showBulk: $this->showBulk,
            selectPage: $this->selectPage,
            selectAll: $this->selectAll,
            showSearch: $this->showSearch,
            searchPlaceholder: $this->searchPlaceholder,
            paginator: $this->paginator,
            emptyMessage: $this->emptyMessage,
            emptyIcon: $this->emptyIcon,
            class: $this->class,
            openRowActionModal: $this->openRowActionModal,
            openRowActionUuid: $this->openRowActionUuid,
            openBulkActionModal: $this->openBulkActionModal,
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.datatable');
    }
}
