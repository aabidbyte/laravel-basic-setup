<?php

test('formatCurrency returns empty string for null input', function () {
    expect(formatCurrency(null))->toBe('');
});

test('formatCurrency returns empty string for empty string input', function () {
    expect(formatCurrency(''))->toBe('');
});

test('formatCurrency formats currency using defaults (USD, en_US)', function () {
    $result = formatCurrency(100.50);
    expect($result)->toBe('$100.50');
});

test('formatCurrency formats currency using specified currency and locale', function () {
    $result = formatCurrency(100.50, 'EUR', 'fr_FR');
    // Number::currency might spit out distinct non-breaking spaces or simple spaces depending on lib
    // using toContain ensures we check generic formatting correctness
    expect($result)->toContain('100,50');
    expect($result)->toContain('€');
});

test('formatCurrency handles integer input', function () {
    $result = formatCurrency(100);
    expect($result)->toBe('$100.00');
});

test('formatCurrency handles string numeric input', function () {
    $result = formatCurrency('100.50');
    expect($result)->toBe('$100.50');
});

test('formatCurrency uses correct symbol position for USD', function () {
    $result = formatCurrency(100.50, 'USD', 'en_US');
    expect($result)->toStartWith('$');
});

test('formatCurrency uses correct symbol position for EUR', function () {
    $result = formatCurrency(100.50, 'EUR', 'fr_FR');
    expect($result)->toEndWith('€');
});

test('formatCurrency uses correct decimal separator for fr_FR', function () {
    $result = formatCurrency(100.50, 'EUR', 'fr_FR');
    expect($result)->toContain(',');
});

test('formatCurrency uses specified currency code (JPY)', function () {
    $result = formatCurrency(100, 'JPY', 'ja_JP');
    // JPY typically has no decimals
    expect($result)->toContain('￥100');
});
