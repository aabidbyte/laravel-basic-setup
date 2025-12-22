<?php

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
    return $request->input('identifier') ?? $request->input('email');
}

/**
 * Set team ID in session for TeamsPermission middleware.
 *
 * This helper centralizes team session setting logic used after successful authentication.
 * It ensures the team_id is available for Spatie Permission's team-based authorization.
 */
function setTeamSessionForUser(\App\Models\User $user): void
{
    if ($user->team_id) {
        session(['team_id' => $user->team_id]);
    }
}
