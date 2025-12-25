<?php

declare(strict_types=1);

use App\Constants\DataTable\DataTableUi;

it('can render badge component with text prop', function () {
    $html = DataTableUi::renderComponent(DataTableUi::BADGE, 'Test Badge', [
        'variant' => 'primary',
        'size' => 'sm',
    ]);

    expect($html)
        ->toContain('Test Badge')
        ->toContain('badge')
        ->toContain('badge-primary')
        ->toContain('badge-sm');
});

it('can render badge component with array content', function () {
    $html = DataTableUi::renderComponent(DataTableUi::BADGE, ['Admin', 'User'], [
        'variant' => 'primary',
        'size' => 'sm',
    ]);

    expect($html)
        ->toContain('Admin')
        ->toContain('User')
        ->toContain('badge-primary')
        ->toContain('badge-sm');
});

it('can render button component with text prop', function () {
    $html = DataTableUi::renderComponent('button', 'Click Me', [
        'variant' => 'primary',
        'size' => 'md',
    ]);

    expect($html)
        ->toContain('Click Me')
        ->toContain('btn');
});

it('returns content as string when component view does not exist', function () {
    $html = DataTableUi::renderComponent('nonexistent', 'Test Content');

    expect($html)->toBe('Test Content');
});

it('handles empty array content', function () {
    $html = DataTableUi::renderComponent(DataTableUi::BADGE, []);

    expect($html)->toBe('');
});

it('handles array with non-string items', function () {
    $html = DataTableUi::renderComponent(DataTableUi::BADGE, ['String', 123, true]);

    expect($html)
        ->toContain('String')
        ->toContain('123')
        ->toContain('1');
});

