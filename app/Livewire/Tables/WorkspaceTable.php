<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WorkspaceTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = 'Your Workspaces';

    /**
     * Whether to show the search bar.
     */
    public bool $showSearch = true;

    /**
     * Whether to show the header.
     */
    public bool $showHeader = false;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return Tenant::query()->whereRaw('1 = 0');
        }

        $query = Tenant::query();

        // If user is not a super admin, only show tenants they belong to
        if (! $user->hasRole(\App\Constants\Auth\Roles::SUPER_ADMIN)) {
            $query->whereIn('id', $user->tenants->pluck('id'));
        }

        // Hide the current tenant if we are in a tenant context
        if (tenant()) {
            $query->where('id', '!=', tenant('id'));
        }

        return $query->select(['tenants.id', 'tenants.name', 'tenants.plan', 'tenants.id as uuid']);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('tenancy.tenant_name'), 'name')
                ->searchable()
                ->sortable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('tenancy.tenant_id'), 'id')
                ->searchable()
                ->sortable()
                ->class('text-xs text-base-content/50'),

            Column::make(__('tenancy.plan'), 'plan')
                ->format(fn ($value) => $value ?? __('tenancy.free_plan')),
        ];
    }

    /**
     * Define the filters for the table.
     */
    protected function getFilterDefinitions(): array
    {
        return [
            \App\Services\DataTable\Builders\Filter::make('plan', __('tenancy.plan'))
                ->options([
                    'free' => __('tenancy.plans.free'),
                    'pro' => __('tenancy.plans.pro'),
                    'enterprise' => __('tenancy.plans.enterprise'),
                ]),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        return Action::make('switch')
            ->execute(fn ($tenant) => $this->switchTo($tenant->id));
    }

    /**
     * Switch to a different workspace.
     */
    protected function switchTo(string $tenantId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        // Protection: Don't switch to the current tenant
        if (tenant('id') === $tenantId) {
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => __('tenancy.already_in_workspace'),
            ]);

            return;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant || (! $user->hasRole(\App\Constants\Auth\Roles::SUPER_ADMIN) && ! $user->tenants->contains('id', $tenantId))) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('tenancy.access_denied'),
            ]);

            return;
        }

        $domain = $tenant->domains()->first();

        if (! $domain) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('tenancy.domain_not_found'),
            ]);

            return;
        }

        // Redirect to the tenant domain
        $url = (request()->secure() ? 'https://' : 'http://') . $domain->domain . '/dashboard';
        $this->redirect($url);
    }
}
