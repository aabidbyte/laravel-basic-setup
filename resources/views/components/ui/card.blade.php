@props([
    'title' => null,
    'description' => null,
    'class' => '',
    'padding' => 'p-6',
    'shadow' => 'shadow-sm',
    'border' => 'border border-base-200',
    'bg' => 'bg-base-100',
])

<div {{ $attributes->merge(['class' => trim("card {$bg} {$shadow} {$border} rounded-box {$class}")]) }}>
    <div class="card-body {{ $padding }}">
        @if ($title || isset($actions))
            <div class="mb-4 flex items-start justify-between gap-4">
                @if ($title)
                    <div class="flex flex-col">
                        <h3 class="text-lg font-semibold">{{ $title }}</h3>
                        @if ($description)
                            <p class="text-base-content/60 text-sm">{{ $description }}</p>
                        @endif
                    </div>
                @endif

                @if (isset($actions))
                    <div class="card-actions justify-end">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        @elseif ($description)
            <p class="text-base-content/60 mb-4 text-sm">{{ $description }}</p>
        @endif

        {{ $slot }}

        @if (isset($footer))
            <div class="card-actions mt-6 justify-end border-t border-base-content/10 pt-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
