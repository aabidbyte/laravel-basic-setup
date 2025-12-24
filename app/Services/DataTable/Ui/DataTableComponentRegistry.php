<?php

declare(strict_types=1);

namespace App\Services\DataTable\Ui;

use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableColumnType;
use InvalidArgumentException;

/**
 * Registry for DataTable cell components
 *
 * Maps column types to Blade component names using allowlisted constants.
 * Prevents XSS by ensuring only registered components can be rendered.
 */
class DataTableComponentRegistry
{
    /**
     * Mapping of column types to component names
     *
     * @var array<string, string>
     */
    private array $components = [
        DataTableColumnType::TEXT->value => DataTableUi::COMPONENT_CELL_TEXT,
        DataTableColumnType::BADGE->value => DataTableUi::COMPONENT_CELL_BADGE,
        DataTableColumnType::BOOLEAN->value => DataTableUi::COMPONENT_CELL_BOOLEAN,
        DataTableColumnType::DATE->value => DataTableUi::COMPONENT_CELL_DATE,
        DataTableColumnType::DATETIME->value => DataTableUi::COMPONENT_CELL_DATETIME,
        DataTableColumnType::CURRENCY->value => DataTableUi::COMPONENT_CELL_CURRENCY,
        DataTableColumnType::NUMBER->value => DataTableUi::COMPONENT_CELL_NUMBER,
        DataTableColumnType::LINK->value => DataTableUi::COMPONENT_CELL_LINK,
        DataTableColumnType::AVATAR->value => DataTableUi::COMPONENT_CELL_AVATAR,
        DataTableColumnType::SAFE_HTML->value => DataTableUi::COMPONENT_CELL_SAFE_HTML,
    ];

    /**
     * Get component name for a column type
     *
     * @throws InvalidArgumentException if type is not registered
     */
    public function getComponent(DataTableColumnType|string $type): string
    {
        $typeValue = $type instanceof DataTableColumnType ? $type->value : $type;

        if (! isset($this->components[$typeValue])) {
            throw new InvalidArgumentException("Column type '{$typeValue}' is not registered in DataTableComponentRegistry");
        }

        return $this->components[$typeValue];
    }

    /**
     * Check if a component is registered
     */
    public function hasComponent(DataTableColumnType|string $type): bool
    {
        $typeValue = $type instanceof DataTableColumnType ? $type->value : $type;

        return isset($this->components[$typeValue]);
    }

    /**
     * Register a custom component mapping (for extensibility)
     *
     * @throws InvalidArgumentException if type is already registered
     */
    public function register(DataTableColumnType|string $type, string $component): void
    {
        $typeValue = $type instanceof DataTableColumnType ? $type->value : $type;

        if (isset($this->components[$typeValue])) {
            throw new InvalidArgumentException("Column type '{$typeValue}' is already registered");
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
