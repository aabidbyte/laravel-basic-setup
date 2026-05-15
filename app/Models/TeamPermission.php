<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamPermission extends BaseModel
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
        'entity',
        'action',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(TeamRole::class, 'team_permission_team_role', 'team_permission_id', 'team_role_id')
            ->withPivot('uuid');
    }

    public function label(): string
    {
        return $this->display_name ?? $this->name;
    }
}
