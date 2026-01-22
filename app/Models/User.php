<?php

namespace App\Models;

use App\Models\Base\BaseUserModel;
use App\Models\Concerns\HasDataTable;
use App\Models\Concerns\HasRolesAndPermissions;
use App\Models\Pivots\TeamUser;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use RuntimeException;

class User extends BaseUserModel implements MustVerifyEmail
{
    use HasDataTable;
    use HasRolesAndPermissions;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'pending_email',
        'pending_email_token',
        'pending_email_expires_at',
        'password',
        'created_by_user_id',
        'is_active',
        'last_login_at',
        'frontend_preferences',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'pending_email_expires_at' => 'datetime',
            'password' => 'hashed',
            'frontend_preferences' => 'array',
            'notification_preferences' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get a human-readable label for this user.
     *
     * Used for notifications and UI display to provide context about the model.
     */
    public function label(): string
    {
        return $this->name;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->using(TeamUser::class)
            ->withTimestamps();
    }

    /**
     * Get the user who created this user.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'created_by_user_id');
    }

    /**
     * Get the mail settings for this user.
     */
    public function mailSettings(): MorphMany
    {
        return $this->morphMany(MailSettings::class, 'settable');
    }

    /**
     * Check if user has custom mail settings configured.
     */
    public function hasMailSettings(): bool
    {
        return $this->mailSettings()->active()->exists();
    }

    /**
     * Check if user has a pending email change.
     */
    public function hasPendingEmailChange(): bool
    {
        return ! empty($this->pending_email) && ! $this->isPendingEmailExpired();
    }

    /**
     * Check if the pending email has expired.
     */
    public function isPendingEmailExpired(): bool
    {
        if (empty($this->pending_email_expires_at)) {
            return true;
        }

        return $this->pending_email_expires_at->isPast();
    }

    /**
     * Cancel the pending email change.
     */
    public function cancelPendingEmailChange(): bool
    {
        return $this->update([
            'pending_email' => null,
            'pending_email_token' => null,
            'pending_email_expires_at' => null,
        ]);
    }

    /**
     * Confirm the pending email change (called after user verifies).
     */
    public function confirmPendingEmail(): bool
    {
        if (! $this->hasPendingEmailChange()) {
            return false;
        }

        $newEmail = $this->pending_email;

        return $this->update([
            'email' => $newEmail,
            'email_verified_at' => now(),
            'pending_email' => null,
            'pending_email_token' => null,
            'pending_email_expires_at' => null,
        ]);
    }

    /**
     * Check if user has a verified email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! empty($this->email) && ! empty($this->email_verified_at);
    }

    /**
     * Get the entity's notifications.
     * Override to use our custom Notification model with resolving accessors.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * Get the user's timezone from frontend_preferences.
     */
    public function getTimezoneAttribute(): ?string
    {
        return $this->frontend_preferences['timezone'] ?? null;
    }

    /**
     * Set the user's timezone in frontend_preferences.
     */
    public function setTimezoneAttribute(?string $value): void
    {
        $preferences = $this->frontend_preferences ?? [];
        $preferences['timezone'] = $value;
        $this->frontend_preferences = $preferences;
    }

    /**
     * Get the user's locale from frontend_preferences.
     */
    public function getLocaleAttribute(): ?string
    {
        return $this->frontend_preferences['locale'] ?? null;
    }

    /**
     * Set the user's locale in frontend_preferences.
     */
    public function setLocaleAttribute(?string $value): void
    {
        $preferences = $this->frontend_preferences ?? [];
        $preferences['locale'] = $value;
        $this->frontend_preferences = $preferences;
    }

    /**
     * Get roles for datatable display
     *
     * @return array<int, array<string, string>>
     */
    public function getRolesForDatatableAttribute(): array
    {
        return $this->roles->map(fn ($role) => [
            'label' => $role->name,
            'color' => 'primary',
        ])->toArray();
    }

    /**
     * Get teams for datatable display
     *
     * @return array<int, array<string, string>>
     */
    public function getTeamsForDatatableAttribute(): array
    {
        return $this->teams->map(fn ($team) => [
            'label' => $team->name,
            'color' => 'secondary',
        ])->toArray();
    }

    /**
     * Find user by identifier (email or username).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function findByIdentifier(string $identifier)
    {
        return static::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier);
    }

    /**
     * Get the email address for password reset.
     *
     * This method is used by Laravel's password reset system to identify the user
     * and send the password reset notification. Password reset notifications require
     * an email address, so users without email cannot use the standard password
     * reset flow.
     *
     * For token storage, we use 'identifier' (email or username) to support users
     * with optional email addresses, but the notification system requires an email.
     */
    public function getEmailForPasswordReset(): string
    {
        if (empty($this->email)) {
            throw new RuntimeException('User must have an email address to reset password via email notification.');
        }

        return $this->email;
    }

    /**
     * Update the last login timestamp
     *
     * Overrides the base method to handle MySQL trigger for user ID 1.
     * This is a system update that should always be allowed, even for user ID 1.
     */
    public function updateLastLoginAt(): bool
    {
        // Handle MySQL trigger for user ID 1 (system updates like last_login_at should always be allowed)
        if ($this->id === 1 && DB::getDriverName() === 'mysql' && ! isTesting()) {
            DB::statement('SET @laravel_user_id_1_self_edit = 1');
        }

        $result = parent::updateLastLoginAt();

        // Clear the session variable after update (MySQL only)
        if ($this->id === 1 && DB::getDriverName() === 'mysql' && ! isTesting()) {
            DB::statement('SET @laravel_user_id_1_self_edit = NULL');
        }

        return $result;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
