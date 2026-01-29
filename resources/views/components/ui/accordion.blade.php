@props([
    'name' => null, // Input name for "accordion" behavior (radio group)
    'open' => false, // Initial state (if not using x-data / radio)
    'title' => '', // Accordion Header
    'description' => '', // Optional helper text
    'icon' => null, // Optional icon component name
    'arrow' => true, // Show arrow indicator
    'plus' => false, // Show plus/minus indicator (mutually exclusive with arrow)
    'forceOpen' => false, // Force open state via class (for desktop overrides)
    'enabled' => true, // Whether to render the accordion wrapper or just the content
])

@php
    $modifier = match (true) {
        $plus => 'collapse-plus',
        $arrow => 'collapse-arrow',
        default => '',
    };
@endphp

@if ($enabled)
    <div
         {{ $attributes->class([
             'collapse bg-base-100 border border-base-300 shadow-sm rounded-box',
             $modifier,
             'collapse-open' => $forceOpen, // Force open class
         ]) }}>
        {{-- Trigger --}}
        @if ($name)
            <input type="radio"
                   name="{{ $name }}"
                   @checked($open) />
        @else
            <input type="checkbox"
                   @checked($open) />
        @endif

        {{-- Header --}}
        <div class="collapse-title flex items-center gap-3 text-xl font-medium">
            @if ($icon)
                <x-ui.icon :name="$icon"
                           class="h-6 w-6 flex-shrink-0" />
            @endif

            <div class="flex flex-col gap-0.5">
                <span>{{ $title }}</span>
                @if ($description)
                    <span class="text-base-content/70 text-sm font-normal">{{ $description }}</span>
                @endif
            </div>
        </div>

        {{-- Content --}}
        <div class="collapse-content">
            {{ $slot }}
        </div>
    </div>
@else
    {{ $slot }}
@endif
