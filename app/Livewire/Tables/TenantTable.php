<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Tenancy\TenantService;
use Illuminate\Database\Eloquent\Builder;

class TenantTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->title = __('tenancy.tenants_management');
    }

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        // We alias id to uuid because the Datatable system expects a uuid column
        return Tenant::query()
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
                ->sortable(),

            Column::make(__('tenancy.tenant_name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('tenancy.plan'), 'plan')
                ->searchable()
                ->sortable(),

            Column::make(__('tenancy.users_count'), 'users_count')
                ->sortable(),

            Column::make(__('common.created_at'), 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('Y-m-d H:i') ?? '-'),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->color('primary')
                ->route(fn ($tenant) => route('tenants.settings.edit', $tenant)),

            Action::make('subscriptions', __('subscriptions.title'))
                ->icon('credit-card')
                ->color('ghost')
                ->route(fn ($tenant) => route('tenants.subscriptions', $tenant)),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->color('error')
                ->confirm(__('tenancy.confirm_delete_tenant'))
                ->execute(function (Tenant $tenant) {
                    app(TenantService::class)->deleteTenant($tenant);
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => __('tenancy.tenant_deleted'),
                    ]);
                }),
        ];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        return Action::make('edit')
            ->route('tenants.settings.edit', $uuid);
    }
}
