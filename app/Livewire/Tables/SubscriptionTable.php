<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\DataTable\Datatable;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class SubscriptionTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = 'subscriptions.list_title';

    /**
     * Whether to show the search bar.
     */
    public bool $showSearch = true;

    public ?Tenant $tenant = null;

    public ?Plan $plan = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Subscription::class);
    }

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $query = Subscription::query()
            ->select('subscriptions.*')
            ->with(['plan', 'tenant']);

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->tenant_id);
        }

        if ($this->plan) {
            $query->where('plan_id', $this->plan->id);
        }

        return $query;
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        $columns = [];

        if (! $this->plan) {
            $columns[] = Column::make(__('subscriptions.plan'), 'plan.name')
                ->format(fn ($value) => '<strong>' . ($value ?? __('subscriptions.no_plan')) . '</strong>')
                ->html();
        }

        if (! $this->tenant) {
            $columns[] = Column::make(__('subscriptions.tenant'), 'tenant.name')
                ->format(fn ($value) => '<strong>' . ($value ?? __('subscriptions.no_tenant')) . '</strong>')
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

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->variant('ghost')
                ->color('primary')
                ->route(fn (Subscription $subscription) => route('subscriptions.edit', $subscription))
                ->can(PolicyAbilities::UPDATE),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('subscriptions.delete_confirm'))
                ->execute(function (Subscription $subscription): void {
                    $subscription->delete();

                    NotificationBuilder::make()
                        ->title('subscriptions.deleted_successfully', ['name' => $subscription->label()])
                        ->success()
                        ->send();
                })
                ->can(PolicyAbilities::DELETE),
        ];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('subscriptions.edit')) {
            return Action::make()
                ->route('subscriptions.edit', $uuid)
                ->can(PolicyAbilities::UPDATE);
        }

        return null;
    }
}
