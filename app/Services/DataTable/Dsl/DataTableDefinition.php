<?php

declare(strict_types=1);

namespace App\Services\DataTable\Dsl;

/**
 * Fluent builder for DataTable definitions
 */
class DataTableDefinition
{
    /** @var array<int, HeaderItem> */
    private array $headers = [];

    /** @var array<int, RowActionItem> */
    private array $rowActions = [];

    /** @var array<int, BulkActionItem> */
    private array $bulkActions = [];

    /** @var array<int, FilterItem> */
    private array $filters = [];

    /**
     * Create a new DataTableDefinition instance
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Add header items
     */
    public function headers(HeaderItem ...$headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Add row action items
     */
    public function actions(RowActionItem ...$actions): self
    {
        $this->rowActions = array_merge($this->rowActions, $actions);

        return $this;
    }

    /**
     * Add bulk action items
     */
    public function bulkActions(BulkActionItem ...$actions): self
    {
        $this->bulkActions = array_merge($this->bulkActions, $actions);

        return $this;
    }

    /**
     * Add filter items
     */
    public function filters(FilterItem ...$filters): self
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    /**
     * Get all headers (filtered by visibility)
     *
     * @return array<int, HeaderItem>
     */
    public function getHeaders(): array
    {
        return array_filter($this->headers, fn (HeaderItem $header) => $header->isVisible());
    }

    /**
     * Get all row actions (filtered by visibility)
     *
     * @return array<int, RowActionItem>
     */
    public function getRowActions(): array
    {
        return array_filter($this->rowActions, fn (RowActionItem $action) => $action->isVisible());
    }

    /**
     * Get all bulk actions (filtered by visibility)
     *
     * @return array<int, BulkActionItem>
     */
    public function getBulkActions(): array
    {
        return array_filter($this->bulkActions, fn (BulkActionItem $action) => $action->isVisible());
    }

    /**
     * Get all filters (filtered by visibility)
     *
     * @return array<int, FilterItem>
     */
    public function getFilters(): array
    {
        return array_filter($this->filters, fn (FilterItem $filter) => $filter->isVisible());
    }

    /**
     * Get row action by key
     */
    public function getRowAction(string $key): ?RowActionItem
    {
        foreach ($this->rowActions as $action) {
            if ($action->toArray()['key'] === $key) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Get bulk action by key
     */
    public function getBulkAction(string $key): ?BulkActionItem
    {
        foreach ($this->bulkActions as $action) {
            if ($action->toArray()['key'] === $key) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Get filter by key
     */
    public function getFilter(string $key): ?FilterItem
    {
        foreach ($this->filters as $filter) {
            if ($filter->toArray()['key'] === $key) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Convert to array for view rendering
     *
     * @return array{headers: array<int, array<string, mixed>>, rowActions: array<int, array<string, mixed>>, bulkActions: array<int, array<string, mixed>>, filters: array<int, array<string, mixed>>}
     */
    public function toArrayForView(): array
    {
        return [
            'headers' => array_map(fn (HeaderItem $header) => $header->toArray(), $this->getHeaders()),
            'rowActions' => array_map(fn (RowActionItem $action) => $action->toArray(), $this->getRowActions()),
            'bulkActions' => array_map(fn (BulkActionItem $action) => $action->toArray(), $this->getBulkActions()),
            'filters' => array_map(fn (FilterItem $filter) => $filter->toArray(), $this->getFilters()),
        ];
    }
}
