<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * MailSettings model for custom SMTP configurations.
 */
class MailSettings extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'settable_id',
        'settable_type',
        'provider',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
        'last_used_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the owning settable model (User, Team, or Tenant/App).
     */
    public function settable_entity(): MorphTo
    {
        return $this->morphTo('settable');
    }

    /**
     * Scope a query to only include active settings.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to settings for a specific user.
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('settable_type', User::class)
            ->where('settable_id', $user->id);
    }

    /**
     * Scope a query to settings for a specific team.
     */
    public function scopeForTeam(Builder $query, Team $team): void
    {
        $query->where('settable_type', Team::class)
            ->where('settable_id', $team->id);
    }

    /**
     * Scope a query to app-level settings.
     */
    public function scopeForApp(Builder $query): void
    {
        $query->where('settable_type', 'app')
            ->whereNull('settable_id');
    }

    /**
     * Get the first active settings for a user.
     */
    public static function getForUser(User $user): ?self
    {
        try {
            return static::query()
                ->forUser($user)
                ->active()
                ->first();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the first active settings for a team.
     */
    public static function getForTeam(Team $team): ?self
    {
        try {
            return static::query()
                ->forTeam($team)
                ->active()
                ->first();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the first active app-level settings.
     */
    public static function getForApp(): ?self
    {
        try {
            return static::query()
                ->forApp()
                ->active()
                ->first();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if settings have valid SMTP configuration.
     */
    public function isValid(): bool
    {
        return ! empty($this->host) && ! empty($this->username) && ! empty($this->password);
    }

    /**
     * Update the last used timestamp.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }

    /**
     * Get a human-readable label for this mail settings.
     */
    public function label(): string
    {
        return $this->provider . ' (' . ($this->from_address ?? 'no address') . ')';
    }
}
