<?php

namespace App\View\Components\Ui;

use App\Services\Stats\Data\ChartPayload;
use App\Services\Stats\Transformers\ChartJsTransformer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\Component;

class Chart extends Component
{
    public array $chartConfig;

    public function __construct(
        public $config = [],
        public string $height = '300px',
    ) {
        $this->chartConfig = $this->resolveConfig($config);
    }

    protected function resolveConfig($config): array
    {
        if ($config instanceof ChartPayload) {
            $transformer = new ChartJsTransformer;

            return $transformer->transform($config);
        }

        if ($config instanceof Arrayable) {
            return $config->toArray();
        }

        return (array) $config;
    }

    public function render()
    {
        return view('components.ui.chart');
    }
}
