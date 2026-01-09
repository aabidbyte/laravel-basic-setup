@props(['item'])

@php
    $itemAttributes = $item['attributes'] ?? [];
    $isActive = $item['isActive'] ?? false;
    $hasNestedActive = false;
    // Check if any nested item is active
    foreach ($item['items'] ?? [] as $subItem) {
        if ($subItem['isActive'] ?? false) {
            $hasNestedActive = true;
            break;
        }
    }
    $detailsOpenAttr = $isActive || $hasNestedActive ? 'open' : '';
@endphp

@if ($item['hasItems'] ?? false)
    <details {!! $detailsOpenAttr !!}
             class="nav-details">
        <summary class="nav-summary {{ ($item['isActive'] ?? false) || $hasNestedActive ? 'active' : '' }}">
            <div class="nav-summary-content">
                @if ($item['icon'] ?? null)
                    <x-ui.icon name="{{ $item['icon'] }}"
                               class="h-5 w-5"></x-ui.icon>
                @endif
                <span>{{ $item['title'] ?? '' }}</span>
                @if ($item['hasBadge'] ?? false)
                    <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
                @endif
            </div>
            <x-ui.icon name="chevron-down"
                       class="nav-chevron h-4 w-4"></x-ui.icon>
        </summary>
        <div class="nav-nested-items">
            @foreach ($item['items'] ?? [] as $subItem)
                <x-navigation.item :item="$subItem"></x-navigation.item>
            @endforeach
        </div>
    </details>
@elseif ($item['hasUrl'] ?? false)
    @if ($item['isExternal'] ?? false)
        <a href="{{ $item['url'] ?? '#' }}"
           target="_blank"
           rel="noopener noreferrer"
           {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}"
                           class="h-5 w-5"></x-ui.icon>
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
            @endif
            <x-ui.icon name="arrow-top-right-on-square"
                       class="ml-auto h-4 w-4 opacity-50"></x-ui.icon>
        </a>
    @else
        <a href="{{ $item['url'] ?? '#' }}"
           wire:navigate
           class="{{ $item['isActive'] ?? false ? 'active' : '' }}"
           {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}"
                           class="h-5 w-5"></x-ui.icon>
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
            @endif
        </a>
    @endif
@endif
