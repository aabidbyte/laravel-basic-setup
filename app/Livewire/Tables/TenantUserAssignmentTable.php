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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;

class TenantUserAssignmentTable extends UserTable
{
    #[Locked]
    public string $tenantId = '';

    public function mount(string $tenantId = ''): void
    {
        $this->tenantId = $tenantId;

        $this->authorize(PolicyAbilities::VIEW_ANY, User::class);
    }

    public function baseQuery(): Builder
    {
        return User::query()
            ->with(['roles'])
            ->withExists([
                'tenants as assigned_to_tenant' => fn (Builder $query) => $query->where('tenants.tenant_id', $this->tenantId),
            ])
            ->select('users.*');
    }

    /**
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
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

            Column::make(__('table.users.assignment'), 'assigned_to_tenant')
                ->format(fn ($value) => $value
                    ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('tenancy.assigned'), ['color' => 'success', 'size' => 'sm'])
                    : DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('tenancy.not_assigned'), ['color' => 'ghost', 'size' => 'sm']))
                ->html(),
        ];
    }

    /**
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('assignment', __('table.users.filters.assignment'))
                ->placeholder(__('table.users.filters.all_assignments'))
                ->type(DataTableFilterType::SELECT)
                ->options([
                    'assigned' => __('tenancy.assigned'),
                    'unassigned' => __('tenancy.not_assigned'),
                ])
                ->execute(function (Builder $query, string $value): void {
                    if ($value === 'assigned') {
                        $query->whereHas('tenants', fn (Builder $tenantQuery) => $tenantQuery->where('tenants.tenant_id', $this->tenantId));
                    }

                    if ($value === 'unassigned') {
                        $query->whereDoesntHave('tenants', fn (Builder $tenantQuery) => $tenantQuery->where('tenants.tenant_id', $this->tenantId));
                    }
                }),

            Filter::make('role', __('table.users.filters.role'))
                ->placeholder(__('table.users.filters.all_roles'))
                ->type(DataTableFilterType::SELECT)
                ->relationship('roles', 'name')
                ->options($this->getRoleOptions()),

            Filter::make('is_active', __('table.users.filters.status'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type(DataTableFilterType::SELECT)
                ->options([
                    '1' => __('table.users.status_active'),
                    '0' => __('table.users.status_inactive'),
                ])
                ->valueMapping(['1' => true, '0' => false]),
        ];
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
        return [
            Action::make('select', __('actions.select'))
                ->icon('check')
                ->variant('ghost')
                ->color('primary')
                ->execute(fn (User $user) => $this->toggleAssignment($user))
                ->can(Permissions::EDIT_TENANTS(), false),
        ];
    }

    /**
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('assign', __('tenancy.assign_selected_users'))
                ->icon('user-plus')
                ->variant('ghost')
                ->color('success')
                ->execute(fn (Collection $users) => $this->assignUsers($users))
                ->can(Permissions::EDIT_TENANTS()),

            BulkAction::make('detach', __('tenancy.detach_selected_users'))
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
        return Action::make()
            ->execute(fn (User $user) => $this->toggleAssignment($user))
            ->can(Permissions::EDIT_TENANTS(), false);
    }

    protected function toggleAssignment(User $user): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $tenant->users()->toggle([$user->id]);

        NotificationBuilder::make()
            ->title('tenancy.user_assignment_updated')
            ->success()
            ->send();
    }

    protected function assignUsers(Collection $users): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $tenant->users()->syncWithoutDetaching($users->pluck('id')->all());

        NotificationBuilder::make()
            ->title('tenancy.users_assigned_successfully')
            ->success()
            ->send();
    }

    protected function detachUsers(Collection $users): void
    {
        $tenant = $this->tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $tenant->users()->detach($users->pluck('id')->all());

        NotificationBuilder::make()
            ->title('tenancy.users_detached_successfully')
            ->success()
            ->send();
    }

    protected function tenant(): ?Tenant
    {
        return Tenant::where('tenant_id', $this->tenantId)->first();
    }
}
