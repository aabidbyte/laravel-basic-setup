<?php

use Illuminate\Support\Facades\App;

test('formatCurrency returns empty string for null input', function () {
    expect(formatCurrency(null))->toBe('');
});

test('formatCurrency returns empty string for empty string input', function () {
    expect(formatCurrency(''))->toBe('');
});

test('formatCurrency formats currency using current locale', function () {
    App::setLocale('en_US');

    $result = formatCurrency(100.50);

    expect($result)->toBe('$100.50');
});

test('formatCurrency formats currency using specified locale', function () {
    $result = formatCurrency(100.50, 'fr_FR');

    expect($result)->toBe('100,50 €');
});

test('formatCurrency handles integer input', function () {
    App::setLocale('en_US');

    $result = formatCurrency(100);

    expect($result)->toBe('$100.00');
});

test('formatCurrency handles string numeric input', function () {
    App::setLocale('en_US');

    $result = formatCurrency('100.50');

    expect($result)->toBe('$100.50');
});

test('formatCurrency uses correct symbol position for en_US', function () {
    App::setLocale('en_US');

    $result = formatCurrency(100.50);

    expect($result)->toStartWith('$');
});

test('formatCurrency uses correct symbol position for fr_FR', function () {
    $result = formatCurrency(100.50, 'fr_FR');

    expect($result)->toEndWith('€');
});

test('formatCurrency uses correct decimal separator for fr_FR', function () {
    $result = formatCurrency(100.50, 'fr_FR');

    expect($result)->toContain(',');
    expect($result)->not->toContain('.');
});

test('formatCurrency uses correct thousands separator for fr_FR', function () {
    $result = formatCurrency(1000.50, 'fr_FR');

    expect($result)->toContain('1 000');
});

test('formatCurrency uses correct thousands separator for en_US', function () {
    App::setLocale('en_US');

    $result = formatCurrency(1000.50);

    expect($result)->toContain('1,000');
});

test('formatCurrency respects precision setting', function () {
    App::setLocale('en_US');

    $result = formatCurrency(100.5);

    expect($result)->toBe('$100.50');
});

test('formatCurrency allows currency code override', function () {
    App::setLocale('en_US');

    $result = formatCurrency(100.50, null, 'EUR');

    expect($result)->toContain('€');
});

test('formatCurrency falls back to basic formatting when currency config is missing', function () {
    // This test verifies the fallback behavior
    $result = formatCurrency(100.50, 'unsupported_locale');

    expect($result)->not->toBeEmpty();
});
