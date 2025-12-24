<?php

declare(strict_types=1);

namespace App\Services\DataTable\Ui;

use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableFilterType;
use InvalidArgumentException;

/**
 * Registry for DataTable filter components
 *
 * Maps filter types to Blade/Livewire component names using allowlisted constants.
 * Prevents XSS by ensuring only registered components can be rendered.
 */
class DataTableFilterComponentRegistry
{
    /**
     * Mapping of filter types to component names
     *
     * @var array<string, string>
     */
    private array $components = [
        DataTableFilterType::SELECT->value => DataTableUi::COMPONENT_FILTER_SELECT,
        DataTableFilterType::MULTISELECT->value => DataTableUi::COMPONENT_FILTER_MULTISELECT,
        DataTableFilterType::BOOLEAN->value => DataTableUi::COMPONENT_FILTER_BOOLEAN,
        DataTableFilterType::DATE_RANGE->value => DataTableUi::COMPONENT_FILTER_DATE_RANGE,
        DataTableFilterType::RELATIONSHIP->value => DataTableUi::COMPONENT_FILTER_RELATIONSHIP,
    ];

    /**
     * Get component name for a filter type
     *
     * @throws InvalidArgumentException if type is not registered
     */
    public function getComponent(DataTableFilterType|string $type): string
    {
        $typeValue = $type instanceof DataTableFilterType ? $type->value : $type;

        if (! isset($this->components[$typeValue])) {
            throw new InvalidArgumentException("Filter type '{$typeValue}' is not registered in DataTableFilterComponentRegistry");
        }

        return $this->components[$typeValue];
    }

    /**
     * Check if a component is registered
     */
    public function hasComponent(DataTableFilterType|string $type): bool
    {
        $typeValue = $type instanceof DataTableFilterType ? $type->value : $type;

        return isset($this->components[$typeValue]);
    }

    /**
     * Register a custom component mapping (for extensibility)
     *
     * @throws InvalidArgumentException if type is already registered
     */
    public function register(DataTableFilterType|string $type, string $component): void
    {
        $typeValue = $type instanceof DataTableFilterType ? $type->value : $type;

        if (isset($this->components[$typeValue])) {
            throw new InvalidArgumentException("Filter type '{$typeValue}' is already registered");
        }

        $this->components[$typeValue] = $component;
    }

    /**
     * Get all registered components
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->components;
    }
}
