<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivotModel;
use App\Models\Concerns\HasUuid;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for the permission_role relationship.
 *
 * Represents permission assignments to roles.
 * This pivot table has id and uuid columns.
 */
class PermissionRole extends BasePivotModel
{
    use HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permission_role';

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
     * Get the role that this pivot belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
