<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableFilterType;
use App\Livewire\DataTable\Datatable;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantMembershipQuery;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
        $query = User::query()
            ->with(['roles', 'tenants'])
            ->select(['users.*']);

        return $this->tenantMembershipQuery()->apply(
            $query,
            TenantAudience::forTenant((string) $this->tenantId, $this->currentActor()),
        );
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
     * Get filter definitions.
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [
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

            Filter::make('email_verified_at', __('table.users.filters.verified'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type(DataTableFilterType::SELECT)
                ->options([
                    '1' => __('table.users.verified_yes'),
                    '0' => __('table.users.verified_no'),
                ])
                ->valueMapping(['1' => 'not_null', '0' => 'null']),
        ];
    }

    /**
     * Get role options.
     *
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
                    $tenant = Tenant::where('tenant_id', $this->tenantId)->firstOrFail();
                    $this->authorize(PolicyAbilities::UPDATE, $tenant);
                    $tenant->users()->detach($user->id);
                    NotificationBuilder::make()
                        ->title('tenancy.user_detached_successfully')
                        ->success()
                        ->send();
                })
                ->show(fn () => Auth::user()?->can(Permissions::EDIT_TENANTS()) ?? false),
        ];
    }

    /**
     * Handle row click.
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

    protected function currentActor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    protected function tenantMembershipQuery(): TenantMembershipQuery
    {
        return app(TenantMembershipQuery::class);
    }
}
