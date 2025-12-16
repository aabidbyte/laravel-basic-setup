@props(['group'])

@php
    /** @var array $group */
@endphp

@if ($group['isVisible'])
    <div class="menu-title">
        <span>{{ $group['title'] }}</span>
    </div>
    @foreach ($group['items'] as $item)
        <div class="menu-item">
            <x-navigation.item :item="$item" />
        </div>
    @endforeach
@endif
