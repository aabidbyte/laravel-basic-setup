@props(['iconName', 'iconClass', 'title', 'subtitle' => null, 'createdAt', 'isRead' => false])

<div class="flex items-start gap-2">
    <div class="flex-shrink-0 mt-0.5">
        <x-ui.icon name="{{ $iconName }}" class="{{ $iconClass }}" />
    </div>
    <div class="flex-1 min-w-0">
        <div class="truncate">{{ $title }}</div>
        @if ($subtitle)
            <div class="text-xs opacity-70 truncate">{{ $subtitle }}</div>
        @endif
        <div class="text-xs opacity-60 mt-1">
            {{ $createdAt->diffForHumans() }}
        </div>
    </div>
    @if (!$isRead)
        <div class="badge badge-primary badge-xs"></div>
    @endif
</div>
