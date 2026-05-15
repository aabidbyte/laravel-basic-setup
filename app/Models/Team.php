<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\Pivots\TeamUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Team extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'tenant_id',
        'description',
        'color',
        'created_by_user_id',
    ];

    /**
     * Get the user who created this team.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->using(TeamUser::class)
            ->withPivot('role', 'team_role_id')
            ->withTimestamps();
    }

    /**
     * Get the team roles scoped to this team context.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(TeamRole::class, 'team_user', 'team_id', 'team_role_id')
            ->using(TeamUser::class)
            ->withTimestamps();
    }

    /**
     * Get the mail settings for this team.
     */
    public function mailSettings(): MorphMany
    {
        return $this->morphMany(MailSettings::class, 'settable');
    }

    /**
     * Check if team has custom mail settings configured.
     */
    public function hasMailSettings(): bool
    {
        return $this->mailSettings()->active()->exists();
    }

    /**
     * Get a human-readable label for this team.
     */
    public function label(): string
    {
        return $this->name;
    }
}
