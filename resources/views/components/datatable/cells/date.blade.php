@props([
    'value' => null,
    'format' => 'Y-m-d',
])

@if ($value)
    {{ \Carbon\Carbon::parse($value)->format($format) }}
@endif

