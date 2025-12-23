@props([
    'value' => null,
])

@if ($value !== null)
    @php
        $sanitizer = app(\App\Services\Security\HtmlSanitizer::class);
        $sanitized = $sanitizer->sanitize($value);
    @endphp
    {!! $sanitized !!}
@endif

