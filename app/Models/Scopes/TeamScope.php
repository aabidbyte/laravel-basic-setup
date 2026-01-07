<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global scope for team-based data isolation.
 *
 * Filters queries to only return records belonging to the current user's teams.
 * Users in the "super team" (Default Team) bypass this scope and see all data.
 */
class TeamScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // No user authenticated - don't apply scope (handled by auth middleware)
        if (! $user) {
            return;
        }

        // Check if user is in super team (bypasses scope)
        $superTeamId = config('teams.super_team_id', 1);
        if ($user->teams->contains('id', $superTeamId)) {
            return; // Super team members see all data
        }

        // Get user's team IDs
        $teamIds = $user->teams->pluck('id')->toArray();

        if (empty($teamIds)) {
            // User has no teams - show nothing
            $builder->whereRaw('1 = 0');

            return;
        }

        // Filter by user's teams
        $builder->whereIn($model->getTable() . '.team_id', $teamIds);
    }
}
