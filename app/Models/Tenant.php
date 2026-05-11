<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Subscription\SubscriptionStatus;
use App\Enums\Tenancy\TenantPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Tenant model.
 *
 * This model represents a tenant in the multi-tenant architecture.
 * It uses the multi-database tenancy approach where each tenant has its own database.
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $plan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $data
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    use HasFactory;

    /**
     * Get the custom columns for the tenant model.
     *
     * These columns are stored directly in the tenants table instead of the JSON data column.
     *
     * @return array<string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'plan',
            'should_seed',
        ];
    }

    /**
     * Get the users that belong to the tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withTimestamps();
    }

    /**
     * Get the subscriptions for the tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the current active subscription for the tenant.
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'should_seed' => 'boolean',
            'plan' => TenantPlan::class,
        ];
    }
}
