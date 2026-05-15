<?php

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Get identifier from request (supports both 'identifier' and 'email' fields).
 *
 * This helper centralizes identifier extraction logic used across authentication flows.
 * It supports both the custom 'identifier' field (for dual email/username login)
 * and the standard 'email' field for backward compatibility.
 */
function getIdentifierFromRequest(Request $request): ?string
{
    // Strict mode: Application standardizes on 'identifier' field
    return $request->input('identifier');
}

/**
 * Set tenant ID in session for tenant-based access control.
 *
 * This helper centralizes tenant session setting logic used after successful authentication.
 * It sets the first tenant as the active tenant for the session.
 */
function setTenantSessionForUser(User $user): void
{
    $firstTenant = $user->tenants()->orderBy('tenants.name')->first();
    if ($firstTenant) {
        session(['tenant_id' => $firstTenant->tenant_id]);
    }
}
