@props([
    'config' => [],
    'height' => '300px',
])

@php
    use App\Services\Stats\Data\ChartPayload;
    use App\Services\Stats\Transformers\ChartJsTransformer;
    use Illuminate\Contracts\Support\Arrayable;

    if (!isset($chartConfig)) {
        $chartConfig = match (true) {
            $config instanceof ChartPayload => new ChartJsTransformer()->transform($config),
            $config instanceof Arrayable => $config->toArray(),
            default => (array) $config,
        };
    }
@endphp

<div x-data="chartUi"
     data-config='@json($chartConfig)'
     class="relative w-full"
     style="height: {{ $height }}">
    <canvas x-ref="canvas"></canvas>
</div>
