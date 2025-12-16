<?php

declare(strict_types=1);

use App\Services\Navigation\NavigationItem;

test('can create navigation item with make method', function () {
    $item = NavigationItem::make();

    expect($item)->toBeInstanceOf(NavigationItem::class);
});

test('can set and get title', function () {
    $item = NavigationItem::make()->title('Dashboard');

    expect($item->getTitle())->toBe('Dashboard');
});

test('can set url and get url', function () {
    $item = NavigationItem::make()->url('https://example.com');

    expect($item->getUrl())->toBe('https://example.com');
});

test('can set route and get route name', function () {
    $item = NavigationItem::make()->route('dashboard');

    expect($item->getRoute())->toBe('dashboard');
});

test('can set route with parameters', function () {
    $item = NavigationItem::make()->route('profile.edit', ['id' => 1]);

    expect($item->getRouteParameters())->toBe(['id' => 1]);
});

test('can set icon', function () {
    $item = NavigationItem::make()->icon('<svg>icon</svg>');

    expect($item->getIcon())->toBe('<svg>icon</svg>');
});

test('can set visibility with boolean', function () {
    $visibleItem = NavigationItem::make()->show(true);
    $hiddenItem = NavigationItem::make()->show(false);

    expect($visibleItem->isVisible())->toBeTrue()
        ->and($hiddenItem->isVisible())->toBeFalse();
});

test('can set visibility with closure', function () {
    $item = NavigationItem::make()->show(fn () => false);

    expect($item->isVisible())->toBeFalse();
});

test('can mark as external link', function () {
    $item = NavigationItem::make()->external();

    expect($item->isExternal())->toBeTrue();
});

test('can add nested items', function () {
    $subItem1 = NavigationItem::make()->title('Sub 1');
    $subItem2 = NavigationItem::make()->title('Sub 2');

    $item = NavigationItem::make()
        ->title('Parent')
        ->items($subItem1, $subItem2);

    expect($item->hasItems())->toBeTrue()
        ->and($item->getItems())->toHaveCount(2);
});

test('filters hidden nested items', function () {
    $visibleItem = NavigationItem::make()->title('Visible')->show(true);
    $hiddenItem = NavigationItem::make()->title('Hidden')->show(false);

    $item = NavigationItem::make()
        ->items($visibleItem, $hiddenItem);

    expect($item->getItems())->toHaveCount(1);
});

test('can set badge with string', function () {
    $item = NavigationItem::make()->badge('New');

    expect($item->hasBadge())->toBeTrue()
        ->and($item->getBadge())->toBe('New');
});

test('can set badge with integer', function () {
    $item = NavigationItem::make()->badge(5);

    expect($item->getBadge())->toBe(5);
});

test('can set badge with closure', function () {
    $item = NavigationItem::make()->badge(fn () => 10);

    expect($item->getBadge())->toBe(10);
});

test('can set custom active state', function () {
    $item = NavigationItem::make()->active(true);

    expect($item->isActive())->toBeTrue();
});

test('can set custom active state with closure', function () {
    $item = NavigationItem::make()->active(fn () => false);

    expect($item->isActive())->toBeFalse();
});

test('can check if item is not active by default', function () {
    $item = NavigationItem::make()->route('dashboard');

    // In unit tests, without HTTP context, should return false
    expect($item->isActive())->toBeFalse();
});

test('can set additional attributes', function () {
    $item = NavigationItem::make()->attributes(['data-test' => 'menu-item']);

    expect($item->getAttributes())->toBe(['data-test' => 'menu-item']);
});

test('can convert to array', function () {
    $item = NavigationItem::make()
        ->title('Dashboard')
        ->url('https://example.com/dashboard')
        ->icon('<svg>icon</svg>')
        ->badge(5);

    $array = $item->toArray();

    expect($array)->toHaveKeys(['title', 'url', 'icon', 'badge', 'isActive', 'hasItems', 'items', 'hasBadge', 'attributes', 'hasUrl', 'isExternal'])
        ->and($array['title'])->toBe('Dashboard')
        ->and($array['url'])->toBe('https://example.com/dashboard')
        ->and($array['badge'])->toBe(5)
        ->and($array['hasBadge'])->toBeTrue()
        ->and($array['hasUrl'])->toBeTrue()
        ->and($array['hasItems'])->toBeFalse()
        ->and($array['isExternal'])->toBeFalse()
        ->and($array['attributes'])->toBe([]);
});

test('supports method chaining', function () {
    $item = NavigationItem::make()
        ->title('Dashboard')
        ->route('dashboard')
        ->icon('<svg>icon</svg>')
        ->badge(5)
        ->external()
        ->show(true);

    expect($item)->toBeInstanceOf(NavigationItem::class)
        ->and($item->getTitle())->toBe('Dashboard')
        ->and($item->isExternal())->toBeTrue()
        ->and($item->isVisible())->toBeTrue();
});
