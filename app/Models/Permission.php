<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission model for RBAC.
 *
 * Permissions are assigned to roles and checked via Gates/Policies.
 */
class Permission extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get a human-readable label for this permission.
     */
    public function label(): string
    {
        return $this->display_name ?? $this->name;
    }
}
