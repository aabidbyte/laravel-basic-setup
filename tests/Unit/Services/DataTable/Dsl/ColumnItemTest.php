<?php

declare(strict_types=1);

use App\Enums\DataTable\DataTableColumnType;
use App\Services\DataTable\Dsl\ColumnItem;

it('can mark a column as searchable', function () {
    $column = ColumnItem::make()
        ->name('email')
        ->searchable();

    expect($column->isSearchable())->toBeTrue();
});

it('can mark a column as not searchable', function () {
    $column = ColumnItem::make()
        ->name('password')
        ->searchable(false);

    expect($column->isSearchable())->toBeFalse();
});

it('defaults to not searchable', function () {
    $column = ColumnItem::make()
        ->name('name');

    expect($column->isSearchable())->toBeFalse();
});

it('includes searchable flag in toArray', function () {
    $column = ColumnItem::make()
        ->name('email')
        ->searchable();

    $array = $column->toArray();

    expect($array)->toHaveKey('searchable');
    expect($array['searchable'])->toBeTrue();
});

it('can chain searchable with other methods', function () {
    $column = ColumnItem::make()
        ->name('email')
        ->type(DataTableColumnType::TEXT)
        ->searchable()
        ->props(['muted' => true]);

    expect($column->isSearchable())->toBeTrue();
    expect($column->getName())->toBe('email');
});

it('can get column name', function () {
    $column = ColumnItem::make()
        ->name('email');

    expect($column->getName())->toBe('email');
});

it('returns null for name when not set', function () {
    $column = ColumnItem::make();

    expect($column->getName())->toBeNull();
});
