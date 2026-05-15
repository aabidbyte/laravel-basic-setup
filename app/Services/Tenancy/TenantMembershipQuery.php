<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Constants\Auth\Roles;
use App\Enums\Tenancy\TenantAudienceMode;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Applies reusable tenant-membership visibility rules to central model queries.
 *
 * This service is intentionally model-agnostic. Any central model with a
 * `tenants()` relationship can opt into the same workflow used by user
 * datatables: all tenant members, one tenant, central-only records, or all
 * records, intersected with the actor's tenant access unless they are a super
 * admin.
 */
class TenantMembershipQuery
{
    public const CENTRAL_RECORDS_FILTER = '__central_records';

    public function apply(Builder $query, TenantAudience $audience, string $tenantRelation = 'tenants'): Builder
    {
        $this->applyAudienceMode($query, $audience, $tenantRelation);
        $this->applyActorVisibility($query, $audience, $tenantRelation);

        return $query;
    }

    public function applyToTenantKey(Builder $query, TenantAudience $audience, string $tenantColumn = 'tenant_id'): Builder
    {
        $this->applyTenantKeyAudienceMode($query, $audience, $tenantColumn);
        $this->applyTenantKeyActorVisibility($query, $audience, $tenantColumn);

        return $query;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function forModel(string $modelClass, TenantAudience $audience, string $tenantRelation = 'tenants'): Builder
    {
        if (! \is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("{$modelClass} must be an Eloquent model class.");
        }

        return $this->apply($modelClass::query(), $audience, $tenantRelation);
    }

    /**
     * @return array<string, string>
     */
    public function tenantFilterOptions(bool $includeCentralRecords = true): array
    {
        $options = Tenant::query()
            ->orderBy('name')
            ->pluck('name', 'tenant_id')
            ->toArray();

        if (! $includeCentralRecords) {
            return $options;
        }

        return [self::CENTRAL_RECORDS_FILTER => __('tenancy.central_users')] + $options;
    }

    /**
     * @return array<int, string>
     */
    public function tenantLabelsFor(Model $model, string $tenantRelation = 'tenants'): array
    {
        if ($model instanceof User && $model->isProtectedCentralAccount()) {
            return [__('tenancy.central_users')];
        }

        $tenantNames = $model->{$tenantRelation}
            ->pluck('name')
            ->filter()
            ->values()
            ->toArray();

        if (! empty($tenantNames)) {
            return $tenantNames;
        }

        return [__('tenancy.central_users')];
    }

    public function audienceFromFilter(TenantAudience $audience, string $filterValue): TenantAudience
    {
        if ($filterValue === self::CENTRAL_RECORDS_FILTER) {
            return $audience->forCentralRecords();
        }

        return $audience->forSelectedTenant($filterValue);
    }

    private function applyAudienceMode(Builder $query, TenantAudience $audience, string $tenantRelation): void
    {
        match ($audience->mode) {
            TenantAudienceMode::AllTenantMembers => $this->applyAllTenantMembersScope($query, $tenantRelation),
            TenantAudienceMode::AllRecords => null,
            TenantAudienceMode::SpecificTenant => $this->applyTenantScope($query, $tenantRelation, $audience->tenantId),
            TenantAudienceMode::CentralOnly => $this->applyCentralOnlyScope($query, $tenantRelation),
        };
    }

    private function applyAllTenantMembersScope(Builder $query, string $tenantRelation): void
    {
        $query->whereHas($tenantRelation);
        $this->excludeProtectedCentralAccount($query);
    }

    private function applyTenantScope(Builder $query, string $tenantRelation, ?string $tenantId): void
    {
        if ($tenantId === null || $tenantId === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas(
            $tenantRelation,
            fn (Builder $tenantQuery): Builder => $tenantQuery->where('tenants.tenant_id', $tenantId),
        );
        $this->excludeProtectedCentralAccount($query);
    }

    private function applyCentralOnlyScope(Builder $query, string $tenantRelation): void
    {
        if (! $this->queryTargetsUsers($query)) {
            $query->whereDoesntHave($tenantRelation);

            return;
        }

        $query->where(function (Builder $centralQuery) use ($tenantRelation): void {
            $centralQuery
                ->whereDoesntHave($tenantRelation)
                ->orWhere($centralQuery->getModel()->getQualifiedKeyName(), User::PROTECTED_CENTRAL_ACCOUNT_ID);
        });
    }

    private function applyActorVisibility(Builder $query, TenantAudience $audience, string $tenantRelation): void
    {
        $actor = $audience->actor;

        if (! $actor instanceof User) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($actor->hasRole(Roles::SUPER_ADMIN)) {
            return;
        }

        if ($audience->mode === TenantAudienceMode::CentralOnly) {
            $query->whereRaw('1 = 0');

            return;
        }

        $tenantIds = $actor->tenants()->pluck('tenants.tenant_id')->all();

        if (empty($tenantIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas(
            $tenantRelation,
            fn (Builder $tenantQuery): Builder => $tenantQuery->whereIn('tenants.tenant_id', $tenantIds),
        );
    }

    private function excludeProtectedCentralAccount(Builder $query): void
    {
        if (! $this->queryTargetsUsers($query)) {
            return;
        }

        $query->whereKeyNot(User::PROTECTED_CENTRAL_ACCOUNT_ID);
    }

    private function queryTargetsUsers(Builder $query): bool
    {
        return $query->getModel() instanceof User;
    }

    private function applyTenantKeyAudienceMode(Builder $query, TenantAudience $audience, string $tenantColumn): void
    {
        match ($audience->mode) {
            TenantAudienceMode::AllTenantMembers => $query->whereNotNull($tenantColumn),
            TenantAudienceMode::AllRecords => null,
            TenantAudienceMode::SpecificTenant => $this->applyTenantKeyScope($query, $tenantColumn, $audience->tenantId),
            TenantAudienceMode::CentralOnly => $query->whereNull($tenantColumn),
        };
    }

    private function applyTenantKeyScope(Builder $query, string $tenantColumn, ?string $tenantId): void
    {
        if ($tenantId === null || $tenantId === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where($tenantColumn, $tenantId);
    }

    private function applyTenantKeyActorVisibility(Builder $query, TenantAudience $audience, string $tenantColumn): void
    {
        $actor = $audience->actor;

        if (! $actor instanceof User) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($actor->hasRole(Roles::SUPER_ADMIN)) {
            return;
        }

        if ($audience->mode === TenantAudienceMode::CentralOnly) {
            $query->whereRaw('1 = 0');

            return;
        }

        $tenantIds = $actor->tenants()->pluck('tenants.tenant_id')->all();

        if (empty($tenantIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($tenantColumn, $tenantIds);
    }
}
