<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

/**
 * Central entry point for user impersonation so authorization and tenant checks
 * cannot be bypassed by calling Livewire helpers directly.
 */
class UserImpersonationService
{
    /**
     * Perform impersonation after validating the actor may impersonate the target
     * in the given tenant context (central when tenant is null).
     *
     * @return array{type: 'tenant', url: string}|array{type: 'central'}
     */
    public function execute(User $actor, User $target, ?Tenant $targetTenant): array
    {
        $this->assertMayImpersonate($actor, $target, $targetTenant);

        if ($targetTenant !== null) {
            $token = tenancy()->impersonate($targetTenant, $target->id, '/dashboard', 'web');

            $domain = $targetTenant->domains()->first();
            if ($domain === null) {
                throw new AuthorizationException();
            }

            $protocol = request()->secure() ? 'https://' : 'http://';
            $host = $domain->domain;

            // Handle non-standard ports in development
            $port = request()->getPort();
            if ($port && ! \in_array($port, [80, 443], true)) {
                $host .= ':' . $port;
            }

            $url = "{$protocol}{$host}/impersonate/{$token->token}";

            return ['type' => 'tenant', 'url' => $url];
        }

        Auth::login($target);
        request()->session()->put('impersonator_id', $actor->id);

        return ['type' => 'central'];
    }

    /**
     * Ensure the actor may impersonate the target in the given context.
     *
     * @throws AuthorizationException
     */
    public function assertMayImpersonate(User $actor, User $target, ?Tenant $targetTenant): void
    {
        // For tenant switching (self-impersonation across domains),
        // we only require the actor to have access to the target tenant.
        if ($actor->id === $target->id && $targetTenant !== null) {
            if ($actor->hasRole(Roles::SUPER_ADMIN)) {
                return;
            }

            if ($actor->tenants()->whereKey($targetTenant->getKey())->exists()) {
                return;
            }

            throw new AuthorizationException();
        }

        if (! $actor->can(Permissions::IMPERSONATE_USERS())) {
            throw new AuthorizationException();
        }

        if ($actor->id === $target->id && $targetTenant === null) {
            throw new AuthorizationException();
        }

        if ($target->hasRole(Roles::SUPER_ADMIN) && $targetTenant === null) {
            throw new AuthorizationException();
        }

        if ($targetTenant === null) {
            if ($target->tenants()->exists()) {
                throw new AuthorizationException();
            }

            return;
        }

        if (! $target->tenants()->whereKey($targetTenant->getKey())->exists()) {
            throw new AuthorizationException();
        }

        if (! $actor->hasRole(Roles::SUPER_ADMIN) && ! $actor->tenants()->whereKey($targetTenant->getKey())->exists()) {
            throw new AuthorizationException();
        }
    }
}
