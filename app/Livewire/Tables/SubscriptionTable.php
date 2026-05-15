<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionTable extends Datatable
{
    public ?Tenant $tenant = null;

    public ?Plan $plan = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $query = Subscription::query()->select('subscriptions.*');

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->tenant_id);
        }

        if ($this->plan) {
            $query->where('plan_id', $this->plan->id);
        }

        return $query->with(['plan', 'tenant']);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        $columns = [];

        if (! $this->plan) {
            $columns[] = Column::make(__('subscriptions.plan'), 'plan.name')
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html();
        }

        if (! $this->tenant) {
            $columns[] = Column::make(__('subscriptions.tenant'), 'tenant.name')
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html();
        }

        $columns = array_merge($columns, [
            Column::make(__('subscriptions.status'), 'status')
                ->format(fn ($value) => $value->label())
                ->badge(fn ($subscription) => $subscription->status->color()),

            Column::make(__('subscriptions.starts_at'), 'starts_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? '-'),

            Column::make(__('subscriptions.ends_at'), 'ends_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? __('subscriptions.no_expiry')),

            Column::make(__('subscriptions.trial_ends_at'), 'trial_ends_at')
                ->format(fn ($value) => $value?->format('Y-m-d') ?? '-'),
        ]);

        return $columns;
    }
}
