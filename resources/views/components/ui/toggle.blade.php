@props([
    'label' => null,
    'description' => null,
    'checked' => false,
    'color' => 'primary',
    'value' => null,
    'size' => null,
])

@php
    $id = $attributes->get('id') ?? uniqid('toggle-');
    
    $colorClass = match($color) {
        'primary' => 'toggle-primary',
        'secondary' => 'toggle-secondary',
        'accent' => 'toggle-accent',
        'success' => 'toggle-success',
        'warning' => 'toggle-warning',
        'error' => 'toggle-error',
        'info' => 'toggle-info',
        default => '',
    };
    
    $sizeClass = match($size) {
        'xs' => 'toggle-xs',
        'sm' => 'toggle-sm',
        'md' => 'toggle-md',
        'lg' => 'toggle-lg',
        default => '',
    };
@endphp

<div class="form-control w-fit">
    <label class="label cursor-pointer justify-start gap-3">
        <input
            type="checkbox"
            id="{{ $id }}"
            {{ $attributes->merge(['class' => trim("toggle {$colorClass} {$sizeClass}")]) }}
            @if($value !== null) value="{{ $value }}" @endif
            @if($checked) checked @endif
        />
        @if($label)
            <span class="label-text">{{ $label }}</span>
        @endif
    </label>
    @if($description)
        <div class="pl-14 -mt-1">
            <span class="text-xs text-base-content/60">{{ $description }}</span>
        </div>
    @endif
</div>
