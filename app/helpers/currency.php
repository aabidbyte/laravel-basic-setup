<?php

use App\Services\I18nService;

/**
 * Format a currency amount according to the current locale's currency settings.
 *
 * @param  float|int|string  $amount
 * @param  string|null  $locale  Locale override
 * @param  string|null  $currencyCode  Currency code override (e.g., 'USD', 'EUR')
 * @return string Formatted currency string (e.g., "$100.00" or "100,00 €")
 */
function formatCurrency($amount, ?string $locale = null, ?string $currencyCode = null): string
{
    if ($amount === null || $amount === '') {
        return '';
    }

    // Convert to float
    $amount = (float) $amount;

    $i18n = app(I18nService::class);
    $locale = $i18n->getValidLocale($locale);
    $localeMetadata = $i18n->getLocaleMetadata($locale);
    $defaultLocale = $i18n->getDefaultLocale();
    $supportedLocales = $i18n->getSupportedLocales();

    // Get currency config
    $currencyConfig = $localeMetadata['currency'] ?? $i18n->getLocaleMetadata($defaultLocale)['currency'] ?? null;

    if ($currencyConfig === null) {
        // Fallback to basic formatting
        return number_format($amount, 2, '.', ',');
    }

    // Override currency code if provided
    if ($currencyCode !== null) {
        // Try to find the currency in supported locales
        $foundCurrency = null;
        foreach ($supportedLocales as $loc => $config) {
            if (isset($config['currency']['code']) && $config['currency']['code'] === $currencyCode) {
                $foundCurrency = $config['currency'];
                break;
            }
        }

        if ($foundCurrency !== null) {
            $currencyConfig = $foundCurrency;
        } else {
            // Use currency code as symbol fallback
            $currencyConfig = [
                'code' => $currencyCode,
                'symbol' => $currencyCode,
                'precision' => $currencyConfig['precision'] ?? 2,
                'symbol_position' => $currencyConfig['symbol_position'] ?? 'before',
                'decimal_separator' => $currencyConfig['decimal_separator'] ?? '.',
                'thousands_separator' => $currencyConfig['thousands_separator'] ?? ',',
            ];
        }
    }

    $precision = $currencyConfig['precision'] ?? 2;
    $symbol = $currencyConfig['symbol'] ?? '';
    $symbolPosition = $currencyConfig['symbol_position'] ?? 'before';
    $decimalSeparator = $currencyConfig['decimal_separator'] ?? '.';
    $thousandsSeparator = $currencyConfig['thousands_separator'] ?? ',';

    // Format number with precision and locale-specific separators
    $formattedAmount = number_format($amount, $precision, $decimalSeparator, $thousandsSeparator);

    // Place symbol based on position
    if ($symbolPosition === 'after') {
        return $formattedAmount . ' ' . $symbol;
    }

    return $symbol . $formattedAmount;
}
