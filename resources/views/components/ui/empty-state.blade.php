{{--
    Empty State Component Props:
    - icon: Icon name (default: 'inbox')
    - title: Optional title text
    - description: Description text (required)
    - iconSize: Icon size class (default: 'h-12 w-12')
    - iconClass: Additional icon classes
    - class: Additional container classes
--}}
@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
    'iconSize' => 'h-12 w-12',
    'iconClass' => 'opacity-50 mb-4',
    'class' => '',
])

<div class="card bg-base-200 {{ $class }}">
    <div class="card-body text-center">
        <x-ui.icon :name="$icon"
                   class="{{ $iconSize }} {{ $iconClass }} mx-auto"></x-ui.icon>
        @if ($title)
            <x-ui.title level="4"
                        class="text-base-content/70">{{ $title }}</x-ui.title>
        @endif
        @if ($description)
            <p class="text-base-content/60">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
