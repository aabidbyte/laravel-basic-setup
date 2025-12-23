<?php

declare(strict_types=1);

use App\Constants\DataTableUi;
use App\Enums\DataTableColumnType;
use App\Services\DataTable\Ui\DataTableComponentRegistry;

it('can get component for column type', function () {
    $registry = app(DataTableComponentRegistry::class);

    expect($registry->getComponent(DataTableColumnType::TEXT))
        ->toBe(DataTableUi::COMPONENT_CELL_TEXT);
});

it('throws exception for unregistered type', function () {
    $registry = new DataTableComponentRegistry;

    expect(fn () => $registry->getComponent('nonexistent'))
        ->toThrow(InvalidArgumentException::class);
});

it('can check if component is registered', function () {
    $registry = app(DataTableComponentRegistry::class);

    expect($registry->hasComponent(DataTableColumnType::TEXT))->toBeTrue();
    expect($registry->hasComponent('nonexistent'))->toBeFalse();
});

it('can register custom component', function () {
    $registry = new DataTableComponentRegistry;

    // Register a new type that doesn't exist in defaults
    $registry->register('custom_type', 'custom.text');

    expect($registry->getComponent('custom_type'))
        ->toBe('custom.text');
});

it('throws exception when registering duplicate type', function () {
    $registry = app(DataTableComponentRegistry::class);

    expect(fn () => $registry->register(DataTableColumnType::TEXT, 'custom.text'))
        ->toThrow(InvalidArgumentException::class);
});
