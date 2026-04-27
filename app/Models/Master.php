<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Database\ConnectionType;
use App\Models\Base\BaseLandlordModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Master extends BaseLandlordModel
{
    protected $fillable = [
        'name',
        'db_name',
        'created_by_user_uuid',
    ];

    protected static function booted(): void
    {
        static::creating(function (Master $master) {
            if (auth()->check()) {
                $user = auth()->user();
                if (! $master->created_by_user_uuid) {
                    $master->created_by_user_uuid = $user->uuid;
                }
            }
        });

        static::created(function (Master $master) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($master) {
                $creatorConnection = null;
                if (auth()->check()) {
                    $creatorConnection = config('database.default');
                }

                event(new \App\Events\Database\MasterCreated(
                    master: $master,
                    creatorUserUuid: $master->created_by_user_uuid,
                    creatorConnection: $creatorConnection,
                ));
            });
        });
    }

    public function label(): string
    {
        return $this->name;
    }

    /**
     * Get tenants belonging to this master.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'master_id', 'id');
    }

    /**
     * Resolve the user who created this master.
     * Since users exist in independent databases, we look them up by UUID.
     */
    public function creator(): ?User
    {
        if (! $this->created_by_user_uuid) {
            return null;
        }

        // Try local (Landlord) first if we are on landlord connection
        if (config('database.default') === ConnectionType::LANDLORD->connectionName()) {
            return User::where('uuid', $this->created_by_user_uuid)->first();
        }

        // Lookups from cross-DB need a specific connection which is handled by the Listener/Service
        return null;
    }

    /**
     * Get domains associated with this master.
     */
    public function domains(): MorphMany
    {
        return $this->morphMany(Domain::class, 'tenant');
    }
}
