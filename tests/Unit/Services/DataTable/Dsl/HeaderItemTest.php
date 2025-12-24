<?php

declare(strict_types=1);

use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\HeaderItem;

it('can get associated column', function () {
    $column = ColumnItem::make()->name('email');
    $header = HeaderItem::make()
        ->label('Email')
        ->column($column);

    expect($header->getColumn())->not->toBeNull();
    expect($header->getColumn()->getName())->toBe('email');
});

it('returns null when no column is set', function () {
    $header = HeaderItem::make()->label('Name');

    expect($header->getColumn())->toBeNull();
});
