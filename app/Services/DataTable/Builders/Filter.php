<?php

declare(strict_types=1);

namespace App\Services\DataTable\Builders;

use Closure;

/**
 * Fluent builder for DataTable filters
 *
 * Provides a simple API for defining filters with support for:
 * - Multiple filter types (select, date_range, etc.)
 * - Static and dynamic options
 * - Relationship filtering
 * - Custom filter logic with closures
 * - Conditional visibility
 */
class Filter
{
    /**
     * Filter key (unique identifier, usually matches field name)
     */
    private string $key;

    /**
     * Filter label (displayed to user)
     */
    private string $label;

    /**
     * Placeholder text
     */
    private ?string $placeholder = null;

    /**
     * Filter type (select, date_range, etc.)
     */
    private string $type = 'select';

    /**
     * Static options array (associative array: value => label)
     *
     * @var array<string, string>|null
     */
    private ?array $options = null;

    /**
     * Options closure (for dynamic options)
     */
    private ?Closure $optionsCallback = null;

    /**
     * Resolved options (cached after first resolution).
     */
    private ?array $resolvedOptions = null;

    /**
     * Relationship configuration
     *
     * @var array{name: string, column: string}|null
     */
    private ?array $relationship = null;

    /**
     * Value mapping (transform filter values before querying)
     *
     * @var array<string, mixed>|null
     */
    private ?array $valueMapping = null;

    /**
     * Field mapping (use different field name in query)
     */
    private ?string $fieldMapping = null;

    /**
     * Execute closure (custom filter logic)
     */
    private ?Closure $execute = null;

    /**
     * Conditional visibility
     */
    private bool|Closure $show = true;

    /**
     * Create a new Filter instance
     *
     * @param  string  $key  Unique filter identifier (usually matches field name)
     * @param  string  $label  Filter label displayed to user
     */
    public static function make(string $key, string $label): self
    {
        $instance = new self;
        $instance->key = $key;
        $instance->label = $label;

        return $instance;
    }

    /**
     * Set the filter placeholder
     *
     * @param  string  $placeholder  Placeholder text
     * @return $this
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set the filter type
     *
     * @param  string  $type  Filter type (select, date_range, etc.)
     * @return $this
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set static options
     *
     * @param  array<string, string>  $options  Options array (value => label)
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set options callback (for dynamic options)
     *
     * @param  Closure  $callback  Returns associative array of options (value => label)
     * @return $this
     */
    public function optionsCallback(Closure $callback): self
    {
        $this->optionsCallback = $callback;

        return $this;
    }

    /**
     * Configure relationship filtering
     *
     * @param  string  $name  Relationship name
     * @param  string  $column  Column to filter on
     * @return $this
     */
    public function relationship(string $name, string $column): self
    {
        $this->relationship = [
            'name' => $name,
            'column' => $column,
        ];

        return $this;
    }

    /**
     * Set value mapping (transform filter values before querying)
     *
     * @param  array<string, mixed>  $mapping  Value mapping
     * @return $this
     */
    public function valueMapping(array $mapping): self
    {
        $this->valueMapping = $mapping;

        return $this;
    }

    /**
     * Set field mapping (use different field name in query)
     *
     * @param  string  $field  Field name to use in query
     * @return $this
     */
    public function fieldMapping(string $field): self
    {
        $this->fieldMapping = $field;

        return $this;
    }

    /**
     * Set custom filter logic
     *
     * @param  Closure  $callback  Receives ($query, $value, $key)
     * @return $this
     */
    public function execute(Closure $callback): self
    {
        $this->execute = $callback;

        return $this;
    }

    /**
     * Set conditional visibility
     *
     * @param  bool|Closure  $condition  Visibility condition
     * @return $this
     */
    public function show(bool|Closure $condition): self
    {
        $this->show = $condition;

        return $this;
    }

    /**
     * Get the filter key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the filter label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the placeholder
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * Get the filter type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the options
     *
     * @return array<string, string> Associative array (value => label)
     */
    public function getOptions(): array
    {
        if ($this->resolvedOptions !== null) {
            return $this->resolvedOptions;
        }

        $options = [];

        if ($this->options !== null) {
            $options = $this->options;
        } elseif ($this->optionsCallback !== null) {
            $options = ($this->optionsCallback)();
        }

        // Always prepend an empty option as the first option using centralized helper
        $this->resolvedOptions = prepend_empty_option($options, $this->placeholder);

        return $this->resolvedOptions;
    }

    /**
     * Clear resolved options (for testing).
     */
    public function clearResolvedOptions(): void
    {
        $this->resolvedOptions = null;
    }

    /**
     * Get the relationship configuration
     *
     * @return array{name: string, column: string}|null
     */
    public function getRelationship(): ?array
    {
        return $this->relationship;
    }

    /**
     * Get the value mapping
     *
     * @return array<string, mixed>|null
     */
    public function getValueMapping(): ?array
    {
        return $this->valueMapping;
    }

    /**
     * Get the field mapping
     */
    public function getFieldMapping(): ?string
    {
        return $this->fieldMapping;
    }

    /**
     * Get the execute callback
     */
    public function getExecute(): ?Closure
    {
        return $this->execute;
    }

    /**
     * Check if the filter is visible
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
        return [
            'key' => $this->key,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'options' => $this->getOptions(),
            'relationship' => $this->relationship,
            'valueMapping' => $this->valueMapping,
            'fieldMapping' => $this->fieldMapping,
            'hasExecute' => $this->execute !== null,
        ];
    }
}
