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
@endphp

<div class="form-control w-fit">
    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox"
               id="{{ $id }}"
               {{ $attributes->merge(['class' => trim("checkbox {$colorClass} {$sizeClass}")]) }}
               @if ($value !== null) value="{{ $value }}" @endif
               @if ($checked) checked @endif />
        @if ($label)
            <span class="label-text">{{ $label }}</span>
        @endif
    </label>
    @if ($description)
        <div class="-mt-1 pl-9">
            <span class="text-base-content/60 text-xs">{{ $description }}</span>
        </div>
    @endif
</div>
