<?php

declare(strict_types=1);

namespace App\Livewire\Charts\Users;

use App\Enums\Stats\ChartType;
use App\Enums\Stats\StatTrend;
use App\Enums\Stats\StatVariant;
use App\Livewire\Bases\BaseChartsComponent;
use App\Models\User;
use App\Services\Stats\ChartBuilder;
use App\Services\Stats\Data\ChartPayload;
use App\Services\Stats\Data\MetricPayload;
use App\Services\Stats\MetricBuilder;
use Livewire\Attributes\Computed;

class UsersChartsIndex extends BaseChartsComponent
{
    public function getTitle(): string
    {
        return __('charts.users_analytics');
    }

    public function getDescription(): string
    {
        return __('charts.users_overview_description');
    }

    protected function schema(): ?array
    {
        return [
            'totalUsersStat' => ['class' => 'col-span-1 md:col-span-2'],
            'activeUsersStat' => ['class' => 'col-span-1 md:col-span-2'],
            'registrationsChart' => ['class' => 'col-span-1 md:col-span-4'],
        ];
    }

    #[Computed]
    public function totalUsersStat(): MetricPayload
    {
        $query = User::query();

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return MetricBuilder::make()
            ->label('Total Users')
            ->value($query->count())
            ->trend(5.2, StatTrend::UP) // Dummy trend for example
            ->icon('users')
            ->color('primary')
            ->build();
    }

    #[Computed]
    public function activeUsersStat(): MetricPayload
    {
        $query = User::query()->where('is_active', true);

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return MetricBuilder::make()
            ->label('Active Users')
            ->value($query->count())
            ->trend(0, StatTrend::NEUTRAL)
            ->icon('check-circle')
            ->color('success')
            ->variant(StatVariant::OUTLINE)
            ->build();
    }

    #[Computed]
    public function registrationsChart(): ChartPayload
    {
        // Example data - in real app, use aggregate queries
        return ChartBuilder::make()
            ->type(ChartType::LINE)
            ->title('charts.registrations_last_6_months')
            ->labels(['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'])
            ->dataset('Registrations', [12, 19, 3, 5, 2, 30], [
                'borderColor' => 'rgb(99, 102, 241)', // Primary color approximation
                'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                'fill' => true,
                'tension' => 0.4,
            ])
            ->build();
    }

    // Render method is inherited from BaseChartsComponent
}
