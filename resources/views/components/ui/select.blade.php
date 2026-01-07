@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'prependEmpty' => true, // Whether to automatically prepend empty option
])

@php
    $selectId = $attributes->get('id') ?? uniqid('select-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);

    // Automatically prepend empty option if enabled and not already present
    if ($prependEmpty && !isset($options[''])) {
        $options = prepend_empty_option($options, $placeholder);
    }
@endphp

<label class="flex flex-col gap-2">
    @if ($label)
        <div class="label">
            <span class="label-text">
                {{ $label }}
                @if ($required)
                    <span class="text-error">*</span>
                @endif
            </span>
            @isset($labelAppend)
                {{ $labelAppend }}
            @endisset
        </div>
    @endif
    <select
        {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($hasError ? ' select-error' : '')])->except(['label', 'error', 'options', 'selected', 'placeholder', 'prependEmpty']) }}
        id="{{ $selectId }}"
    >
        {!! render_select_options($options, $selected) !!}
    </select>
    <x-ui.input-error :name="$attributes->get('name')" :error="$error" />
</label>
