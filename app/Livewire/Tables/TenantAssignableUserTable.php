<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenantAssignableUserTable extends TenantUserAssignmentTable
{
    public ?string $queryStringAlias = 'available_users';

    protected string $datatableIdentifier = 'available-users';

    protected function applyAssignmentScope(Builder $query): void
    {
        $query->whereDoesntHave('tenants', fn (Builder $tenantQuery) => $tenantQuery->where('tenants.tenant_id', $this->tenantId));
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
            Action::make('assign', __('tenancy.assign_user'))
                ->icon('user-plus')
                ->variant('ghost')
                ->color('success')
                ->execute(fn (User $user) => $this->assignUser($user))
                ->can(Permissions::EDIT_TENANTS(), false);
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
        ];
    }
}
