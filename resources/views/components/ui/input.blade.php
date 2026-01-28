@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('input-');
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

    @if ($attributes->get('type') === 'textarea')
        <textarea {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($hasError ? ' textarea-error' : '')])->except(['label', 'error']) }}
                  id="{{ $inputId }}"></textarea>
    @else
        <input {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($hasError ? ' input-error' : '')])->except(['label', 'error']) }}
               id="{{ $inputId }}" />
    @endif

    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />
</div>
