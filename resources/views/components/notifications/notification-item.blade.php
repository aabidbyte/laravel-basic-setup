@props(['iconName', 'iconClass', 'title', 'subtitle' => null, 'createdAt', 'isRead' => false])

<div class="flex items-start gap-2">
    <div class="mt-0.5 flex-shrink-0">
        <x-ui.icon name="{{ $iconName }}"
                   class="{{ $iconClass }}"></x-ui.icon>
    </div>
    <div class="flex-1">
        <div class="truncate">{{ $title }}</div>
        @if ($subtitle)
            <div class="truncate text-xs opacity-70">{{ $subtitle }}</div>
        @endif
        <div class="mt-1 text-xs opacity-60">
            {{ $createdAt->diffForHumans() }}
        </div>
    </div>
    @if (!$isRead)
        <x-ui.badge variant="primary"
                    size="xs"></x-ui.badge>
    @endif
</div>
