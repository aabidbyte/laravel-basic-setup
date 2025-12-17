<?php

use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use Carbon\Carbon;

/**
 * Format a date according to the current locale's date format.
 * Uses user's timezone preference for display (timezone is for display only, DB storage uses app timezone).
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $date
 * @param  string|null  $locale  Locale override (e.g., 'en_US', 'fr_FR')
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
    } catch (Exception $e) {
        return '';
    }

    $i18n = app(I18nService::class);
    $locale = $i18n->getValidLocale($locale);
    $localeMetadata = $i18n->getLocaleMetadata($locale);
    $defaultLocale = $i18n->getDefaultLocale();

    $format = $localeMetadata['date_format'] ?? $i18n->getLocaleMetadata($defaultLocale)['date_format'] ?? 'Y-m-d';

    // Apply user's timezone preference for display (if not overridden)
    if ($timezone === null) {
        $preferences = app(FrontendPreferencesService::class);
        $timezone = $preferences->getTimezone();
    }

    // Convert to user's timezone for display (original remains in app timezone for DB)
    $carbon = $carbon->setTimezone($timezone);

    return $carbon->format($format);
}

/**
 * Format a time according to the current locale's time format.
 * Uses user's timezone preference for display (timezone is for display only, DB storage uses app timezone).
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
    } catch (Exception $e) {
        return '';
    }

    $i18n = app(I18nService::class);
    $locale = $i18n->getValidLocale($locale);
    $localeMetadata = $i18n->getLocaleMetadata($locale);
    $defaultLocale = $i18n->getDefaultLocale();

    $format = $localeMetadata['time_format'] ?? $i18n->getLocaleMetadata($defaultLocale)['time_format'] ?? 'H:i:s';

    // Apply user's timezone preference for display (if not overridden)
    if ($timezone === null) {
        $preferences = app(FrontendPreferencesService::class);
        $timezone = $preferences->getTimezone();
    }

    // Convert to user's timezone for display (original remains in app timezone for DB)
    $carbon = $carbon->setTimezone($timezone);

    return $carbon->format($format);
}

/**
 * Format a datetime according to the current locale's datetime format.
 * Uses user's timezone preference for display (timezone is for display only, DB storage uses app timezone).
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
    } catch (Exception $e) {
        return '';
    }

    $i18n = app(I18nService::class);
    $locale = $i18n->getValidLocale($locale);
    $localeMetadata = $i18n->getLocaleMetadata($locale);
    $defaultLocale = $i18n->getDefaultLocale();

    $format = $localeMetadata['datetime_format'] ?? $i18n->getLocaleMetadata($defaultLocale)['datetime_format'] ?? 'Y-m-d H:i:s';

    // Apply user's timezone preference for display (if not overridden)
    if ($timezone === null) {
        $preferences = app(FrontendPreferencesService::class);
        $timezone = $preferences->getTimezone();
    }

    // Convert to user's timezone for display (original remains in app timezone for DB)
    $carbon = $carbon->setTimezone($timezone);

    return $carbon->format($format);
}
