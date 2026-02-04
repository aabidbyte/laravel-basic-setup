@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'size' => 'md',
    'color' => null,
    'containerClass' => null,
])

@php
    // Attributes to promote to the container for proper Alpine.js scope sharing
    $containerAttributeNames = ['x-data', 'x-init', 'x-cloak', 'x-show', 'x-transition'];
    $containerAttributes = $attributes->only($containerAttributeNames);

    // Attributes for the input/textarea itself
    // We exclude props and container attributes
    $inputAttributes = $attributes->except([
        ...$containerAttributeNames,
        'label',
        'error',
        'required',
        'size',
        'color',
        'containerClass',
        'type',
        'x-bind:color',
        ':color',
    ]);

    $inputId = $attributes->get('id') ?? uniqid('input-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);

    $sizeClass = match ($size) {
        'xs' => 'input-xs',
        'sm' => 'input-sm',
        'md' => '',
        'lg' => 'input-lg',
        'xl' => 'input-xl',
        default => 'input-md',
    };

    $colorClasses = [
        'primary' => 'input-primary',
        'secondary' => 'input-secondary',
        'accent' => 'input-accent',
        'info' => 'input-info',
        'success' => 'input-success',
        'warning' => 'input-warning',
        'error' => 'input-error',
    ];

    $colorClass = isset($color) ? $colorClasses[$color] ?? '' : '';

    // Detect if color is being bound by Alpine
    $boundColor = $attributes->get('x-bind:color') ?? $attributes->get(':color');
@endphp

<div @class(['flex flex-col gap-1', $containerClass])
     {{ $containerAttributes }}>
    @if ($label)
        <x-ui.label :for="$inputId"
                    :text="$label"
                    :required="$required">
            @isset($labelAppend)
                <x-slot:labelAppend>{{ $labelAppend }}</x-slot:labelAppend>
            @endisset
        </x-ui.label>
    @endif

    <div class="relative">
        @isset($prepend)
            <div class="pointer-events-none absolute inset-y-0 left-0 z-20 flex items-center pl-3">
                {{ $prepend }}
            </div>
        @endisset

        @if ($attributes->get('type') === 'textarea')
            <textarea {{ $inputAttributes->merge(['class' => 'textarea  w-full ' . ($hasError ? 'textarea-error' : str_replace('input-', 'textarea-', $sizeClass)) . ' ' . str_replace('input-', 'textarea-', $colorClass)]) }}
                      id="{{ $inputId }}"
                      @if ($boundColor) x-bind:class='{{ alpineColorClasses($boundColor, 'textarea-') }}' @endif></textarea>
        @else
            <input {{ $inputAttributes->merge(['class' => 'input w-full ' . ($hasError ? 'input-error' : $sizeClass) . ' ' . $colorClass . (isset($prepend) ? ' pl-10' : '') . (isset($append) ? ' pr-10' : '')]) }}
                   type="{{ $attributes->get('type', 'text') }}"
                   @if ($attributes->get('type') === 'password') x-bind:type="showPassword ? 'text' : 'password'" @endif
                   id="{{ $inputId }}"
                   @if ($boundColor) x-bind:class='{{ alpineColorClasses($boundColor, 'input-') }}' @endif />
        @endif

        @isset($append)
            <div class="pointer-events-none absolute inset-y-0 right-0 z-20 flex items-center pr-3">
                <div class="pointer-events-auto flex items-center">
                    {{ $append }}
                </div>
            </div>
        @endisset
    </div>

    {{ $slot }}

    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />
</div>
