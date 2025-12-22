<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Base\BaseUserModel;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUserModel
{
    use HasRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'team_id',
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
            'password' => 'hashed',
            'frontend_preferences' => 'array',
        ];
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
    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withTimestamps();
    }

    /**
     * Get the user's primary team.
     */
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
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
     *
     * @return string
     */
    public function getEmailForPasswordReset(): string
    {
        if (empty($this->email)) {
            throw new \RuntimeException('User must have an email address to reset password via email notification.');
        }

        return $this->email;
    }
}
