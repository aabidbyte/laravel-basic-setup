<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Features\UserImpersonation;

class TenantService
{
    /**
     * Create a new tenant.
     *
     * @param  array  $data  Tenant data (id, name, plan, should_seed, etc.)
     * @param  array  $userIds  User IDs to associate with the tenant
     */
    public function createTenant(array $data, array $userIds = []): Tenant
    {
        return DB::transaction(function () use ($data, $userIds) {
            $tenant = Tenant::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'plan' => $data['plan'] ?? null,
                'should_seed' => $data['should_seed'] ?? true,
            ]);

            $tenant->domains()->create([
                'domain' => $data['id'] . '.' . config('tenancy.central_domains.0'),
            ]);

            if (! empty($userIds)) {
                $tenant->users()->sync($userIds);
            }

            return $tenant;
        });
    }

    /**
     * Update an existing tenant.
     */
    public function updateTenant(Tenant $tenant, array $data, array $userIds = []): Tenant
    {
        return DB::transaction(function () use ($tenant, $data, $userIds) {
            $tenant->update([
                'name' => $data['name'],
                'plan' => $data['plan'] ?? $tenant->plan,
            ]);

            // Note: tenant ID change is not supported by standard tenancy package easily
            // as it's the primary key and linked to database names, etc.

            if (! empty($userIds)) {
                $tenant->users()->sync($userIds);
            }

            return $tenant->fresh();
        });
    }

    /**
     * Delete a tenant.
     */
    public function deleteTenant(Tenant $tenant): ?bool
    {
        return DB::transaction(function () use ($tenant) {
            // stancl/tenancy handles domain deletion via model events,
            // but we wrap in a transaction to ensure database consistency.
            return $tenant->delete();
        });
    }

    /**
     * Generate an impersonation URL for a tenant.
     */
    public function impersonateTenant(Tenant $tenant, string $userId, string $redirectUrl = '/dashboard'): string
    {
        $token = UserImpersonation::createToken($tenant, $userId, $redirectUrl);

        return request()->getScheme() . '://' . $tenant->domains()->first()->domain . '/impersonate/' . $token;
    }
}
