@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('input-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
@endphp

<label class="form-control w-full">
    @if ($label)
        <div class="label">
            <span class="label-text">{{ $label }}@if ($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </div>
    @endif
    <input
        {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($hasError ? ' input-error' : '')])->except(['label', 'error']) }}
        id="{{ $inputId }}" />
    @if ($error || ($errors->has($attributes->get('name')) ?? false))
        <div class="label">
            <span class="label-text-alt text-error">{{ $error ?? $errors->first($attributes->get('name')) }}</span>
        </div>
    @endif
</label>
