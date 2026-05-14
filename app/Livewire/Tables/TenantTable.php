<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Constants\Auth\Roles;
use App\Livewire\DataTable\Datatable;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\UserImpersonationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class TenantTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = 'tenancy.your_tenants';

    /**
     * Whether to show the search bar.
     */
    public bool $showSearch = true;

    /**
     * Whether to show the header.
     */
    public bool $showHeader = false;

    /**
     * Whether this table is used in the tenant switcher.
     */
    public bool $isSwitcher = false;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return Tenant::query()->whereRaw('1 = 0');
        }

        $query = Tenant::query()->with(['planModel']);

        // If user is not a super admin, only show tenants they belong to
        if (! $user->hasRole(Roles::SUPER_ADMIN)) {
            $query->whereIn('id', $user->tenants->pluck('id'));
        }

        // Hide the current tenant if we are in a tenant context
        if (tenant()) {
            $query->where('id', '!=', tenant('id'));
        }

        return $query
            ->select(['tenants.id', 'tenants.name', 'tenants.plan', 'tenants.color', 'tenants.id as uuid'])
            ->withCount('users');
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
                ->format(fn ($value, $row) => $row->planModel?->name ?? __('tenancy.free_plan')),

            Column::make(__('fields.color'), 'color')
                ->format(fn ($value) => view('components.ui.badge', [
                    'color' => $value,
                    'size' => 'sm',
                    'text' => __("fields.colors.{$value}"),
                ])->render())
                ->html(),

            Column::make(__('tenancy.users_count'), 'users_count')
                ->sortable()
                ->format(fn ($value) => view('components.ui.badge', [
                    'color' => 'primary',
                    'size' => 'sm',
                    'text' => (string) $value,
                ])->render())
                ->html(),
        ];
    }

    /**
     * Define the filters for the table.
     */
    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('plan', __('tenancy.plan'))
                ->options([
                    'free' => __('tenancy.plans.free'),
                    'pro' => __('tenancy.plans.pro'),
                    'enterprise' => __('tenancy.plans.enterprise'),
                ]),
        ];
    }

    /**
     * Find a model by its UUID.
     *
     * Overridden to allow finding the current tenant even if it's hidden from the table view,
     * so that the protection logic in switchTo can fire a notification.
     */
    protected function findModelByUuid(string $uuid): ?Model
    {
        return Tenant::query()
            ->with(['planModel'])
            ->withCount('users')
            ->whereKey($uuid)
            ->first();
    }

    /**
     * Define the row click action.
     */
    protected function rowActions(): array
    {
        $actions = [];

        $actions[] = Action::make('switch', __('tenancy.switch'))
            ->icon('arrow-path')
            ->color('success')
            ->variant('ghost')
            ->execute(fn ($tenant) => $this->switchTo($tenant->id));

        if (Route::has('tenants.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn ($tenant) => route('tenants.show', $tenant->id))
                ->variant('ghost')
                ->color('info')
                ->can(PolicyAbilities::VIEW);
        }

        if (Route::has('tenants.settings.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn ($tenant) => route('tenants.settings.edit', $tenant->id))
                ->variant('ghost')
                ->color('primary')
                ->can(PolicyAbilities::UPDATE);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (Tenant $tenant) {
                $tenant->delete();
                NotificationBuilder::make()
                    ->title('actions.deleted_successfully', ['tenant' => $tenant->label()])
                    ->success()
                    ->send();
            })
            ->can(PolicyAbilities::DELETE);

        return $actions;
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        if ($this->isSwitcher) {
            return Action::make('switch', __('tenancy.switch'))
                ->execute(fn ($tenant) => $this->switchTo($tenant->id));
        }

        if (Route::has('tenants.show')) {
            return Action::make('view', __('actions.view'))
                ->route(fn ($tenant) => route('tenants.show', $tenant->id))
                ->can(PolicyAbilities::VIEW);
        }

        return null;
    }

    /**
     * Switch to a different tenant.
     */
    public function switchTo(string $tenantId): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        // Protection: Don't switch to the current tenant
        if (tenant('id') === $tenantId) {
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => __('tenancy.already_in_tenant'),
            ]);

            return;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant || (! $user->hasRole(Roles::SUPER_ADMIN) && ! $user->tenants->contains('id', $tenantId))) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('tenancy.access_denied'),
            ]);

            return;
        }

        // Use Impersonation for seamless switch without re-login
        $impersonation = app(UserImpersonationService::class)->execute($user, $user, $tenant);

        if ($impersonation['type'] === 'tenant') {
            $this->redirect($impersonation['url']);

            return;
        }

        $this->redirect('/dashboard');
    }
}
