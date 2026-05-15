<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Constants\Auth\Roles;
use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableFilterType;
use App\Livewire\DataTable\Datatable;
use App\Models\CentralUser;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantMembershipQuery;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class UserTable extends Datatable
{
    public const CENTRAL_USERS_FILTER = TenantMembershipQuery::CENTRAL_RECORDS_FILTER;

    /**
     * Mount the component and authorize access
     */
    public function mount(): void
    {
        // Authorize access to the list
        $this->authorize(PolicyAbilities::VIEW_ANY, User::class);
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        $relations = ['roles', 'tenants'];

        // Only include teams if we are in a tenant context or the table exists
        if (tenant() || Schema::hasTable('teams')) {
            $relations[] = 'teams';
        }

        $query = $this->userQuery()
            ->with($relations)
            ->select('users.*');

        return $this->tenantMembershipQuery()->apply($query, $this->tenantAudience());
    }

    /**
     * Get column definitions
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        $columns = [
            Column::make(__('table.users.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.users.email'), 'email')
                ->sortable()
                ->searchable()
                ->class('text-base-content/70'),

            Column::make(__('table.users.status'), 'is_active')
                ->sortable()
                ->format(fn ($value) => $value
                    ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('users.active'), ['color' => 'success', 'size' => 'sm'])
                    : DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('users.inactive'), ['color' => 'error', 'size' => 'sm']))
                ->html(),

            Column::make(__('table.users.roles'), 'roles_for_datatable')
                ->content(function (User $user) {
                    $roles = $user->roles->map(fn (Role $role) => $role->display_name ?? $role->name)->toArray();

                    return \count($roles) > 3
                        ? [trans_choice('users.roles_count', \count($roles))]
                        : $roles;
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'primary', 'size' => 'sm']),

            Column::make(__('table.users.tenants'), 'tenants_for_datatable')
                ->content(fn (User $user) => $this->tenantMembershipQuery()->tenantLabelsFor($user))
                ->type(DataTableUi::UI_BADGE, ['color' => 'accent', 'size' => 'sm']),
        ];

        // Only add teams column if we are in a tenant context or the table exists
        if (tenant() || Schema::hasTable('teams')) {
            $columns[] = Column::make(__('table.users.teams'), 'teams_for_datatable')
                ->content(function (User $user) {
                    $teams = $user->teams->pluck('name')->toArray();

                    return \count($teams) > 3
                        ? [trans_choice('users.teams_count', \count($teams))]
                        : $teams;
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'secondary', 'size' => 'sm']);
        }

        $columns[] = Column::make(__('table.users.last_login_at'), 'last_login_at')
            ->sortable()
            ->format(fn ($value) => $value ? $value->diffForHumans() : '—');

        return $columns;
    }

    /**
     * Get filter definitions
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        $filters = [];

        if ($this->canFilterByTenant()) {
            $filters[] = $this->tenantFilter();
        }

        return [
            ...$filters,
            Filter::make('role', __('table.users.filters.role'))
                ->placeholder(__('table.users.filters.all_roles'))
                ->type(DataTableFilterType::SELECT)
                ->relationship('roles', 'name')
                ->options($this->getRoleOptions()), // Use memoized options

            Filter::make('is_active', __('table.users.filters.status'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type(DataTableFilterType::SELECT)
                ->options([
                    '1' => __('table.users.status_active'),
                    '0' => __('table.users.status_inactive'),
                ])
                ->valueMapping(['1' => true, '0' => false]),

            Filter::make('email_verified_at', __('table.users.filters.verified'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type(DataTableFilterType::SELECT)
                ->options([
                    '1' => __('table.users.verified_yes'),
                    '0' => __('table.users.verified_no'),
                ])
                ->valueMapping(['1' => 'not_null', '0' => 'null']),

            Filter::make('created_at', __('table.users.filters.created_at'))
                ->type(DataTableFilterType::DATE_RANGE)
                ->execute(function (Builder $query, $value) {
                    $from = $value['from'] ?? null;
                    $to = $value['to'] ?? null;

                    if ($from) {
                        $query->whereDate('users.created_at', '>=', $from);
                    }
                    if ($to) {
                        $query->whereDate('users.created_at', '<=', $to);
                    }
                }),
        ];
    }

    /**
     * Get role options (memoized).
     *
     * @return array<string, string>
     */
    protected function getRoleOptions(): array
    {
        return $this->memoize(
            'filter:roles',
            fn () => $this->roleQuery()->pluck('name', 'name')->toArray(),
        );
    }

    protected function userQuery(): Builder
    {
        return $this->usesTenantConnection()
            ? CentralUser::query()
            : User::query();
    }

    protected function roleQuery(): Builder
    {
        return $this->usesTenantConnection()
            ? Role::on('central')
            : Role::query();
    }

    protected function usesTenantConnection(): bool
    {
        return \function_exists('tenancy') && tenancy()->initialized;
    }

    protected function tenantAudience(): TenantAudience
    {
        $audience = TenantAudience::visibleTo($this->currentActor());
        $tenantFilter = $this->selectedTenantFilter();

        if ($tenantFilter === null || ! $this->canFilterByTenant()) {
            return $audience;
        }

        return $this->tenantMembershipQuery()->audienceFromFilter($audience, $tenantFilter);
    }

    protected function tenantFilter(): Filter
    {
        return Filter::make('tenant_id', __('table.users.filters.tenant'))
            ->placeholder(__('table.users.filters.all_tenants'))
            ->type(DataTableFilterType::SELECT)
            ->options($this->tenantFilterOptions())
            ->execute(function (): void {
                // Tenant membership filtering is applied in baseQuery() so it can
                // replace the default all-tenant-members audience instead of
                // being intersected with it by the generic datatable filter pass.
            });
    }

    /**
     * @return array<string, string>
     */
    protected function tenantFilterOptions(): array
    {
        return $this->memoize(
            'filter:tenants',
            fn () => $this->tenantMembershipQuery()->tenantFilterOptions(),
        );
    }

    protected function canFilterByTenant(): bool
    {
        return $this->currentActor()?->hasRole(Roles::SUPER_ADMIN) ?? false;
    }

    protected function currentActor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    protected function selectedTenantFilter(): ?string
    {
        $tenantFilter = $this->filters['tenant_id'] ?? null;

        return \is_string($tenantFilter) && $tenantFilter !== '' ? $tenantFilter : null;
    }

    protected function tenantMembershipQuery(): TenantMembershipQuery
    {
        return app(TenantMembershipQuery::class);
    }

    /**
     * Get row action definitions
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        // Only add view action if route exists
        if (Route::has('users.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (User $user) => route('users.show', $user->uuid))
                ->variant('ghost')
                ->color('info')
                ->can(PolicyAbilities::VIEW);
        }

        if (Route::has('users.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (User $user) => route('users.edit', $user->uuid))
                ->variant('ghost')
                ->color('primary')
                ->can(PolicyAbilities::UPDATE);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (User $user) {
                $user->delete();
                NotificationBuilder::make()
                    ->title('actions.deleted_successfully', ['user' => $user->label()])
                    ->success()
                    ->send();
            })
            ->can(PolicyAbilities::DELETE);

        return $actions;
    }

    /**
     * Get bulk action definitions
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('activate', __('actions.activate_selected'))
                ->icon('check')
                ->variant('ghost')
                ->color('success')
                ->execute(fn ($users) => $users->each->update(['is_active' => true]))
                ->can(PolicyAbilities::UPDATE),

            BulkAction::make('deactivate', __('actions.deactivate_selected'))
                ->icon('x-mark')
                ->variant('ghost')
                ->color('warning')
                ->execute(fn ($users) => $users->each->update(['is_active' => false]))
                ->can(PolicyAbilities::UPDATE),

            BulkAction::make('delete', __('actions.delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(fn ($users) => $users->each->delete())
                ->can(PolicyAbilities::DELETE),
        ];
    }

    /**
     * Handle row click
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('users.show')) {
            return Action::make()
                ->route('users.show', $uuid)
                ->can(PolicyAbilities::VIEW);
        }

        return null;
    }
}
