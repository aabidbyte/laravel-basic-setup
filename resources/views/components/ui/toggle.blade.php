@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'checked' => false,
    'color' => 'primary',
    'value' => null,
    'size' => null,
    'disabled' => false,
    'labelPosition' => 'right', // left, right
])

@php
    $id = $attributes->get('id') ?? uniqid('toggle-');
    $model = $attributes->wire('model');

    $classes = [
        'toggle',
        match ($color) {
            'primary' => 'toggle-primary',
            'secondary' => 'toggle-secondary',
            'accent' => 'toggle-accent',
            'success' => 'toggle-success',
            'warning' => 'toggle-warning',
            'error' => 'toggle-error',
            'info' => 'toggle-info',
            default => '',
        },
        match ($size) {
            'xs' => 'toggle-xs',
            'sm' => 'toggle-sm',
            'md' => 'toggle-md',
            'lg' => 'toggle-lg',
            default => '',
        },
    ];

    // Detect if color is being bound by Alpine
    $boundColor = $attributes->get('x-bind:color') ?? $attributes->get(':color');
@endphp

<div class="form-control w-fit">
    <x-ui.label class="{{ $labelPosition === 'left' ? 'justify-between gap-3' : 'justify-start gap-4' }} cursor-pointer"
                variant="plain">
        @if ($label && $labelPosition === 'left')
            <span class="label-text {{ $disabled ? 'text-base-content/50' : '' }}">{{ $label }}</span>
        @endif

        <input type="checkbox"
               id="{{ $id }}"
               @if ($name) name="{{ $name }}" @endif
               @if ($value !== null) value="{{ $value }}" @endif
               @if ($checked) checked @endif
               @if ($disabled) disabled @endif
               {{ $attributes->class($classes)->except(['color', 'x-bind:color', ':color']) }}
               @if ($boundColor) x-bind:class='{{ alpineColorClasses($boundColor, 'toggle-') }}' @endif />

        @if ($label && $labelPosition === 'right')
            <span class="label-text {{ $disabled ? 'text-base-content/50' : '' }}">{{ $label }}</span>
        @endif
    </x-ui.label>

    @if ($description)
        <div class="{{ $labelPosition === 'left' ? '' : 'pl-14' }} -mt-1">
            <span class="text-base-content/60 text-xs">{{ $description }}</span>
        </div>
    @endif
</div>
