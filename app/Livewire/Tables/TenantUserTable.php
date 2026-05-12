<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\DataTable\Datatable;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class TenantUserTable extends Datatable
{
    #[Locked]
    public ?string $tenantId = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(?string $tenantId = null): void
    {
        $this->tenantId = $tenantId;
        $this->authorize(PolicyAbilities::VIEW_ANY, User::class);
    }

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        return User::query()
            ->with(['roles'])
            ->whereHas('tenants', function ($query) {
                $query->where('tenants.id', $this->tenantId);
            })
            ->select(['users.*']);
    }

    /**
     * Define the columns for the table.
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

            Column::make(__('table.users.roles'), 'roles_for_datatable')
                ->content(function (User $user) {
                    return $user->roles->map(fn (Role $role) => $role->display_name ?? $role->name)->toArray();
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'primary', 'size' => 'sm']),

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
                ->variant('ghost')
                ->route(fn (User $user) => route('users.show', $user->uuid))
                ->can(PolicyAbilities::VIEW),

            Action::make('detach', __('tenancy.detach_user'))
                ->icon('user-minus')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('tenancy.detach_user_confirm'))
                ->execute(function (User $user) {
                    $tenant = Tenant::find($this->tenantId);
                    if ($tenant) {
                        $tenant->users()->detach($user->id);
                        NotificationBuilder::make()
                            ->title('tenancy.user_detached_successfully')
                            ->success()
                            ->send();
                    }
                })
                ->show(fn () => auth()->user()->hasPermissionTo(Permissions::EDIT_TENANTS())),
        ];
    }
}
