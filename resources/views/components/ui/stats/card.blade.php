@props(['stat'])

@php
    // Handle MetricPayload or array
    $payload = $stat instanceof \Illuminate\Contracts\Support\Arrayable ? $stat->toArray() : $stat;

    // Defaults
    $variant = $payload['variant'] ?? 'default';
    $color = $payload['color'] ?? 'primary';
    $trend = $payload['trend'] ?? 'neutral';

    // Style Mapping
    $containerClass = match ($variant) {
        'outline' => 'border border-base-200 bg-transparent',
        'solid' => 'bg-' . $color . ' text-' . $color . '-content',
        default => 'bg-base-100 shadow-sm border border-base-200',
    };

    $trendColor = match ($trend) {
        'up' => 'text-success',
        'down' => 'text-error',
        default => 'text-base-content/60',
    };

    $trendIcon = match ($trend) {
        'up' => 'arrow-trending-up',
        'down' => 'arrow-trending-down',
        default => 'minus',
    };
@endphp

<div class="card {{ $containerClass }} rounded-box w-full">
    <div class="card-body p-4">
        <div class="flex items-start justify-between">
            <div class="flex flex-col">
                <span class="text-base-content/60 text-sm font-medium">{{ $payload['label'] ?? '' }}</span>
                <span class="mt-1 text-2xl font-bold">{{ $payload['value'] ?? 0 }}</span>
            </div>

            @if (isset($payload['icon']))
                <div class="bg-base-200/50 text-{{ $color }} rounded-lg p-2">
                    <x-ui.icon :name="$payload['icon']"
                               class="h-6 w-6" />
                </div>
            @endif
        </div>

        @if (isset($payload['trend_value']))
            <div class="{{ $trendColor }} mt-2 flex items-center gap-1 text-xs font-medium">
                <x-ui.icon :name="$trendIcon"
                           class="h-3 w-3" />
                <span>{{ $payload['trend_value'] }}%</span>
                <span class="text-base-content/60 ml-1">{{ __('vs last month') }}</span>
            </div>
        @endif
    </div>
</div>
