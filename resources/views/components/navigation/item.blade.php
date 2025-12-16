@props(['item'])

@php
    $itemAttributes = $item['attributes'] ?? [];
@endphp

@if ($item['hasItems'] ?? false)
    <details @if ($item['isActive'] ?? false) open @endif>
        <summary class="{{ $item['isActive'] ?? false ? 'active' : '' }}">
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5" />
            @endif
            <span>{{ $item['title'] ?? '' }}</span>
            @if ($item['hasBadge'] ?? false)
                <span class="badge badge-sm">{{ $item['badge'] ?? '' }}</span>
            @endif
        </summary>
        <div>
            @foreach ($item['items'] ?? [] as $subItem)
                <x-navigation.item :item="$subItem" />
            @endforeach
        </div>
    </details>
@elseif ($item['hasUrl'] ?? false)
    @if ($item['isExternal'] ?? false)
        <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer"
            {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5" />
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <span class="badge badge-sm">{{ $item['badge'] ?? '' }}</span>
            @endif
        </a>
    @else
        <a href="{{ $item['url'] ?? '#' }}" wire:navigate class="{{ $item['isActive'] ?? false ? 'active' : '' }}"
            {{ $attributes->merge($itemAttributes) }}>
            @if ($item['icon'] ?? null)
                <x-ui.icon name="{{ $item['icon'] }}" class="h-5 w-5" />
            @endif
            {{ $item['title'] ?? '' }}
            @if ($item['hasBadge'] ?? false)
                <span class="badge badge-sm">{{ $item['badge'] ?? '' }}</span>
            @endif
        </a>
    @endif
@endif
