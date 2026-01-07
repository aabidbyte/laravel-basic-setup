@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('search-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
@endphp

<label class="flex flex-col gap-2">
    @if ($label)
        <div class="label">
            <span class="label-text">{{ $label }}@if ($required)
                    <span class="text-error">*</span>
                @endif
            </span>
            @isset($labelAppend)
                {{ $labelAppend }}
            @endisset
        </div>
    @endif
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none z-10">
            <x-ui.icon
                name="magnifying-glass"
                size="sm"
                class="text-base-content opacity-50"
            />
        </div>
        <input
            {{ $attributes->merge(['class' => 'input input-bordered w-full pl-10' . ($hasError ? ' input-error' : '')])->except(['label', 'error']) }}
            id="{{ $inputId }}"
        />
    </div>
    <x-ui.input-error :name="$attributes->get('name')" :error="$error" />
</label>
