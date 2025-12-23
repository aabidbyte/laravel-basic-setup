<?php

declare(strict_types=1);

use App\Services\DataTable\Ui\ViewportVisibility;

it('returns empty string for empty viewports', function () {
    expect(ViewportVisibility::classes([]))->toBe('');
});

it('generates classes for viewport-only visibility', function () {
    $classes = ViewportVisibility::classes(['sm', 'lg']);

    expect($classes)->toContain('hidden');
    expect($classes)->toContain('sm:table-cell');
    expect($classes)->toContain('lg:table-cell');
});

it('generates classes with custom element type', function () {
    $classes = ViewportVisibility::classes(['md'], 'block');

    expect($classes)->toContain('hidden');
    expect($classes)->toContain('md:block');
});

it('validates viewport names', function () {
    expect(ViewportVisibility::isValidViewport('sm'))->toBeTrue();
    expect(ViewportVisibility::isValidViewport('md'))->toBeTrue();
    expect(ViewportVisibility::isValidViewport('lg'))->toBeTrue();
    expect(ViewportVisibility::isValidViewport('xl'))->toBeTrue();
    expect(ViewportVisibility::isValidViewport('2xl'))->toBeTrue();
    expect(ViewportVisibility::isValidViewport('invalid'))->toBeFalse();
});

it('returns all valid viewports', function () {
    $viewports = ViewportVisibility::getValidViewports();

    expect($viewports)->toContain('sm', 'md', 'lg', 'xl', '2xl');
});
