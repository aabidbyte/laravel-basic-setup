<?php

declare(strict_types=1);

namespace App\Services\Stats\Data;

use App\Enums\Stats\ChartType;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class ChartPayload implements Arrayable, JsonSerializable
{
    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDataset>  $datasets
     */
    public function __construct(
        public ChartType $type,
        public array $labels,
        public array $datasets,
        public array $options = [],
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'data' => [
                'labels' => array_map(fn ($label) => __($label), $this->labels),
                'datasets' => array_map(fn (ChartDataset $dataset) => $dataset->toArray(), $this->datasets),
            ],
            'options' => $this->options,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
