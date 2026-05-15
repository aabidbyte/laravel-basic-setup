<?php

declare(strict_types=1);

namespace App\Policies;

use App\Constants\Auth\Permissions;
use App\Enums\Feature\FeatureKey;
use App\Models\ErrorLog;
use App\Models\User;
use App\Services\Features\FeatureResolver;

/**
 * Policy for ErrorLog model authorization.
 *
 * Controls access to error log management features.
 */
class ErrorLogPolicy
{
    /**
     * Perform pre-authorization checks (Super Admin bypass).
     *
     * Returning true grants all abilities. Returning null falls through to the specific method.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super Admin (ID 1) can do anything
        if ($user->id === 1) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any error logs (list).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_ERROR_LOGS())
            || $this->tenantHasErrorLogsFeature();
    }

    /**
     * Determine if the user can view a specific error log.
     */
    public function view(User $user, ErrorLog $errorLog): bool
    {
        return ($user->hasPermissionTo(Permissions::VIEW_ERROR_LOGS()) || $this->tenantHasErrorLogsFeature())
            && $this->belongsToCurrentTenant($errorLog);
    }

    /**
     * Determine if the user can resolve an error log.
     */
    public function resolve(User $user, ErrorLog $errorLog): bool
    {
        return $user->hasPermissionTo(Permissions::RESOLVE_ERROR_LOGS())
            && $this->belongsToCurrentTenant($errorLog);
    }

    /**
     * Determine if the user can delete an error log.
     */
    public function delete(User $user, ErrorLog $errorLog): bool
    {
        return $user->hasPermissionTo(Permissions::DELETE_ERROR_LOGS())
            && $this->belongsToCurrentTenant($errorLog);
    }

    protected function belongsToCurrentTenant(ErrorLog $errorLog): bool
    {
        if (tenant() === null) {
            return true;
        }

        return $errorLog->tenant_id === null || $errorLog->tenant_id === tenant()->getTenantKey();
    }

    protected function tenantHasErrorLogsFeature(): bool
    {
        $tenant = tenant();

        if ($tenant === null) {
            return false;
        }

        return app(FeatureResolver::class)->allows($tenant, FeatureKey::ERROR_LOGS);
    }
}
