@props([
    'value' => null,
    'format' => 'Y-m-d H:i',
])

@if ($value)
    {{ \Carbon\Carbon::parse($value)->format($format) }}
@endif

