<?php

namespace App\Models\Base;

use App\Models\Concerns\HasUuid;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * Base user model class for all authenticatable models.
 *
 * This model includes the HasUuid trait to ensure all user models
 * automatically generate unique UUIDs when created.
 *
 * This model also includes the SoftDeletes trait to ensure all user models
 * support soft deletion by default.
 *
 * All new authenticatable models should extend this class instead of
 * Illuminate\Foundation\Auth\User directly.
 *
 * @see \App\Models\Base\BaseModel For regular models
 */
abstract class BaseUserModel extends Authenticatable
{
    use HasFactory;
    use HasUuid;
    use Notifiable;
    use SoftDeletes;

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Prevent deletion of SuperAdmin user (ID 1)
        static::deleting(function (BaseUserModel $user) {
            if ($user->id === 1) {
                throw new Exception('Cannot delete SuperAdmin user with ID 1');
            }
        });

        // Handle MySQL trigger for user ID 1 updates
        // The trigger requires @laravel_user_id_1_self_edit to be set to 1
        static::updating(function (BaseUserModel $user) {
            if ($user->id === 1) {
                // Skip protection in testing environment or non-MySQL databases
                if (isTesting() || DB::getDriverName() !== 'mysql') {
                    return;
                }

                // Check if MySQL variable is already set (e.g., by updateLastLoginAt or other system updates)
                // If not set, check if current authenticated user is user ID 1
                $variableSet = DB::selectOne('SELECT @laravel_user_id_1_self_edit as value')?->value;

                if ($variableSet !== 1) {
                    // Variable not set, check if user is updating themselves
                    $currentUser = \Illuminate\Support\Facades\Auth::user();

                    if ($currentUser && $currentUser->id === 1) {
                        // User ID 1 is updating themselves - allow it
                        DB::statement('SET @laravel_user_id_1_self_edit = 1');
                    } else {
                        throw new Exception('Cannot edit user ID 1 - only user ID 1 can edit themselves');
                    }
                }
            }
        });

        // Clear the session variable after update (MySQL only)
        static::updated(function (BaseUserModel $user) {
            if ($user->id === 1 && DB::getDriverName() === 'mysql') {
                DB::statement('SET @laravel_user_id_1_self_edit = NULL');
            }
        });
    }

    /**
     * Check if the user is active
     */
    public function isActive(): bool
    {
        return isset($this->is_active) && $this->is_active === true;
    }

    /**
     * Activate the user
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the user
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Update the last login timestamp
     *
     * This is a system update that should always be allowed.
     * Subclasses can override this to add additional logic (e.g., MySQL trigger handling).
     */
    public function updateLastLoginAt(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
