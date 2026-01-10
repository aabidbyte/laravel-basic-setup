<?php

use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use Carbon\Carbon;

/**
 * Format a date according to the locale's format.
 * Uses Carbon's isoFormat('L') for localized date (e.g., 12/31/2024 or 31/12/2024).
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $date
 * @param  string|null  $locale  Locale override
 * @param  string|null  $timezone  Timezone override (for display only)
 * @return string Formatted date string
 */
function formatDate($date, ?string $locale = null, ?string $timezone = null): string
{
    if ($date === null || $date === '') {
        return '';
    }

    try {
        $carbon = Carbon::parse($date);
    } catch (\Exception $e) {
        return '';
    }

    // Set locale if provided
    if ($locale) {
        $i18n = app(I18nService::class);
        $validLocale = $i18n->getValidLocale($locale);
        $carbon->locale($validLocale);
    }

    // Apply user's timezone preference for display (if not overridden)
    if ($timezone === null) {
        // Optimized: access timezone directly if possible or resolve service
        $timezone = app(FrontendPreferencesService::class)->getTimezone();
    }

    // Convert to user's timezone for display
    return $carbon->setTimezone($timezone)->isoFormat('L');
}

/**
 * Format a time according to the locale's format.
 * Uses Carbon's isoFormat('LT') for localized time (e.g., 8:30 PM or 20:30).
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $time
 * @param  string|null  $locale  Locale override
 * @param  string|null  $timezone  Timezone override (for display only)
 * @return string Formatted time string
 */
function formatTime($time, ?string $locale = null, ?string $timezone = null): string
{
    if ($time === null || $time === '') {
        return '';
    }

    try {
        $carbon = Carbon::parse($time);
    } catch (\Exception $e) {
        return '';
    }

    if ($locale) {
        $i18n = app(I18nService::class);
        $validLocale = $i18n->getValidLocale($locale);
        $carbon->locale($validLocale);
    }

    if ($timezone === null) {
        $timezone = app(FrontendPreferencesService::class)->getTimezone();
    }

    return $carbon->setTimezone($timezone)->isoFormat('LT');
}

/**
 * Format a datetime according to the locale's format.
 * Uses Carbon's isoFormat('L LT') for localized date and time.
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $datetime
 * @param  string|null  $locale  Locale override
 * @param  string|null  $timezone  Timezone override (for display only)
 * @return string Formatted datetime string
 */
function formatDateTime($datetime, ?string $locale = null, ?string $timezone = null): string
{
    if ($datetime === null || $datetime === '') {
        return '';
    }

    try {
        $carbon = Carbon::parse($datetime);
    } catch (\Exception $e) {
        return '';
    }

    if ($locale) {
        $i18n = app(I18nService::class);
        $validLocale = $i18n->getValidLocale($locale);
        $carbon->locale($validLocale);
    }

    if ($timezone === null) {
        $timezone = app(FrontendPreferencesService::class)->getTimezone();
    }

    // Combine Date (L) and Time (LT) formats
    return $carbon->setTimezone($timezone)->isoFormat('L LT');
}
