@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('search-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
@endphp

<div class="flex flex-col gap-1">
    @if ($label)
        <x-ui.label :for="$inputId"
                    :text="$label"
                    :required="$required">
            @isset($labelAppend)
                <x-slot:labelAppend>{{ $labelAppend }}</x-slot:labelAppend>
            @endisset
        </x-ui.label>
    @endif

    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-2">
            <x-ui.icon name="magnifying-glass"
                       size="sm"
                       class="text-base-content opacity-50" />
        </div>
        <input {{ $attributes->merge(['class' => 'input input-bordered w-full pl-10' . ($hasError ? ' input-error' : '')])->except(['label', 'error']) }}
               id="{{ $inputId }}" />
    </div>

    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />
</div>
