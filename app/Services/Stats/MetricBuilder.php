<?php

declare(strict_types=1);

namespace App\Services\Stats;

use App\Enums\Stats\StatTrend;
use App\Enums\Stats\StatVariant;
use App\Services\Stats\Data\MetricPayload;
use RuntimeException;

class MetricBuilder
{
    protected ?string $label = null;

    protected int|float|string|null $value = null;

    protected ?float $trendValue = null;

    protected ?StatTrend $trend = null;

    protected ?string $icon = null;

    protected ?string $color = null;

    protected StatVariant $variant = StatVariant::DEFAULT;

    public static function make(): self
    {
        return new self;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function value(int|float|string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function trend(float $value, StatTrend $direction): self
    {
        $this->trendValue = $value;
        $this->trend = $direction;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function variant(StatVariant $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function build(): MetricPayload
    {
        if ($this->label === null) {
            throw new RuntimeException('Metric label is required.');
        }

        if ($this->value === null) {
            throw new RuntimeException('Metric value is required.');
        }

        return new MetricPayload(
            label: $this->label,
            value: $this->value,
            trendValue: $this->trendValue,
            trend: $this->trend,
            icon: $this->icon,
            color: $this->color,
            variant: $this->variant,
        );
    }
}
