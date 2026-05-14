<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\DataTable\Datatable;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Tenancy\UserImpersonationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ImpersonateUserTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = 'Select User to Impersonate';

    /**
     * Whether to show the search bar.
     */
    public bool $showSearch = true;

    /**
     * Whether to show the header.
     */
    public bool $showHeader = true;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        $admin = Auth::user();

        $query = User::query()
            ->with(['tenants'])
            ->select('users.*');

        if (! $admin instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        // If we are in a tenant context, only show users belonging to this tenant
        // UNLESS the user is a super admin who might want to find users from other tenants
        if (tenant() && ! $admin->hasRole(Roles::SUPER_ADMIN)) {
            $query->whereHas('tenants', function ($q) {
                $q->where('tenants.id', tenant('id'));
            });
        }

        // Don't show self in the datatable
        $query->where('id', '!=', $admin->id);

        // Don't allow impersonating other Super Admins
        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', Roles::SUPER_ADMIN);
        });

        return $query;
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.users.name'), 'name')
                ->searchable()
                ->sortable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.users.email'), 'email')
                ->searchable()
                ->sortable(),

            Column::make(__('tenancy.tenants'), 'tenants_count')
                ->content(function (User $user) {
                    return $user->tenants->pluck('name')->toArray();
                })
                ->type('badge', ['color' => 'secondary', 'size' => 'sm']),
        ];
    }

    /**
     * Define the filters for the table.
     */
    protected function getFilterDefinitions(): array
    {
        $filters = [];
        $admin = Auth::user();

        // Only show tenant filter if we are in central context OR if we are a super admin
        if (! tenant() || ($admin instanceof User && $admin->hasRole(Roles::SUPER_ADMIN))) {
            $filters[] = Filter::make('tenant_id', __('tenancy.tenant'))
                ->type('select')
                ->options(Tenant::pluck('name', 'id')->toArray())
                ->execute(fn ($q, $value) => $q->whereHas('tenants', fn ($inner) => $inner->where('tenants.id', $value)));
        }

        $filters[] = Filter::make('role', __('roles.role'))
            ->type('select')
            ->options(Role::where('name', '!=', Roles::SUPER_ADMIN)->pluck('name', 'id')->toArray())
            ->execute(fn ($q, $value) => $q->whereHas('roles', fn ($inner) => $inner->where('roles.id', $value)));

        return $filters;
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        return Action::make('impersonate')
            ->execute(fn () => $this->initiateImpersonation($uuid));
    }

    /**
     * Initiate the impersonation flow.
     */
    public function initiateImpersonation(string $uuid): void
    {
        $admin = Auth::user();
        if (! $admin || ! $admin->can(Permissions::IMPERSONATE_USERS())) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('tenancy.permission_denied')]);

            return;
        }

        $user = User::where('uuid', $uuid)->firstOrFail();

        // If user is already the admin, ignore
        if ($user->id === $admin->id) {
            $this->dispatch('notify', ['type' => 'info', 'message' => __('tenancy.cannot_impersonate_self')]);

            return;
        }

        $tenants = $user->tenants;

        if (tenant()) {
            // We are already in a tenant, impersonate directly into this tenant
            $this->performImpersonation($user, tenant());

            return;
        }

        // Central context
        if ($tenants->count() === 0) {
            // Impersonate centrally
            $this->performImpersonation($user);
        } elseif ($tenants->count() === 1) {
            // Impersonate into their only tenant
            $this->performImpersonation($user, $tenants->first());
        } else {
            // Option A: Prompt for tenant
            $this->dispatch('prompt-tenant-selection', [
                'user_uuid' => $user->uuid,
                'tenants' => $tenants->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->toArray(),
            ]);
        }
    }

    /**
     * Perform the actual impersonation and redirect.
     */
    protected function performImpersonation(User $user, ?Tenant $targetTenant = null): void
    {
        $actor = Auth::user();
        if (! $actor instanceof User) {
            return;
        }

        try {
            $result = app(UserImpersonationService::class)->execute($actor, $user, $targetTenant);
        } catch (AuthorizationException) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('tenancy.permission_denied')]);

            return;
        }

        if ($result['type'] === 'tenant') {
            $this->redirect($result['url']);

            return;
        }

        $this->redirect('/dashboard');
    }
}
