<?php

declare(strict_types=1);

use App\Services\Navigation\NavigationItem;

test('toArrayFromMany converts multiple items to array', function () {
    $result = NavigationItem::toArrayFromMany(
        NavigationItem::make()
            ->title('Item 1')
            ->url('/item1'),
        NavigationItem::make()
            ->title('Item 2')
            ->url('/item2'),
    );

    expect($result)->toHaveCount(2)
        ->and($result[0]['title'])->toBe('Item 1')
        ->and($result[1]['title'])->toBe('Item 2');
});

test('toArrayFromMany filters out invisible items', function () {
    $result = NavigationItem::toArrayFromMany(
        NavigationItem::make()
            ->title('Visible')
            ->url('/visible')
            ->show(true),
        NavigationItem::make()
            ->title('Hidden')
            ->url('/hidden')
            ->show(false),
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]['title'])->toBe('Visible');
});

test('toArrayFromMany returns empty array when all items are invisible', function () {
    $result = NavigationItem::toArrayFromMany(
        NavigationItem::make()
            ->title('Hidden 1')
            ->url('/hidden1')
            ->show(false),
        NavigationItem::make()
            ->title('Hidden 2')
            ->url('/hidden2')
            ->show(false),
    );

    expect($result)->toBeEmpty();
});

test('toArrayFromMany handles items with closures', function () {
    $visible = true;

    $result = NavigationItem::toArrayFromMany(
        NavigationItem::make()
            ->title('Dynamic Visible')
            ->url('/visible')
            ->show(fn () => $visible),
        NavigationItem::make()
            ->title('Dynamic Hidden')
            ->url('/hidden')
            ->show(fn () => ! $visible),
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]['title'])->toBe('Dynamic Visible');
});
