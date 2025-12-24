<?php

declare(strict_types=1);

namespace App\Services\DataTable\Dsl;

/**
 * Fluent builder for DataTable header items
 */
class HeaderItem
{
    private ?string $label = null;

    private bool $sortable = false;

    private ?string $sortKey = null;

    private ?ColumnItem $column = null;

    private bool|\Closure $show = true;

    private array $showInViewPortsOnly = [];

    /**
     * Create a new HeaderItem instance
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Set the header label (must be translated)
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Alias for label() for better readability
     */
    public function title(string $title): self
    {
        return $this->label($title);
    }

    /**
     * Mark the header as sortable
     */
    public function sortable(?string $sortKey = null): self
    {
        $this->sortable = true;
        $this->sortKey = $sortKey;

        return $this;
    }

    /**
     * Set the associated column item
     */
    public function column(ColumnItem $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Set visibility condition
     */
    public function show(bool|\Closure $condition): self
    {
        $this->show = $condition;

        return $this;
    }

    /**
     * Show only in specified viewports
     *
     * @param  array<string>  $viewports  e.g., ['sm', 'lg']
     */
    public function showInViewPortsOnly(array $viewports): self
    {
        $this->showInViewPortsOnly = $viewports;

        return $this;
    }

    /**
     * Check if the header should be visible
     */
    public function isVisible(): bool
    {
        if (is_bool($this->show)) {
            return $this->show;
        }

        return (bool) ($this->show)();
    }

    /**
     * Convert to array for view rendering
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'label' => $this->label,
            'sortable' => $this->sortable,
            'sortKey' => $this->sortKey,
            'showInViewPortsOnly' => $this->showInViewPortsOnly,
        ];

        if ($this->column !== null) {
            $data['column'] = $this->column->toArray();
        }

        return $data;
    }
}
