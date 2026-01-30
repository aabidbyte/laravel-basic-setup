<?php

declare(strict_types=1);

namespace App\Livewire\Bases;

use App\Enums\Stats\ChartComponentType;
use App\Enums\Ui\PlaceholderType;
use Livewire\Attributes\Url;
use ReflectionClass;
use ReflectionMethod;

abstract class BaseChartsComponent extends LivewireBaseComponent
{
    /**
     * Placeholder type for lazy loading skeleton.
     */
    protected PlaceholderType $placeholderType = PlaceholderType::CHARTS_STATS;

    /**
     * Date Range Start (Y-m-d)
     */
    #[Url]
    public ?string $dateFrom = null;

    /**
     * Date Range End (Y-m-d)
     */
    #[Url]
    public ?string $dateTo = null;

    /**
     * Listen for date range updates unique to this component class.
     * Dispatch: $dispatch('date-range-changed:UsersChartsIndex', from: '...', to: '...')
     */
    public function getListeners(): array
    {
        return [
            'date-range-changed:' . class_basename(static::class) => 'updateDateRange',
            'date-range-changed:' . class_basename(self::class) => 'updateDateRange',
        ];
    }

    public function updateDateRange(?string $from = null, ?string $to = null): void
    {
        $this->dateFrom = $from;
        $this->dateTo = $to;
    }

    /**
     * The title of the charts section.
     */
    protected string $title = '';

    /**
     * The description of the charts section.
     */
    protected string $description = '';

    /**
     * Enable accordion wrapper behavior on mobile/tablet.
     * On desktop, the content is always visible regardless of this setting,
     * unless this is set to false, in which case it's never an accordion.
     */
    protected bool $hasAccordion = true;

    /**
     * specific to the charts component logic, we're not using properties to pass data to the view
     * we are using the render method to pass data to the view
     * but we want to be able to access these properties in the view
     * so we need to make them public or use a method to access them
     */

    /**
     * The CSS grid class for the layout.
     * Default: 1 col mobile, 2 cols tablet, 4 cols desktop.
     */
    protected string $layout = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4';

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function hasAccordion(): bool
    {
        return $this->hasAccordion;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function render()
    {
        return view('components.charts.system', [
            'components' => $this->getChartComponents(),
        ]);
    }

    /**
     * Optional schema to define layout and order.
     * Return type: ['methodName' => ['class' => '...'], 'methodName2']
     */
    protected function schema(): ?array
    {
        return null;
    }

    /**
     * Reflection magic to get all public/computed properties that are Stats/Charts.
     */
    protected function getChartComponents(): array
    {
        $components = [];
        $schema = $this->schema();

        // If schema is defined, use it to drive the components list
        if ($schema !== null) {
            foreach ($schema as $key => $config) {
                // Handle non-associative array (just method name)
                $methodName = \is_int($key) ? $config : $key;
                $itemConfig = \is_array($config) ? $config : [];

                if (! \method_exists($this, $methodName)) {
                    continue;
                }

                $value = $this->$methodName();

                if ($value instanceof \App\Services\Stats\Data\MetricPayload) {
                    $components[] = [
                        'type' => ChartComponentType::STAT,
                        'payload' => $value,
                        'class' => $itemConfig['class'] ?? null,
                    ];
                } elseif ($value instanceof \App\Services\Stats\Data\ChartPayload) {
                    $components[] = [
                        'type' => ChartComponentType::CHART,
                        'payload' => $value,
                        'class' => $itemConfig['class'] ?? null,
                    ];
                }
            }

            return $components;
        }

        // Fallback: Reflection auto-discovery
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(\Livewire\Attributes\Computed::class);
            if (! empty($attributes)) {
                $propertyName = $method->getName();

                $value = $this->$propertyName();

                if ($value instanceof \App\Services\Stats\Data\MetricPayload) {
                    $components[] = [
                        'type' => ChartComponentType::STAT,
                        'payload' => $value,
                        'class' => null,
                    ];
                } elseif ($value instanceof \App\Services\Stats\Data\ChartPayload) {
                    $components[] = [
                        'type' => ChartComponentType::CHART,
                        'payload' => $value,
                        'class' => null,
                    ];
                }
            }
        }

        return $components;
    }
}
