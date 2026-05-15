<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamRole extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'display_name',
        'description',
        'color',
        'is_admin',
        'is_default',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(TeamPermission::class, 'team_permission_team_role', 'team_role_id', 'team_permission_id')
            ->withPivot('uuid');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_role_id', 'user_id')
            ->withPivot('uuid', 'team_id', 'role')
            ->withTimestamps();
    }

    public function label(): string
    {
        return $this->display_name ?? $this->name;
    }
}
