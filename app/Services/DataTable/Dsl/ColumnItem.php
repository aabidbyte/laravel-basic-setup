<?php

declare(strict_types=1);

namespace App\Services\DataTable\Dsl;

use App\Enums\DataTable\DataTableColumnType;

/**
 * Fluent builder for DataTable column items
 */
class ColumnItem
{
    private ?string $name = null;

    private ?DataTableColumnType $type = null;

    private ?string $component = null;

    private ?\Closure $render = null;

    private array $props = [];

    private bool|\Closure $show = true;

    private array $showInViewPortsOnly = [];

    private bool $searchable = false;

    /**
     * Create a new ColumnItem instance
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Set the column name (field key from transformer)
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the column type (uses registry to resolve component)
     */
    public function type(DataTableColumnType|string $type): self
    {
        if (is_string($type)) {
            $type = DataTableColumnType::from($type);
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Set a custom component name (overrides type-based resolution)
     */
    public function component(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Set custom render closure (returns HTML string)
     *
     * @param  \Closure(array<string, mixed> $row): string  $render
     */
    public function render(\Closure $render): self
    {
        $this->render = $render;

        return $this;
    }

    /**
     * Set additional props for the component
     *
     * @param  array<string, mixed>  $props
     */
    public function props(array $props): self
    {
        $this->props = array_merge($this->props, $props);

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
     * Mark the column as searchable
     */
    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Check if the column is searchable
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Get the column name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Check if the column should be visible
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
     * Note: Closures are NOT included in the array - they must be executed server-side
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->name,
            'showInViewPortsOnly' => $this->showInViewPortsOnly,
            'searchable' => $this->searchable,
        ];

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }

        if ($this->component !== null) {
            $data['component'] = $this->component;
        }

        if ($this->render !== null) {
            // Mark that custom render exists (but don't serialize closure)
            $data['hasCustomRender'] = true;
        }

        if (! empty($this->props)) {
            $data = array_merge($data, $this->props);
        }

        return $data;
    }

    /**
     * Get the render closure (for server-side execution)
     */
    public function getRender(): ?\Closure
    {
        return $this->render;
    }
}
