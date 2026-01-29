<?php

declare(strict_types=1);

namespace App\Services\Stats\Data;

use App\Enums\Stats\StatTrend;
use App\Enums\Stats\StatVariant;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class MetricPayload implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $label,
        public int|float|string $value,
        public ?float $trendValue = null,
        public ?StatTrend $trend = null,
        public ?string $icon = null,
        public ?string $color = null, // e.g., 'primary', 'success', 'error'
        public StatVariant $variant = StatVariant::DEFAULT,
    ) {}

    public function toArray(): array
    {
        return [
            'label' => __($this->label),
            'value' => $this->value,
            'trend_value' => $this->trendValue,
            'trend' => $this->trend?->value,
            'icon' => $this->icon,
            'color' => $this->color,
            'variant' => $this->variant->value,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
