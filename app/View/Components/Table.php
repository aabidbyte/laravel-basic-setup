<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Constants\DataTableUi;
use App\Services\DataTable\Ui\DataTableComponentRegistry;
use App\Services\DataTable\Ui\ViewportVisibility;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    private DataTableComponentRegistry $componentRegistry;

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
        $this->componentRegistry = app(DataTableComponentRegistry::class);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.table.table');
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
     * Process a row and return all row-related data
     *
     * @param  array<string, mixed>  $row
     * @return array{uuid: string|null, isSelected: bool, rowClickAttr: string, rowClasses: string, rowClassAttr: string, index: int}
     */
    public function processRow(array $row, int $index): array
    {
        // UUID is required - all models must provide UUID in transformer
        $rowUuid = $row['uuid'] ?? null;
        if (! $rowUuid) {
            throw new \RuntimeException(
                'DataTable row must include UUID field. All models must provide UUID in transformer.',
            );
        }

        $isSelected = $rowUuid && in_array($rowUuid, $this->selected, true);
        $rowClickAttr = $this->rowClick && $rowUuid ? "wire:click=\"{$this->rowClick}('{$rowUuid}')\"" : '';

        // Build row classes (replicate row component logic)
        $rowClasses = '';
        // Only add cursor-pointer if wire:click is not present
        if (! $rowClickAttr) {
            $rowClasses .= ' cursor-pointer hover:bg-base-200';
        }
        if ($isSelected) {
            $rowClasses .= ' bg-base-200';
        }
        $rowClasses = trim($rowClasses);
        $rowClassAttr = $rowClasses ? "class=\"{$rowClasses}\"" : '';

        return [
            'uuid' => $rowUuid,
            'isSelected' => $isSelected,
            'rowClickAttr' => $rowClickAttr,
            'rowClasses' => $rowClasses,
            'rowClassAttr' => $rowClassAttr,
            'index' => $index,
        ];
    }

    /**
     * Process a column and return all column-related data
     *
     * @param  array<string, mixed>  $column
     * @param  array<string, mixed>  $row
     * @return array{key: string|null, value: mixed, hidden: bool, viewportClasses: string, cellClassAttr: string, componentName: string|null, hasCustomRender: bool}
     */
    public function processColumn(array $column, array $row): array
    {
        $key = $column['key'] ?? null;
        $value = $key ? ($row[$key] ?? null) : null;
        $hidden = $column['hidden'] ?? false;
        $showInViewPortsOnly = $column['showInViewPortsOnly'] ?? [];
        $viewportClasses = ! empty($showInViewPortsOnly)
            ? ViewportVisibility::classes($showInViewPortsOnly)
            : '';
        $cellClassAttr = $viewportClasses ? "class=\"{$viewportClasses}\"" : '';

        // Determine component name
        $componentName = $column['component'] ?? null;
        if (! $componentName) {
            $type = $column['type'] ?? 'text';
            try {
                $componentName = $this->componentRegistry->getComponent($type);
            } catch (\Exception $e) {
                // Fallback to text
                $componentName = DataTableUi::COMPONENT_CELL_TEXT;
            }
        }

        // Determine what to render
        $hasCustomRender = isset($column['hasCustomRender']) && $column['hasCustomRender'];

        return [
            'key' => $key,
            'value' => $value,
            'hidden' => $hidden,
            'viewportClasses' => $viewportClasses,
            'cellClassAttr' => $cellClassAttr,
            'componentName' => $componentName,
            'hasCustomRender' => $hasCustomRender,
        ];
    }

    /**
     * Process a header column and return all header-related data
     *
     * @param  array<string, mixed>  $column
     * @return array{hidden: bool, responsive: string|null, thClass: string, columnKey: string|null, sortable: bool}
     */
    public function processHeaderColumn(array $column): array
    {
        $hidden = $column['hidden'] ?? false;
        $responsive = $column['responsive'] ?? null;
        $thClass = $responsive ? 'hidden '.$responsive.':table-cell' : '';
        $columnKey = $column['key'] ?? null;
        $sortable = ($column['sortable'] ?? false) && $columnKey;

        return [
            'hidden' => $hidden,
            'responsive' => $responsive,
            'thClass' => $thClass,
            'columnKey' => $columnKey,
            'sortable' => $sortable,
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
     * Get all columns
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->columns;
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
}
