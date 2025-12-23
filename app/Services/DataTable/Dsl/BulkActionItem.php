<?php

declare(strict_types=1);

namespace App\Services\DataTable\Dsl;

use App\Constants\DataTableUi;
use Illuminate\Database\Eloquent\Collection;

/**
 * Fluent builder for DataTable bulk action items
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class BulkActionItem
{
    private ?string $key = null;

    private ?string $label = null;

    private ?string $icon = null;

    private ?string $variant = null;

    private ?string $color = null;

    private ?\Closure $execute = null;

    private ?array $modal = null;

    private bool|\Closure $show = true;

    /**
     * Create a new BulkActionItem instance
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Set the action key (must be a constant from DataTableUi)
     */
    public function key(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the action label (must be translated)
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the action icon name
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the button variant
     */
    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Set the button color
     */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set the execute closure (executes immediately when clicked)
     *
     * @param  \Closure(Collection<int, TModel> $models): mixed  $execute
     */
    public function execute(\Closure $execute): self
    {
        $this->execute = $execute;

        return $this;
    }

    /**
     * Configure modal to show before execution
     *
     * @param  string  $type  One of: 'blade', 'livewire', 'html', 'confirm'
     * @param  string|null  $component  Component name (for blade/livewire) or HTML string (for html)
     * @param  array<string, mixed>  $props  Props to pass to the component
     */
    public function showModal(string $type, ?string $component = null, array $props = []): self
    {
        $this->modal = [
            'type' => $type,
            'component' => $component,
            'props' => $props,
        ];

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
     * Check if the action should be visible
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
     * Note: Closures are NOT included in the array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'label' => $this->label,
            'icon' => $this->icon,
            'variant' => $this->variant ?? DataTableUi::VARIANT_GHOST,
        ];

        if ($this->color !== null) {
            $data['color'] = $this->color;
        }

        if ($this->modal !== null) {
            $data['modal'] = $this->modal;
        }

        if ($this->execute !== null) {
            // Mark that execute exists (but don't serialize closure)
            $data['hasExecute'] = true;
        }

        return $data;
    }

    /**
     * Get the execute closure (for server-side execution)
     */
    public function getExecute(): ?\Closure
    {
        return $this->execute;
    }

    /**
     * Get the modal configuration
     *
     * @return array<string, mixed>|null
     */
    public function getModal(): ?array
    {
        return $this->modal;
    }
}
