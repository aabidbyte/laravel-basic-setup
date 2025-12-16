<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

test('formatDate returns empty string for null input', function () {
    expect(formatDate(null))->toBe('');
});

test('formatDate returns empty string for empty string input', function () {
    expect(formatDate(''))->toBe('');
});

test('formatDate formats date using current locale', function () {
    $date = Carbon::parse('2025-12-16');
    App::setLocale('en_US');

    $result = formatDate($date);

    expect($result)->toBe('12/16/2025');
});

test('formatDate formats date using specified locale', function () {
    $date = Carbon::parse('2025-12-16');

    $result = formatDate($date, 'fr_FR');

    expect($result)->toBe('16/12/2025');
});

test('formatDate accepts string input', function () {
    App::setLocale('en_US');

    $result = formatDate('2025-12-16');

    expect($result)->toBe('12/16/2025');
});

test('formatDate accepts DateTime object', function () {
    $date = new DateTime('2025-12-16');
    App::setLocale('en_US');

    $result = formatDate($date);

    expect($result)->toBe('12/16/2025');
});

test('formatDate returns empty string for invalid date', function () {
    $result = formatDate('invalid-date');

    expect($result)->toBe('');
});

test('formatTime returns empty string for null input', function () {
    expect(formatTime(null))->toBe('');
});

test('formatTime returns empty string for empty string input', function () {
    expect(formatTime(''))->toBe('');
});

test('formatTime formats time using current locale', function () {
    $time = Carbon::parse('2025-12-16 14:30:00');
    App::setLocale('en_US');

    $result = formatTime($time);

    expect($result)->toBe('14:30:00');
});

test('formatTime formats time using specified locale', function () {
    $time = Carbon::parse('2025-12-16 14:30:00');

    $result = formatTime($time, 'fr_FR');

    expect($result)->toBe('14:30:00');
});

test('formatTime accepts string input', function () {
    App::setLocale('en_US');

    $result = formatTime('14:30:00');

    expect($result)->toBe('14:30:00');
});

test('formatDateTime returns empty string for null input', function () {
    expect(formatDateTime(null))->toBe('');
});

test('formatDateTime returns empty string for empty string input', function () {
    expect(formatDateTime(''))->toBe('');
});

test('formatDateTime formats datetime using current locale', function () {
    $datetime = Carbon::parse('2025-12-16 14:30:00');
    App::setLocale('en_US');

    $result = formatDateTime($datetime);

    expect($result)->toBe('12/16/2025 14:30:00');
});

test('formatDateTime formats datetime using specified locale', function () {
    $datetime = Carbon::parse('2025-12-16 14:30:00');

    $result = formatDateTime($datetime, 'fr_FR');

    expect($result)->toBe('16/12/2025 14:30:00');
});

test('formatDateTime accepts string input', function () {
    App::setLocale('en_US');

    $result = formatDateTime('2025-12-16 14:30:00');

    expect($result)->toBe('12/16/2025 14:30:00');
});

test('formatDate falls back to default locale for unsupported locale', function () {
    $date = Carbon::parse('2025-12-16');

    $result = formatDate($date, 'unsupported_locale');

    // Should fall back to default locale (en_US)
    expect($result)->toBe('12/16/2025');
});
