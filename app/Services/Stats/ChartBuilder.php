<?php

declare(strict_types=1);

namespace App\Services\Stats;

use App\Enums\Stats\ChartType;
use App\Services\Stats\Data\ChartDataset;
use App\Services\Stats\Data\ChartPayload;
use RuntimeException;

class ChartBuilder
{
    protected ?ChartType $type = null;

    protected array $labels = [];

    protected array $datasets = [];

    protected array $options = [];

    public static function make(): self
    {
        return new self;
    }

    public function type(ChartType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function labels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param  array  $options  Extra dataset options (colors, fill, etc.)
     */
    public function dataset(string $label, array $data, array $options = []): self
    {
        $this->datasets[] = new ChartDataset($label, $data, $options);

        return $this;
    }

    /**
     * Merge global chart options.
     */
    public function options(array $options): self
    {
        $this->options = array_replace_recursive($this->options, $options);

        return $this;
    }

    /**
     * Shortcut to set the chart title option.
     */
    public function title(string $title): self
    {
        // Chart.js specific structure for title
        return $this->options([
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => __($title),
                ],
            ],
        ]);
    }

    public function build(): ChartPayload
    {
        if (! $this->type) {
            throw new RuntimeException('Chart type must be set.');
        }

        return new ChartPayload(
            type: $this->type,
            labels: $this->labels,
            datasets: $this->datasets,
            options: $this->options,
        );
    }
}
