<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Database\ConnectionType;
use App\Events\Database\TenantCreated;
use App\Models\Base\BaseLandlordModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Tenant extends BaseLandlordModel
{
    protected $fillable = [
        'name',
        'db_name',
        'master_id',
        'created_by_user_uuid',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (auth()->check()) {
                $user = auth()->user();
                if (! $tenant->created_by_user_uuid) {
                    $tenant->created_by_user_uuid = $user->uuid;
                }
            }
        });

        static::created(function (Tenant $tenant) {
            DB::afterCommit(function () use ($tenant) {
                $creatorConnection = null;
                if (auth()->check()) {
                    $creatorConnection = config('database.default');
                }

                event(new TenantCreated(
                    tenant: $tenant,
                    creatorUserUuid: $tenant->created_by_user_uuid,
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
     * Get the master this tenant belongs to.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class, 'master_id', 'id');
    }

    /**
     * Resolve the user who created this tenant.
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
     * Get domains associated with this instance.
     */
    public function domains(): MorphMany
    {
        return $this->morphMany(Domain::class, 'tenant');
    }
}
