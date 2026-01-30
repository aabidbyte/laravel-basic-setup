@php
    use App\Enums\Stats\ChartComponentType;
    $layout = $this->getLayout();
    $hasAccordion = $this->hasAccordion();
    $title = $this->getTitle();
    $description = $this->getDescription();
@endphp

<div>
    <x-ui.accordion :enabled="$hasAccordion"
                    :title="$title"
                    :description="$description"
                    class="lg:collapse-open mb-6">
        <div @class([
            "grid $layout gap-6",
            'pt-4' => $hasAccordion,
            'mb-6' => !$hasAccordion,
        ])>
            @foreach ($components as $index => $chartItem)
                @if ($chartItem['type'] === ChartComponentType::STAT)
                    <div class="{{ $chartItem['class'] ?? 'col-span-1' }}"
                         wire:key="stat-{{ $index }}">
                        <x-ui.stats.card :stat="$chartItem['payload']" />
                    </div>
                @elseif($chartItem['type'] === ChartComponentType::CHART)
                    <div class="{{ $chartItem['class'] ?? 'col-span-1 md:col-span-2 lg:col-span-2' }}"
                         wire:key="chart-{{ $index }}">
                        <div class="card bg-base-100 border-base-200 border shadow-sm">
                            <div class="card-body p-4">
                                <x-ui.chart :config="$chartItem['payload']"
                                            height="200px" />
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-ui.accordion>
</div>
