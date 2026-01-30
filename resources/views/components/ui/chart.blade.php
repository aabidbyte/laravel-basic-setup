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

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('chartUi', () => ({
                    chart: null,
                    config: null,

                    async init() {
                        // Read config from data attribute to avoid CSP/JSON issues in x-data
                        const configData = this.$el.dataset.config;
                        if (configData) {
                            try {
                                this.config = JSON.parse(configData);
                            } catch (e) {
                                console.error('Invalid Chart Config JSON', e);
                                return;
                            }
                        }

                        if (!this.config) {
                            console.error('Chart configuration is missing.');
                            return;
                        }

                        if (!window.Chart) {
                            try {
                                const {
                                    Chart,
                                    registerables
                                } = await import('https://cdn.jsdelivr.net/npm/chart.js/+esm');
                                Chart.register(...registerables);
                                window.Chart = Chart;
                            } catch (e) {
                                console.error('Failed to load Chart.js from CDN', e);
                                return;
                            }
                        }

                        this.$nextTick(() => {
                            this.loadChart();
                        });
                    },

                    loadChart() {
                        if (!window.Chart) {
                            console.error('Chart.js is not loaded.');
                            return;
                        }

                        const ctx = this.$refs.canvas.getContext('2d');

                        if (this.chart) {
                            this.chart.destroy();
                        }

                        this.chart = new window.Chart(ctx, this.config);
                    }
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
