<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivotModel;
use App\Models\Concerns\HasUuid;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for the permission_user relationship.
 *
 * Represents direct permission assignments to users (not through roles).
 * This pivot table has id and uuid columns.
 */
class PermissionUser extends BasePivotModel
{
    use HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permission_user';

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
    public $timestamps = false;

    /**
     * Get the permission that this pivot belongs to.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the user that this pivot belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
