<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TeamScope;

/**
 * Trait for models that need team-based access control.
 *
 * When applied to a model, it automatically adds a global scope that filters
 * queries to only show records from the current user's teams.
 *
 * The "super team" (Default Team, configurable) bypasses this scope and sees all data.
 */
trait HasTeamAccess
{
    /**
     * Boot the trait and add the team scope.
     */
    public static function bootHasTeamAccess(): void
    {
        static::addGlobalScope(new TeamScope);
    }

    /**
     * Query without the team scope.
     *
     * Use this when you need to access all records regardless of team.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutTeamScope($query)
    {
        return $query->withoutGlobalScope(TeamScope::class);
    }

    /**
     * Query for specific team(s).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|array<int>  $teamIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeams($query, int|array $teamIds)
    {
        $teamIds = (array) $teamIds;

        return $query->withoutGlobalScope(TeamScope::class)
            ->whereIn($this->getTable() . '.team_id', $teamIds);
    }
}
