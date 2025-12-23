@props([
    'value' => null,
    'decimals' => 0,
    'decimalSeparator' => '.',
    'thousandsSeparator' => ',',
])

{{ number_format($value ?? 0, $decimals, $decimalSeparator, $thousandsSeparator) }}

