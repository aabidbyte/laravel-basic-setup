@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'prependEmpty' => true,
])

@php
    $selectId = $attributes->get('id') ?? uniqid('select-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);

    if ($prependEmpty && !isset($options[''])) {
        $options = prepend_empty_option($options, $placeholder);
    }
@endphp

<div class="flex flex-col gap-1">
    @if ($label)
        <x-ui.label :for="$selectId"
                    :text="$label"
                    :required="$required">
            @isset($labelAppend)
                <x-slot:labelAppend>{{ $labelAppend }}</x-slot:labelAppend>
            @endisset
        </x-ui.label>
    @endif

    <select {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($hasError ? ' select-error' : '')])->except(['label', 'error', 'options', 'selected', 'placeholder', 'prependEmpty']) }}
            id="{{ $selectId }}">
        {!! render_select_options($options, $selected) !!}
    </select>

    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />
</div>
