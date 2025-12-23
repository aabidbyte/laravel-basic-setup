@props(['item'])

@php
    $itemAttributes = $item['attributes'] ?? [];
    $isActive = $item['isActive'] ?? false;
    $detailsOpenAttr = $isActive ? 'open' : '';
@endphp

@if ($item['hasItems'] ?? false)
    <details {!! $detailsOpenAttr !!}>
        <summary class="{{ $item['isActive'] ?? false ? 'active' : '' }}">
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5"></x-ui.icon>
            @endif
            <span>{{ $item['title'] ?? '' }}</span>
            @if ($item['hasBadge'] ?? false)
                <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
            @endif
        </summary>
        <div>
            @foreach ($item['items'] ?? [] as $subItem)
                <x-navigation.item :item="$subItem"></x-navigation.item>
            @endforeach
        </div>
    </details>
@elseif ($item['hasUrl'] ?? false)
    @if ($item['isExternal'] ?? false)
        <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer"
            {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5"></x-ui.icon>
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
            @endif
        </a>
    @else
        <a href="{{ $item['url'] ?? '#' }}" wire:navigate class="{{ $item['isActive'] ?? false ? 'active' : '' }}"
            {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5"></x-ui.icon>
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <x-ui.badge size="sm">{{ $item['badge'] ?? '' }}</x-ui.badge>
            @endif
        </a>
    @endif
@endif
