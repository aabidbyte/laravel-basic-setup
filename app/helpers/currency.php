<?php

use Illuminate\Support\Number;

/**
 * Format a currency amount.
 *
 * @param  float|int|string  $amount
 * @param  string|null  $currencyCode  Currency code (e.g., 'USD', 'EUR')
 * @param  string|null  $locale  Locale for formatting rules
 * @return string Formatted currency string
 */
function formatCurrency($amount, ?string $currencyCode = 'USD', ?string $locale = 'en_US'): string
{
    if ($amount === null || $amount === '') {
        return '';
    }

    // Use Laravel's Number helper if available, or fallback to basic formatting
    return Number::currency($amount, in: $currencyCode ?? 'USD', locale: $locale ?? 'en_US');
}
