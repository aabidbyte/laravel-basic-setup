@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

@php
    $selectId = $attributes->get('id') ?? uniqid('select-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
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
        {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($hasError ? ' select-error' : '')])->except(['label', 'error', 'options', 'selected', 'placeholder']) }}
        id="{{ $selectId }}">
        @if ($placeholder)
            <option value="" disabled {{ $selected === null || $selected === '' ? 'selected' : '' }}>
                {{ $placeholder }}
            </option>
        @endif
        @if (! empty($options))
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" {{ ($selected !== null && $selected == $value) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        @endif
    </select>
    @if ($error || ($errors->has($attributes->get('name')) ?? false))
        <div class="label">
            <span class="label-text-alt text-error">{{ $error ?? $errors->first($attributes->get('name')) }}</span>
        </div>
    @endif
</label>

