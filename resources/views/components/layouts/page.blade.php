{{--
    Page Content Layout Component
    
    Provides unified structure for CRUD pages with optional back button and action areas.
    Provides unified structure for CRUD pages with optional back button and action areas.
    Livewire automatically wraps this in defaults (e.g., layouts.app).
    
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
    'showBottomBar' => null,
])

@php
    $backLabel = $backLabel ?? __('actions.back');
    $showBottomBar = $showBottomBar ?? isset($bottomActions) || isset($bottomLeft);
@endphp

<div class="flex flex-col gap-2 sm:gap-4">
    {{-- Top Row: Back Button + Actions --}}
    @if (!$showBottomBar)
        <div
             class="sticky top-0 z-30 flex flex-col gap-3 p-2 backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:gap-2">
            <div>
                @if ($backHref)
                    <x-ui.button href="{{ $backHref }}"
                                 wire:navigate
                                 variant="ghost"
                                 size="sm"
                                 class="gap-2">
                        <x-ui.icon name="arrow-left"
                                   size="sm"></x-ui.icon>
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
    @endif
    {{-- Content Slot --}}
    <div class="flex-1 px-4">
        {{ $slot }}
    </div>

    {{-- Bottom Row: Back Button + Actions (Optional) --}}
    @if ($showBottomBar)
        <div
             class="border-base-300 sticky bottom-0 z-30 flex flex-col gap-3 border-t p-2 backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:gap-2">
            <div>
                @if (isset($bottomLeft))
                    {{ $bottomLeft }}
                @elseif($backHref)
                    <x-ui.button href="{{ $backHref }}"
                                 wire:navigate
                                 variant="ghost"
                                 size="sm"
                                 class="gap-2">
                        <x-ui.icon name="arrow-left"
                                   size="sm"></x-ui.icon>
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
