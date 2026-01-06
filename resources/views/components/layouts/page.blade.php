{{--
    Page Content Layout Component
    
    Provides unified structure for CRUD pages with optional back button and action areas.
    Must be used INSIDE x-layouts.app (not as a wrapper).
    
    Props:
    - backHref: URL for back button (optional)
    - backLabel: Label for back button (default: translated 'Back')
    - showBottomBar: Whether to show bottom action bar (default: false)
    
    Slots:
    - topActions: Actions for top-right area
    - default: Main content
    - bottomLeft: Override bottom-left (instead of back button)
    - bottomActions: Actions for bottom-right area
--}}
@props([
    'backHref' => null,
    'backLabel' => null,
    'showBottomBar' => false,
])

@php
    $backLabel = $backLabel ?? __('ui.actions.back');
@endphp

<div class="flex flex-col gap-4 sm:gap-6">
    {{-- Top Row: Back Button + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            @if ($backHref)
                <x-ui.button
                    href="{{ $backHref }}"
                    wire:navigate
                    variant="ghost"
                    size="sm"
                    class="gap-2"
                >
                    <x-ui.icon
                        name="arrow-left"
                        size="sm"
                    ></x-ui.icon>
                    <span class="hidden sm:inline">{{ $backLabel }}</span>
                </x-ui.button>
            @endif
        </div>

        @if (isset($topActions))
            <div class="flex flex-wrap items-center gap-2">
                {{ $topActions }}
            </div>
        @endif
    </div>

    {{-- Content Slot --}}
    <div class="flex-1">
        {{ $slot }}
    </div>

    {{-- Bottom Row: Back Button + Actions (Optional) --}}
    @if ($showBottomBar || isset($bottomActions) || isset($bottomLeft))
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 pt-4 border-t border-base-300">
            <div>
                @if (isset($bottomLeft))
                    {{ $bottomLeft }}
                @elseif($backHref)
                    <x-ui.button
                        href="{{ $backHref }}"
                        wire:navigate
                        variant="ghost"
                        size="sm"
                        class="gap-2"
                    >
                        <x-ui.icon
                            name="arrow-left"
                            size="sm"
                        ></x-ui.icon>
                        <span class="hidden sm:inline">{{ $backLabel }}</span>
                    </x-ui.button>
                @endif
            </div>

            @if (isset($bottomActions))
                <div class="flex flex-wrap items-center gap-2">
                    {{ $bottomActions }}
                </div>
            @endif
        </div>
    @endif
</div>
