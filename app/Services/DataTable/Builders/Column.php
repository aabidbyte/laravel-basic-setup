<?php

declare(strict_types=1);

namespace App\Services\DataTable\Builders;

use App\Constants\DataTable\DataTableUi;
use Closure;

/**
 * Fluent builder for DataTable columns with automatic relationship parsing
 *
 * Provides a simple API for defining table columns with support for:
 * - Automatic relationship detection from dot notation (e.g., 'address.city.name')
 * - Inline formatting with closures
 * - Sortable and searchable columns
 * - Custom views and HTML rendering
 * - Conditional visibility
 */
class Column
{
    /**
     * Column label (displayed in header)
     */
    private string $label;

    /**
     * Field name or relationship path (e.g., 'name' or 'address.city.name')
     */
    private ?string $field = null;

    /**
     * Whether the column is sortable
     */
    private bool|Closure $sortable = false;

    /**
     * Custom sort closure
     */
    private ?Closure $sortCallback = null;

    /**
     * Whether the column is searchable
     */
    private bool|Closure $searchable = false;

    /**
     * Custom search closure
     */
    private ?Closure $searchCallback = null;

    /**
     * Format closure for value transformation
     */
    private ?Closure $format = null;

    /**
     * View name for custom rendering
     */
    private ?string $view = null;

    /**
     * Label closure for non-DB columns
     */
    private ?Closure $labelCallback = null;

    /**
     * Whether to render as HTML (unescaped)
     */
    private bool $html = false;

    /**
     * Conditional visibility
     */
    private bool|Closure $hidden = false;

    /**
     * Additional CSS classes for the column
     */
    private string $class = '';

    /**
     * Content closure for generating column content
     */
    private ?Closure $contentCallback = null;

    /**
     * Component type (e.g., 'badge', 'button', etc.)
     */
    private ?string $componentType = null;

    /**
     * Component attributes/props
     *
     * @var array<string, mixed>
     */
    private array $componentAttributes = [];

    /**
     * Column width (e.g., '200px', '20rem')
     */
    private ?string $width = null;

    /**
     * Whether to prevent text wrapping
     */
    private bool $nowrap = true;

    /**
     * Create a new Column instance
     *
     * @param  string  $label  Column label (displayed in header)
     * @param  string|null  $field  Field name or relationship path (defaults to snake_case of label)
     */
    public static function make(string $label, ?string $field = null): self
    {
        $instance = new self;
        $instance->label = $label;
        $instance->field = $field ?? str($label)->snake()->toString();

        return $instance;
    }

    /**
     * Make the column sortable
     *
     * @param  bool|Closure  $callback  True for default sorting, or custom sort closure
     * @return $this
     */
    public function sortable(bool|Closure $callback = true): self
    {
        if ($callback instanceof Closure) {
            $this->sortable = true;
            $this->sortCallback = $callback;
        } else {
            $this->sortable = $callback;
        }

        return $this;
    }

    /**
     * Make the column searchable
     *
     * @param  bool|Closure  $callback  True for default search, or custom search closure
     * @return $this
     */
    public function searchable(bool|Closure $callback = true): self
    {
        if ($callback instanceof Closure) {
            $this->searchable = true;
            $this->searchCallback = $callback;
        } else {
            $this->searchable = $callback;
        }

        return $this;
    }

    /**
     * Format the column value
     *
     * @param  Closure  $callback  Receives ($value, $row, Column $column)
     * @return $this
     */
    public function format(Closure $callback): self
    {
        $this->format = $callback;

        return $this;
    }

    /**
     * Render the column using a custom view
     *
     * @param  string  $view  View name
     * @return $this
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Use a label closure for non-DB columns
     *
     * @param  Closure  $callback  Receives ($row, Column $column)
     * @return $this
     */
    public function label(Closure $callback): self
    {
        $this->labelCallback = $callback;

        return $this;
    }

    /**
     * Allow HTML rendering (unescaped output)
     *
     * @return $this
     */
    public function html(): self
    {
        $this->html = true;

        return $this;
    }

    /**
     * Hide the column conditionally
     *
     * @param  bool|Closure  $condition  Hide condition
     * @return $this
     */
    public function hidden(bool|Closure $condition): self
    {
        $this->hidden = $condition;

        return $this;
    }

