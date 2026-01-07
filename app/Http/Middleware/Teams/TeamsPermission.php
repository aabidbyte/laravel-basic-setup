<?php

namespace App\Http\Middleware\Teams;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to set the active team context for the request.
 *
 * This middleware ensures the team_id is available in the session
 * for team-based access control throughout the request lifecycle.
 */
class TeamsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If no team_id in session, set from user's first team
            if (! session()->has('team_id')) {
                $firstTeam = $user->teams()->orderBy('teams.id')->first();
                if ($firstTeam) {
                    session(['team_id' => $firstTeam->id]);
                }
            }
        }

        return $next($request);
    }
}
