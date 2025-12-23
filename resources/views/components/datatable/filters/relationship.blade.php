@props([
    'key' => '',
    'label' => '',
    'placeholder' => '',
    'options' => [],
    'optionsProvider' => null,
    'relationship' => null,
    'value' => null,
    'wireModel' => 'filters',
])

@php
    // Resolve options from provider if needed
    if ($optionsProvider && empty($options)) {
        $provider = app($optionsProvider);
        if (method_exists($provider, 'getOptions')) {
            $options = $provider->getOptions();
        }
    }
@endphp

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif
    <select wire:model.live="{{ $wireModel }}.{{ $key }}" class="select select-bordered select-sm w-full max-w-xs">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option value="{{ $option['value'] ?? $option }}">{{ $option['label'] ?? $option }}</option>
        @endforeach
    </select>
</div>

