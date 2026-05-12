<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\DataTable\Datatable;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantService;
use Illuminate\Database\Eloquent\Builder;

class TenantTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Tenant::class);
        $this->title = __('tenancy.tenants_management');
    }

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        // We alias id to uuid because the Datatable system expects a uuid column
        return Tenant::query()
            ->with(['users', 'currentSubscription.plan', 'planModel'])
            ->withCount('users')
            ->select(['tenants.*', 'tenants.id as uuid']);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('tenancy.tenant_id'), 'id')
                ->searchable()
                ->sortable()
                ->class('font-mono text-xs'),

            Column::make(__('tenancy.tenant_name'), 'name')
                ->searchable()
                ->sortable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make('plan', __('tenancy.plan'))
                ->format(fn ($value, $row) => $row->planModel?->name ?? __('tenancy.no_plan'))
                ->sortable()
                ->searchable()
                ->badge(fn ($tenant) => $tenant->planModel ? $tenant->planModel->tier->color() : ($tenant->currentSubscription ? $tenant->currentSubscription->planModel->tier->color() : 'badge-ghost')),

            Column::make(__('tenancy.users_count'), 'users_count')
                ->sortable()
                ->format(fn ($value) => (int) ($value ?? 0))
                ->class('text-center'),

            Column::make(__('common.created_at'), 'created_at')
                ->sortable()
                ->format(fn ($value) => formatDateTime($value)),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('view', __('actions.view'))
                ->icon('eye')
                ->color('ghost')
                ->route(fn ($tenant) => route('tenants.show', $tenant->id))
                ->can(PolicyAbilities::VIEW),

            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->color('primary')
                ->route(fn ($tenant) => route('tenants.settings.edit', $tenant->id))
                ->can(PolicyAbilities::UPDATE),

            Action::make('subscriptions', __('subscriptions.title'))
                ->icon('credit-card')
                ->color('ghost')
                ->route(fn ($tenant) => route('tenants.subscriptions', $tenant->id))
                ->can(PolicyAbilities::VIEW),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->color('error')
                ->confirm(__('tenancy.confirm_delete_tenant'))
                ->execute(function (Tenant $tenant) {
                    app(TenantService::class)->deleteTenant($tenant);
                    NotificationBuilder::make()
                        ->title('tenancy.tenant_deleted')
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
        return Action::make('view')
            ->route('tenants.show', $uuid)
            ->can(PolicyAbilities::VIEW);
    }
}
