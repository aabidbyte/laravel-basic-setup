<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableFilterType;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class TenantUserAssignmentTable extends UserTable
{
    private const ASSIGNMENTS_UPDATED_EVENT = 'tenant-user-assignments-updated';

    #[Locked]
    public string $tenantId = '';

    public ?string $queryStringAlias = 'assigned_users';

    protected string $datatableIdentifier = 'assigned-users';

    public function mount(string $tenantId = ''): void
    {
        $this->tenantId = $tenantId;

        $this->authorize(PolicyAbilities::VIEW_ANY, User::class);
    }

    public function baseQuery(): Builder
    {
        $relations = ['roles', 'tenants'];

        if (Schema::hasTable('teams')) {
            $relations[] = 'teams';
        }

        $query = User::query()
            ->with($relations)
            ->select('users.*');

        $this->tenantMembershipQuery()->apply($query, $this->tenantAudience());
        $this->applyAssignmentScope($query);

        return $query;
    }

    /**
     * @return array<int, Column>
     */
    public function columns(): array
    {
        $columns = [
            Column::make(__('table.users.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
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
        ];

        if (Schema::hasTable('teams')) {
            $columns[] = Column::make(__('table.users.teams'), 'teams_for_datatable')
                ->content(function (User $user) {
                    $teams = $user->teams->pluck('name')->toArray();

                    return \count($teams) > 3
                        ? [trans_choice('users.teams_count', \count($teams))]
                        : $teams;
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'secondary', 'size' => 'sm']);
        }

        return $columns;
    }

    /**
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        $filters = [];

        if ($this->canFilterByTenant()) {
            $filters[] = $this->tenantFilter();
        }

        $filters[] = Filter::make('role', __('table.users.filters.role'))
            ->placeholder(__('table.users.filters.all_roles'))
            ->type(DataTableFilterType::SELECT)
            ->relationship('roles', 'name')
            ->options($this->getRoleOptions());

        $filters[] = Filter::make('is_active', __('table.users.filters.status'))
            ->placeholder(__('table.users.filters.all_status'))
            ->type(DataTableFilterType::SELECT)
            ->options([
                '1' => __('table.users.status_active'),
                '0' => __('table.users.status_inactive'),
            ])
            ->valueMapping(['1' => true, '0' => false]);

        return $filters;
    }

    /**
     * @return array<string, string>
     */
    protected function getRoleOptions(): array
    {
        return $this->memoize(
            'filter:roles',
            fn () => Role::pluck('name', 'name')->toArray(),
        );
    }

    /**
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        return [];
    }

    protected function rowClickAction(): Action
    {
        return
            Action::make('remove', __('tenancy.detach_user'))
                ->icon('user-minus')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('tenancy.detach_user_confirm'))
                ->execute(fn (User $user) => $this->detachUser($user))
                ->can(Permissions::EDIT_TENANTS(), false);
    }

    /**
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('remove', __('tenancy.detach_selected_users'))
                ->icon('user-minus')
                ->variant('ghost')
                ->color('warning')
                ->confirm(__('tenancy.detach_users_confirm'))
                ->execute(fn (Collection $users) => $this->detachUsers($users))
                ->can(Permissions::EDIT_TENANTS()),
        ];
    }

    public function rowClick(string $uuid): ?Action
    {
        return $this->rowClickAction();
    }

    #[On('tenant-user-assignments-updated.{tenantId}')]
    public function refreshTenantUserAssignments(): void
    {
        $this->refreshTable();
    }

    protected function assignUser(User $user): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $this->authorize(PolicyAbilities::UPDATE, $tenant);
        $tenant->users()->syncWithoutDetaching([$user->id]);
        $this->dispatchTenantUserAssignmentsUpdated();

        NotificationBuilder::make()
            ->title('tenancy.user_assigned_successfully')
            ->success()
            ->send();
    }

    protected function assignUsers(Collection $users): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $this->authorize(PolicyAbilities::UPDATE, $tenant);
        $tenant->users()->syncWithoutDetaching($users->pluck('id')->all());
        $this->dispatchTenantUserAssignmentsUpdated();

        NotificationBuilder::make()
            ->title('tenancy.users_assigned_successfully')
            ->success()
            ->send();
    }

    protected function detachUser(User $user): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $this->authorize(PolicyAbilities::UPDATE, $tenant);
        $tenant->users()->detach($user->id);
        $this->dispatchTenantUserAssignmentsUpdated();

        NotificationBuilder::make()
            ->title('tenancy.user_detached_successfully')
            ->success()
            ->send();
    }

    protected function detachUsers(Collection $users): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $this->authorize(PolicyAbilities::UPDATE, $tenant);
        $tenant->users()->detach($users->pluck('id')->all());
        $this->dispatchTenantUserAssignmentsUpdated();

        NotificationBuilder::make()
            ->title('tenancy.users_detached_successfully')
            ->success()
            ->send();
    }

    protected function tenant(): ?Tenant
    {
        return Tenant::where('tenant_id', $this->tenantId)->first();
    }

    protected function tenantAudience(): TenantAudience
    {
        $audience = TenantAudience::forTenant($this->tenantId, $this->currentActor());
        $tenantFilter = $this->selectedTenantFilter();

        if ($tenantFilter === null || ! $this->canFilterByTenant()) {
            return $audience;
        }

        return $this->tenantMembershipQuery()
            ->audienceFromFilter(TenantAudience::visibleTo($this->currentActor()), $tenantFilter);
    }

    protected function applyAssignmentScope(Builder $query): void
    {
        $query->whereHas('tenants', fn (Builder $tenantQuery) => $tenantQuery->where('tenants.tenant_id', $this->tenantId));
    }

    protected function dispatchTenantUserAssignmentsUpdated(): void
    {
        if ($this->tenantId === '') {
            return;
        }

        $this->dispatch(self::ASSIGNMENTS_UPDATED_EVENT . ".{$this->tenantId}");
    }
}
