<?php

declare(strict_types=1);

namespace App\Services\Stats\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class ChartDataset implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $label,
        public array $data,
        public array $options = [],
    ) {}

    public function toArray(): array
    {
        return \array_merge([
            'label' => __($this->label),
            'data' => $this->data,
        ], $this->options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
