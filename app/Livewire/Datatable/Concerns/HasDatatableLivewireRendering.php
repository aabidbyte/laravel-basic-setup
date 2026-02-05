<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use App\Services\DataTable\Builders\Column;
use Livewire\Attributes\Computed;

/**
 * Trait for handling DataTable rendering logic.
 *
 * @property string $search
 *
 * @method array columns()
 */
trait HasDatatableLivewireRendering
{
    /**
     * Get columns for view
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * Get columns for view
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function getColumns(): array
    {
        return collect($this->columns())
            ->reject(fn (Column $column) => $column->isHidden())
            ->map(fn (Column $column) => $column->toArray())
            ->values()
            ->toArray();
    }

    #[Computed]
    public function totalColumns(): int
    {
        $extraColumns = 0;

        if ($this->hasBulkActions()) {
            $extraColumns++;
        }

        if ($this->hasRowActions()) {
            $extraColumns++;
        }

        return \count($this->getColumns()) + $extraColumns;
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

        // 1. Resolve Value (Component, Label, or Field)
        $value = '';
        if ($column->getContentCallback() !== null && $column->getComponentType() !== null) {
            $value = $column->resolve($row);
        } elseif ($column->getLabelCallback() !== null) {
            $value = ($column->getLabelCallback())($row, $column);
        } else {
            $field = $column->getField();
            if ($field) {
                $value = $column->hasRelationship() ? data_get($row, $field) : ($row->{$field} ?? '');
            }
        }

        // 2. Handle Custom View
        $view = $column->getView();
        if ($view !== null) {
            return view($view, ['value' => $value, 'row' => $row, 'column' => $column])->render();
        }

        // 3. Apply Format Callback
        $format = $column->getFormat();
        if ($format !== null) {
            $value = $format($value, $row, $column);
        }

        // 4. Security: Escape if not explicitly marked as HTML
        if (! $column->isHtml() && ! $column->getComponentType()) {
            $value = e((string) $value);
        }

        return (string) $value;
    }
}
