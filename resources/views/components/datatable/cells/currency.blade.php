@props([
    'value' => null,
    'currency' => null,
])

@if ($value !== null)
    {{ formatCurrency($value, null, $currency) }}
@endif

