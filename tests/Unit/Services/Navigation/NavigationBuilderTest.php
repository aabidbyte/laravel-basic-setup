<?php

declare(strict_types=1);

use App\Services\Navigation\NavigationBuilder;
use App\Services\Navigation\NavigationItem;

test('can create navigation builder with make method', function () {
    $builder = NavigationBuilder::make();

    expect($builder)->toBeInstanceOf(NavigationBuilder::class);
});

test('can set and get title', function () {
    $builder = NavigationBuilder::make()->title('Platform');

    expect($builder->getTitle())->toBe('Platform');
});

test('can add navigation items', function () {
    $item1 = NavigationItem::make()->title('Dashboard');
    $item2 = NavigationItem::make()->title('Settings');

    $builder = NavigationBuilder::make()->items($item1, $item2);

    expect($builder->hasItems())->toBeTrue()
        ->and($builder->getItems())->toHaveCount(2);
});

test('filters hidden items', function () {
    $visibleItem = NavigationItem::make()->title('Visible')->show(true);
    $hiddenItem = NavigationItem::make()->title('Hidden')->show(false);

    $builder = NavigationBuilder::make()->items($visibleItem, $hiddenItem);

    expect($builder->getItems())->toHaveCount(1);
});

test('can set icon', function () {
    $builder = NavigationBuilder::make()->icon('<svg>icon</svg>');

    expect($builder->getIcon())->toBe('<svg>icon</svg>');
});

test('can set visibility with boolean', function () {
    $visibleBuilder = NavigationBuilder::make()->show(true);
    $hiddenBuilder = NavigationBuilder::make()->show(false);

    expect($visibleBuilder->isVisible())->toBeTrue()
        ->and($hiddenBuilder->isVisible())->toBeFalse();
});

test('can set visibility with closure', function () {
    $builder = NavigationBuilder::make()->show(fn () => false);

    expect($builder->isVisible())->toBeFalse();
});

test('can convert to array', function () {
    $item = NavigationItem::make()->title('Dashboard');
    $builder = NavigationBuilder::make()
        ->title('Platform')
        ->icon('<svg>icon</svg>')
        ->items($item);

    $array = $builder->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveCount(1)
        ->and($array[0])->toHaveKeys(['title', 'items', 'icon', 'hasItems', 'isVisible'])
        ->and($array[0]['title'])->toBe('Platform')
        ->and($array[0]['items'])->toHaveCount(1)
        ->and($array[0]['isVisible'])->toBeTrue();
});

test('supports method chaining', function () {
    $item = NavigationItem::make()->title('Dashboard');

    $builder = NavigationBuilder::make()
        ->title('Platform')
        ->icon('<svg>icon</svg>')
        ->items($item)
        ->show(true);

    expect($builder)->toBeInstanceOf(NavigationBuilder::class)
        ->and($builder->getTitle())->toBe('Platform')
        ->and($builder->isVisible())->toBeTrue()
        ->and($builder->hasItems())->toBeTrue();
});

test('returns empty items when builder is not visible', function () {
    $item = NavigationItem::make()->title('Dashboard');
    $builder = NavigationBuilder::make()
        ->items($item)
        ->show(false);

    expect($builder->isVisible())->toBeFalse();
});

test('toArray returns empty array when builder is not visible', function () {
    $item = NavigationItem::make()->title('Dashboard');
    $builder = NavigationBuilder::make()
        ->title('Hidden')
        ->items($item)
        ->show(false);

    expect($builder->toArray())->toBeEmpty();
});

test('toArray returns empty array when builder has no visible items', function () {
    $hiddenItem = NavigationItem::make()->title('Hidden')->show(false);
    $builder = NavigationBuilder::make()
        ->title('Platform')
        ->items($hiddenItem);

    expect($builder->toArray())->toBeEmpty();
});
