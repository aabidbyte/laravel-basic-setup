@props([
    'name',
    'error' => null,
])

@if ($error || ($errors->has($name) ?? false))
    <div {{ $attributes->merge(['class' => 'mt-1']) }}>
        <span class="text-xs text-error">{{ $error ?? $errors->first($name) }}</span>
    </div>
@endif
