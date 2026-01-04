<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Mail settings model for polymorphic mail configuration.
 *
 * Supports User, Team, and App-level mail settings with hierarchical resolution.
 * The credential resolver checks settings in this order:
 * 1. User settings (if user has CONFIGURE_MAIL_SETTINGS permission)
 * 2. Team settings
 * 3. App settings (settable_type = 'app')
 * 4. Environment variables (.env)
 *
 * @property int $id
 * @property string $uuid
 * @property string $settable_type
 * @property int|null $settable_id
 * @property string $provider
 * @property string|null $host
 * @property int|null $port
 * @property string|null $username
 * @property string|null $password
 * @property string|null $encryption
 * @property string|null $from_address
 * @property string|null $from_name
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class MailSettings extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'settable_type',
        'settable_id',
        'provider',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mail_settings';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent owner model (User, Team, or null for App).
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, self>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('settable');
    }

    /**
     * Scope a query to only include active settings.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include settings for a specific user.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('settable_type', User::class)
            ->where('settable_id', $user->id);
    }

    /**
     * Scope a query to only include settings for a specific team.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForTeam(Builder $query, Team $team): Builder
    {
        return $query->where('settable_type', Team::class)
            ->where('settable_id', $team->id);
    }

    /**
     * Scope a query to only include app-level settings.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForApp(Builder $query): Builder
    {
        return $query->where('settable_type', 'app')
            ->whereNull('settable_id');
    }

    /**
     * Get the first active settings for a user.
     */
    public static function getForUser(User $user): ?self
    {
        return static::query()
            ->forUser($user)
            ->active()
            ->first();
    }

    /**
     * Get the first active settings for a team.
     */
    public static function getForTeam(Team $team): ?self
    {
        return static::query()
            ->forTeam($team)
            ->active()
            ->first();
    }

    /**
     * Get the first active app-level settings.
     */
    public static function getForApp(): ?self
    {
        return static::query()
            ->forApp()
            ->active()
            ->first();
    }

    /**
     * Check if settings have valid SMTP configuration.
     */
    public function hasValidSmtpConfig(): bool
    {
        return ! empty($this->host)
            && ! empty($this->port)
            && ! empty($this->from_address);
    }

    /**
     * Convert settings to mailer config array.
     *
     * @return array<string, mixed>
     */
    public function toMailerConfig(): array
    {
        return [
            'transport' => $this->provider,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'encryption' => $this->encryption,
            'from' => [
                'address' => $this->from_address,
                'name' => $this->from_name,
            ],
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
