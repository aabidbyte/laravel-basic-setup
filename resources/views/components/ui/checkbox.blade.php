@props([
    'label' => null,
    'description' => null,
    'checked' => false,
    'color' => 'primary',
    'value' => null,
    'size' => null,
])

@php
    $id = $attributes->get('id') ?? uniqid('checkbox-');

    $colorClass = match ($color) {
        'primary' => 'checkbox-primary',
        'secondary' => 'checkbox-secondary',
        'accent' => 'checkbox-accent',
        'success' => 'checkbox-success',
        'warning' => 'checkbox-warning',
        'error' => 'checkbox-error',
        'info' => 'checkbox-info',
        default => '',
    };

    $sizeClass = match ($size) {
        'xs' => 'checkbox-xs',
        'sm' => 'checkbox-sm',
        'md' => 'checkbox-md',
        'lg' => 'checkbox-lg',
        default => '',
    };

    // Detect if color is being bound by Alpine
    $boundColor = $attributes->get('x-bind:color') ?? $attributes->get(':color');
@endphp

<div class="form-control w-fit">
    <x-ui.label variant="inline">
        <input type="checkbox"
               id="{{ $id }}"
               {{ $attributes->merge(['class' => trim("checkbox {$colorClass} {$sizeClass}")])->except(['color', 'x-bind:color', ':color']) }}
               @if ($boundColor) x-bind:class='{{ alpineColorClasses($boundColor, 'checkbox-') }}' @endif
               @if ($value !== null) value="{{ $value }}" @endif
               @if ($checked) checked @endif />
        @if ($label)
            <span class="label-text">{{ $label }}</span>
        @endif
    </x-ui.label>
    @if ($description)
        <div class="-mt-1 pl-9">
            <span class="text-base-content/60 text-xs">{{ $description }}</span>
        </div>
    @endif
</div>
