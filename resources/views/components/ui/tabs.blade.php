@props([
    'tabs' => [],
    'active' => null,
    'target' => 'activeTab',
    'size' => 'lg',
    'style' => 'lifted',
])

@php
    $sizeClass = [
        'sm' => 'tab-sm',
        'md' => '',
        'lg' => 'tab-lg',
        'xl' => 'tab-xl',
    ][$size] ?? 'tab-lg';

    $styleClass = [
        'boxed' => 'tabs-box',
        'bordered' => 'tabs-border',
        'lifted' => 'tabs-lifted',
    ][$style] ?? 'tabs-lifted';
@endphp

<div {{ $attributes->class(['tabs', $styleClass]) }}>
    @foreach ($tabs as $tab)
        @php
            $key = $tab['key'] ?? null;
            $label = $tab['label'] ?? $key;
            $icon = $tab['icon'] ?? null;
            $isActive = $active === $key;
        @endphp

        @if ($key)
            <button type="button"
                    wire:click="$set('{{ $target }}', '{{ $key }}')"
                    @class(['tab', $sizeClass, 'tab-active' => $isActive])>
                @if ($icon)
                    <x-ui.icon :name="$icon"
                               class="mr-2 h-5 w-5" />
                @endif
                {{ $label }}
            </button>
        @endif
    @endforeach
</div>
