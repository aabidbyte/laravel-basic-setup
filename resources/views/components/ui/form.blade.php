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
    $formAction = $action ? 'action="' . $action . '"' : '';
@endphp

<form
    method="{{ $isGet ? 'GET' : 'POST' }}"
    {!! $formAction !!}
    {{ $attributes->merge(['class' => trim('space-y-6 ' . $class)])->except(['method', 'action']) }}
>
    @if ($needsMethodSpoofing)
        @method($formMethod)
    @endif

    @if (!$isGet)
        @csrf
    @endif

    {{ $slot }}
</form>
