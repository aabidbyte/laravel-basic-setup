<?php

declare(strict_types=1);

namespace App\Services\DataTable\Dsl;

use App\Enums\DataTableFilterType;

/**
 * Fluent builder for DataTable filter items
 */
class FilterItem
{
    private ?string $key = null;

    private ?string $label = null;

    private ?string $placeholder = null;

    private ?DataTableFilterType $type = null;

    private ?string $component = null;

    private array $options = [];

    private ?string $optionsProvider = null;

    private ?array $relationship = null;

    private ?array $valueMapping = null;

    private ?string $fieldMapping = null;

    private array $props = [];

    private bool|\Closure $show = true;

    private ?\Closure $execute = null;

    /**
     * Create a new FilterItem instance
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Set the filter key
     */
    public function key(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the filter label (must be translated)
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the filter placeholder (must be translated)
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set the filter type (uses registry to resolve component)
     */
    public function type(DataTableFilterType|string $type): self
    {
        if (is_string($type)) {
            $type = DataTableFilterType::from($type);
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
     * Set filter options (for select/multiselect)
     *
     * @param  array<int, array{value: mixed, label: string}>  $options
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set options provider class name
     */
    public function optionsProvider(string $provider): self
    {
        $this->optionsProvider = $provider;

        return $this;
    }

    /**
     * Set relationship configuration (for relationship filters)
     *
     * @param  array{name: string, column: string}  $relationship
     */
    public function relationship(array $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    /**
     * Set value mapping (for transforming filter values)
     *
     * @param  array<string, mixed>  $mapping
     */
    public function valueMapping(array $mapping): self
    {
        $this->valueMapping = $mapping;

        return $this;
    }

    /**
     * Set field mapping (for custom field name)
     */
    public function fieldMapping(string $field): self
    {
        $this->fieldMapping = $field;

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
     * Set the execute closure (called when filter value changes)
     *
     * @param  \Closure(mixed $value, string $key): mixed  $execute
     */
    public function execute(\Closure $execute): self
    {
        $this->execute = $execute;

        return $this;
    }

    /**
     * Check if the filter should be visible
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
            'key' => $this->key,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
        ];

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }

        if ($this->component !== null) {
            $data['component'] = $this->component;
        }

        if (! empty($this->options)) {
            $data['options'] = $this->options;
        }

        if ($this->optionsProvider !== null) {
            $data['options_provider'] = $this->optionsProvider;
        }

        if ($this->relationship !== null) {
            $data['relationship'] = $this->relationship;
        }

        if ($this->valueMapping !== null) {
            $data['value_mapping'] = $this->valueMapping;
        }

        if ($this->fieldMapping !== null) {
            $data['field_mapping'] = $this->fieldMapping;
        }

        if (! empty($this->props)) {
            $data = array_merge($data, $this->props);
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
}
