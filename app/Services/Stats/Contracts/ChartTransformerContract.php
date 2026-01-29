<?php

declare(strict_types=1);

namespace App\Services\Stats\Contracts;

use App\Services\Stats\Data\ChartPayload;

interface ChartTransformerContract
{
    /**
     * Transform the generic chart payload into the library-specific configuration.
     */
    public function transform(ChartPayload $payload): array;
}
