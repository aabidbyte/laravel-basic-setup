<?php

use App\Services\I18nService;
use Carbon\Carbon;

/**
 * Format a date according to the current locale's date format.
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $date
 * @param  string|null  $locale  Locale override (e.g., 'en_US', 'fr_FR')
 * @return string Formatted date string
 */
function formatDate($date, ?string $locale = null): string
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

    return $carbon->format($format);
}

/**
 * Format a time according to the current locale's time format.
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $time
 * @param  string|null  $locale  Locale override
 * @return string Formatted time string
 */
function formatTime($time, ?string $locale = null): string
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

    return $carbon->format($format);
}

/**
 * Format a datetime according to the current locale's datetime format.
 *
 * @param  \Carbon\Carbon|\DateTime|string|null  $datetime
 * @param  string|null  $locale  Locale override
 * @return string Formatted datetime string
 */
function formatDateTime($datetime, ?string $locale = null): string
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

    return $carbon->format($format);
}
