@props(['name', 'error' => null])

@if ($error || ($errors->has($name) ?? false))
    <div {{ $attributes->merge(['class' => 'mt-1']) }}>
        <span class="text-error text-xs">{{ $error ?? $errors->first($name) }}</span>
    </div>
@endif
