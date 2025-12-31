<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

use App\Services\DataTable\Builders\Column;

/**
 * Trait for handling DataTable rendering logic.
 *
 * @property string $search
 *
 * @method array columns()
 */
trait HasRendering
{
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
     * Render the table header
     */
    public function renderTableHeader(): string
    {
        return view('components.datatable.header')->render();
    }

    /**
     * Render a table row
     */
    public function renderTableRow(mixed $row): string
    {
        return view('components.datatable.row', ['row' => $row])->render();
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

        // 5. Apply Search Highlighting
        if ($column->isSearchable() && ! empty($this->search)) {
            $value = $this->highlightSearchTerm((string) $value, $this->search);
        }

        return (string) $value;
    }

    /**
     * Highlight search term in value (HTML-safe)
     *
     * @param  string  $value  Value to highlight
     * @param  string  $search  Search term
     */
    protected function highlightSearchTerm(string $value, string $search): string
    {
        if (empty($search) || empty($value)) {
            return $value;
        }

        $quotedSearch = preg_quote($search, '/');

        // Split the string into HTML tags and text content
        $parts = preg_split('/(<[^>]*>)/i', $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $result = '';

        if ($parts === false) {
            return $value;
        }

        foreach ($parts as $part) {
            if (str_starts_with($part, '<') && str_ends_with($part, '>')) {
                $result .= $part;
            } else {
                $result .= preg_replace('/('.$quotedSearch.')/i', '<mark class="bg-warning/30 rounded">$1</mark>', $part);
            }
        }

        return $result;
    }
}
