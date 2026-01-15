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
 * Set team ID in session for team-based access control.
 *
 * This helper centralizes team session setting logic used after successful authentication.
 * It sets the first team as the active team for the session.
 */
function setTeamSessionForUser(User $user): void
{
    $firstTeam = $user->teams()->orderBy('teams.id')->first();
    if ($firstTeam) {
        session(['team_id' => $firstTeam->id]);
    }
}
