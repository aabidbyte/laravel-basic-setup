<?php

use App\Services\FrontendPreferences\FrontendPreferencesService;
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

test('formatDate uses user timezone preference for display', function () {
    $preferences = app(\App\Services\FrontendPreferences\FrontendPreferencesService::class);
    $preferences->setTimezone('America/New_York');

    // Date stored in UTC (app timezone) - use noon to avoid day boundary issues
    $date = Carbon::parse('2025-12-16 12:00:00', 'UTC');

    $result = formatDate($date);

    // Should display in user's timezone (America/New_York is UTC-5 in December)
    // 12:00 UTC = 07:00 EST, so date remains the same
    expect($result)->toBe('12/16/2025');
});

test('formatTime uses user timezone preference for display', function () {
    $preferences = app(\App\Services\FrontendPreferences\FrontendPreferencesService::class);
    $preferences->setTimezone('America/New_York');

    // Time stored in UTC (app timezone)
    $time = Carbon::parse('2025-12-16 14:00:00', 'UTC');

    $result = formatTime($time);

    // Should display in user's timezone (America/New_York is UTC-5 in December)
    // 14:00 UTC = 09:00 EST
    expect($result)->toBe('09:00:00');
});

test('formatDateTime uses user timezone preference for display', function () {
    $preferences = app(\App\Services\FrontendPreferences\FrontendPreferencesService::class);
    $preferences->setTimezone('America/New_York');

    // DateTime stored in UTC (app timezone)
    $datetime = Carbon::parse('2025-12-16 14:00:00', 'UTC');

    $result = formatDateTime($datetime);

    // Should display in user's timezone (America/New_York is UTC-5 in December)
    // 14:00 UTC = 09:00 EST
    expect($result)->toBe('12/16/2025 09:00:00');
});

test('formatDate accepts timezone override', function () {
    $preferences = app(FrontendPreferencesService::class);
    $preferences->setTimezone('America/New_York');

    // Date stored in UTC
    $date = Carbon::parse('2025-12-16 14:00:00', 'UTC');

    // Override timezone
    $result = formatDate($date, null, 'Europe/Paris');

    // Should use override timezone (Europe/Paris is UTC+1 in December)
    // 14:00 UTC = 15:00 CET, but date should remain same
    expect($result)->toBe('12/16/2025');
});
