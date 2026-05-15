<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Locked;

/**
 * Inline DataTable for displaying users with a specific role.
 * Used on the Role show page.
 */
class RoleUserTable extends UserTable
{
    /**
     * The role UUID to filter users by.
     */
    #[Locked]
    public string $roleUuid = '';

    /**
     * The role instance (resolved from UUID).
     */
    protected ?Role $role = null;

    /**
     * Mount the component with the role UUID.
     */
    public function mount(string $roleUuid = ''): void
    {
        $this->roleUuid = $roleUuid;
    }

    /**
     * Get the role instance.
     */
    protected function getRole(): ?Role
    {
        if ($this->role === null && $this->roleUuid) {
            $this->role = Role::where('uuid', $this->roleUuid)->first();
        }

        return $this->role;
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        $role = $this->getRole();

        if (! $role) {
            return User::query()->whereRaw('1 = 0');
        }

        $query = User::query()
            ->with(['tenants'])
            ->whereHas('roles', fn (Builder $q) => $q->where('roles.id', $role->id))
            ->select('users.*');

        return $this->tenantMembershipQuery()->apply($query, $this->tenantAudience());
    }

    /**
     * Get column definitions.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.users.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.users.email'), 'email')
                ->sortable()
                ->searchable()
                ->class('text-base-content/70'),

            Column::make(__('table.users.tenants'), 'tenants_for_datatable')
                ->content(fn (User $user) => $this->tenantMembershipQuery()->tenantLabelsFor($user))
                ->type(DataTableUi::UI_BADGE, ['color' => 'accent', 'size' => 'sm']),
        ];
    }

    /**
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        if (! $this->canFilterByTenant()) {
            return [];
        }

        return [$this->tenantFilter()];
    }

    /**
     * Get row action definitions.
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        if (Route::has('users.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (User $user) => route('users.show', $user->uuid))
                ->variant('ghost')
                ->can(Permissions::VIEW_USERS(), false);
        }

        $actions[] = Action::make('detach', __('actions.detach'))
            ->icon('x-mark')
            ->variant('ghost')
            ->color('warning')
            ->confirm(__('actions.confirm_detach_user'))
            ->execute(function (User $user) {
                $role = $this->getRole();
                if ($role) {
                    $user->removeRole($role);
                    NotificationBuilder::make()
                        ->title('actions.detached_successfully', ['user' => $user->name, 'role' => $role->label()])
                        ->success()
                        ->send();
                }
            })
            ->can(Permissions::EDIT_ROLES(), false);

        return $actions;
    }

    /**
     * Handle row click - navigate to user show page.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('users.show')) {
            return Action::make()
                ->route('users.show', $uuid)
                ->can(Permissions::VIEW_USERS(), false);
        }

        return null;
    }
}
