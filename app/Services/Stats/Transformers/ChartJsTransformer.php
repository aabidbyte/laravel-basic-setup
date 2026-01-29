<?php

declare(strict_types=1);

namespace App\Services\Stats\Transformers;

use App\Services\Stats\Contracts\ChartTransformerContract;
use App\Services\Stats\Data\ChartPayload;

class ChartJsTransformer implements ChartTransformerContract
{
    public function transform(ChartPayload $payload): array
    {
        // Chart.js expects a structure very similar to our normalized payload.
        // We can inject default options here or map specific keys if needed.

        $config = $payload->toArray();

        // Ensure plugins object exists
        if (! isset($config['options']['plugins'])) {
            $config['options']['plugins'] = [];
        }

        // Apply default responsive behavior if not set
        if (! isset($config['options']['responsive'])) {
            $config['options']['responsive'] = true;
        }

        if (! isset($config['options']['maintainAspectRatio'])) {
            $config['options']['maintainAspectRatio'] = false;
        }

        return $config;
    }
}
