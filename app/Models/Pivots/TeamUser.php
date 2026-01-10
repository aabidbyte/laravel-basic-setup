<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivotModel;
use App\Models\Concerns\HasUuid;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for the team_user relationship.
 *
 * Represents team memberships for users.
 * This pivot table has additional columns (id, uuid, timestamps).
 */
class TeamUser extends BasePivotModel
{
    use HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the team that this pivot belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user that this pivot belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
