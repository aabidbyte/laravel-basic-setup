@props([
    'method' => 'POST',
    'action' => null,
    'class' => '',
])

@php
    $formMethod = strtoupper($method);
    $isGet = $formMethod === 'GET';
    $isPost = $formMethod === 'POST';
    $needsMethodSpoofing = !$isGet && !$isPost;
@endphp

<form method="{{ $isGet ? 'GET' : 'POST' }}" @if ($action) action="{{ $action }}" @endif
    {{ $attributes->merge(['class' => trim('space-y-6 ' . $class)])->except(['method', 'action']) }}>
    @if ($needsMethodSpoofing)
        @method($formMethod)
    @endif

    @if (!$isGet)
        @csrf
    @endif

    {{ $slot }}
</form>