    /**
     * Set the column width
     */
    public function width(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Prevent text wrapping in this column
     */
    public function nowrap(bool $nowrap = true): self
    {
        $this->nowrap = $nowrap;

        return $this;
    }

    /**
     * Add CSS classes to the column
     *
     * @param  string  $class  CSS classes
     * @return $this
     */
    public function class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Set content callback for generating column content
     *
     * @param  Closure  $callback  Receives ($row, Column $column) and returns content
     * @return $this
     */
    public function content(Closure $callback): self
    {
        $this->contentCallback = $callback;

        return $this;
    }

    /**
     * Set component type and attributes
     *
     * @param  string  $type  Component type (e.g., 'badge', 'button')
     * @param  array<string, mixed>  $attributes  Component attributes/props
     * @return $this
     */
    public function type(string $type, array $attributes = []): self
    {
        $this->componentType = $type;
        $this->componentAttributes = $attributes;
        // Automatically enable HTML rendering when component type is set
        $this->html = true;

        return $this;
    }

    /**
     * Get the column label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the field name
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Check if the column is sortable
     */
    public function isSortable(): bool
    {
        return is_bool($this->sortable) ? $this->sortable : (bool) ($this->sortable)();
    }

    /**
     * Get the sort callback
     */
    public function getSortCallback(): ?Closure
    {
        return $this->sortCallback;
    }

    /**
     * Check if the column is searchable
     */
    public function isSearchable(): bool
    {
        return is_bool($this->searchable) ? $this->searchable : (bool) ($this->searchable)();
    }

    /**
     * Get the search callback
     */
    public function getSearchCallback(): ?Closure
    {
        return $this->searchCallback;
    }

    /**
     * Get the format callback
     */
    public function getFormat(): ?Closure
    {
        return $this->format;
    }

    /**
     * Get the view name
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * Get the label callback
     */
    public function getLabelCallback(): ?Closure
    {
        return $this->labelCallback;
    }

    /**
     * Check if HTML rendering is enabled
     */
    public function isHtml(): bool
    {
        return $this->html;
    }

    /**
     * Check if the column is hidden
     *
     * @param  mixed  $row  Optional row data for conditional visibility
     */
    public function isHidden(mixed $row = null): bool
    {
        if (is_bool($this->hidden)) {
            return $this->hidden;
        }

        return (bool) ($this->hidden)($row);
    }

    /**
     * Get the CSS classes
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get the content callback
     */
    public function getContentCallback(): ?Closure
    {
        return $this->contentCallback;
    }

    /**
     * Get the component type
     */
    public function getComponentType(): ?string
    {
        return $this->componentType;
    }

    /**
     * Get the component attributes
     *
     * @return array<string, mixed>
     */
    public function getComponentAttributes(): array
    {
        return $this->componentAttributes;
    }

    /**
     * Resolve content to HTML when content callback and component type are set
     *
     * @param  mixed  $row  Row model instance
     * @return string Resolved HTML
     */
    public function resolve(mixed $row): string
    {
        if ($this->contentCallback === null || $this->componentType === null) {
            return '';
        }

        $content = ($this->contentCallback)($row, $this);

        return DataTableUi::renderComponent(
            $this->componentType,
            $content,
            $this->componentAttributes,
        );
    }

    /**
     * Parse relationship path from field name
     *
     * Returns array with:
     * - 'relationships' => ['address', 'city'] (nested relationships)
     * - 'column' => 'name' (final column name)
     *
     * @return array{relationships: array<int, string>, column: string}
     */
    public function parseRelationship(): array
    {
        if ($this->field === null) {
            return ['relationships' => [], 'column' => ''];
        }

        $parts = explode('.', $this->field);

        if (count($parts) === 1) {
            return ['relationships' => [], 'column' => $parts[0]];
        }

        $column = array_pop($parts);

        return [
            'relationships' => $parts,
            'column' => $column,
        ];
    }

    /**
     * Check if this column uses relationships
     */
    public function hasRelationship(): bool
    {
        return str_contains((string) $this->field, '.');
    }

    /**
     * Convert to array for view rendering
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'field' => $this->field,
            'sortable' => $this->isSortable(),
            'searchable' => $this->isSearchable(),
            'html' => $this->html,
            'class' => $this->class,
            'hasFormat' => $this->format !== null,
            'hasView' => $this->view !== null,
            'hasLabelCallback' => $this->labelCallback !== null,
            'hasContentCallback' => $this->contentCallback !== null,
            'componentType' => $this->componentType,
            'width' => $this->width,
            'nowrap' => $this->nowrap,
        ];
    }
}
