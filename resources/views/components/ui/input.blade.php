@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('input-');
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
    @if ($attributes->get('type') === 'textarea')
        <textarea {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($hasError ? ' textarea-error' : '')])->except(['label', 'error']) }}
                  id="{{ $inputId }}"></textarea>
    @else
        <input {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($hasError ? ' input-error' : '')])->except(['label', 'error']) }}
               id="{{ $inputId }}" />
    @endif
    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />
</label>
