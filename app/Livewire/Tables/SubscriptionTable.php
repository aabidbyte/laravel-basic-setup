<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionTable extends Datatable
{
    public ?Tenant $tenant = null;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $query = Subscription::query();

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->id);
        }

        return $query->with('plan')->latest();
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('subscriptions.plan'), 'plan.name')
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('subscriptions.status'), 'status')
                ->format(fn ($value) => $value->label())
                ->badge(fn ($subscription) => $subscription->status->color()),

            Column::make(__('subscriptions.starts_at'), 'starts_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? '-'),

            Column::make(__('subscriptions.ends_at'), 'ends_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? __('subscriptions.no_expiry')),

            Column::make(__('subscriptions.trial_ends_at'), 'trial_ends_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? '-'),
        ];
    }
}
