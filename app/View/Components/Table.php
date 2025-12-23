<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\DataTable\View\DataTableViewData;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    public DataTableViewData $viewData;

    /**
     * Create a new component instance.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $headers
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<string, mixed>>  $actionsPerRow
     * @param  array<string>  $selected
     */
    public function __construct(
        ?DataTableViewData $viewData = null,
        public string $class = '',
        public array $rows = [],
        public array $headers = [],
        public array $columns = [],
        public array $actionsPerRow = [],
        public ?string $rowClick = null,
        public ?string $sortBy = null,
        public string $sortDirection = 'asc',
        public bool $showBulk = false,
        public bool $selectPage = false,
        public bool $selectAll = false,
        public array $selected = [],
        public ?string $emptyMessage = null,
        public string $emptyIcon = 'user-group',
    ) {
        // If viewData is provided, use it; otherwise create from props (backward compatibility)
        if ($viewData) {
            $this->viewData = $viewData;
        } else {
            $this->viewData = new DataTableViewData(
                rows: $this->rows,
                headers: $this->headers,
                columns: $this->columns,
                actionsPerRow: $this->actionsPerRow,
                bulkActions: [],
                filters: [],
                selected: $this->selected,
                rowClick: $this->rowClick,
                sortBy: $this->sortBy,
                sortDirection: $this->sortDirection,
                showBulk: $this->showBulk,
                selectPage: $this->selectPage,
                selectAll: $this->selectAll,
                showSearch: false,
                searchPlaceholder: null,
                paginator: null,
                emptyMessage: $this->emptyMessage,
                emptyIcon: $this->emptyIcon,
                class: $this->class,
                openRowActionModal: null,
                openRowActionUuid: null,
                openBulkActionModal: null,
            );
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.table.table');
    }
}
