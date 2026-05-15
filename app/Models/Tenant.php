<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Subscription\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
 * @property string|null $tenant_id
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $plan
 * @property string|null $color
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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'central';

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
            'tenant_id',
            'slug',
            'name',
            'plan',
            'color',
            'should_seed',
        ];
    }

    /**
     * Get the users that belong to the tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user', 'tenant_id', 'user_id', 'tenant_id', 'id')
            ->withTimestamps();
    }

    /**
     * Get the domains that belong to the tenant.
     */
    public function domains()
    {
        return $this->hasMany(config('tenancy.domain_model'), 'tenant_id', 'tenant_id');
    }

    /**
     * Get the plan associated with the tenant.
     */
    public function planModel(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan', 'uuid');
    }

    /**
     * Get the internal ID used by stancl/tenancy.
     */
    public function getTenantKey(): string
    {
        return (string) $this->tenant_id;
    }

    /**
     * Get the name of the column used for tenant identification.
     */
    public function getTenantKeyName(): string
    {
        return 'tenant_id';
    }

    /**
     * Get the route key for public/front-facing routes.
     */
    public function getRouteKeyName(): string
    {
        return 'tenant_id';
    }

    /**
     * Get a human-readable label for this model.
     */
    public function label(): string
    {
        return $this->name ?? $this->tenant_id;
    }

    /**
     * Get a stable, readable directory name for tenant-scoped operational files.
     */
    public function logDirectoryName(): string
    {
        $slug = $this->safeDirectoryPart($this->slug ?: $this->name ?: 'tenant');
        $shortId = Str::of($this->tenant_id)->before('-')->limit(8, '')->toString();

        if ($shortId === '') {
            return $slug;
        }

        return "{$slug}__{$shortId}";
    }

    /**
     * Get the subscriptions for the tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get custom feature overrides for the tenant.
     */
    public function featureOverrides(): HasMany
    {
        return $this->hasMany(TenantFeatureOverride::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the current active subscription for the tenant.
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'tenant_id')
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
            'tenant_id' => 'string',
            'should_seed' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            $attributes = $tenant->getAttributes();
            $providedId = $attributes['id'] ?? null;

            if (! empty($providedId) && ! \is_numeric($providedId) && empty($tenant->tenant_id)) {
                $tenant->tenant_id = (string) $providedId;
                $attributes = $tenant->getAttributes();
                unset($attributes['id']);
                $tenant->setRawAttributes($attributes);
            }

            if (empty($tenant->tenant_id)) {
                $tenant->tenant_id = (string) Str::uuid();
            }

            if (! empty($tenant->slug)) {
                $tenant->slug = $tenant->safeDirectoryPart($tenant->slug);

                return;
            }

            $tenant->slug = $tenant->uniqueSlugFromName();
        });
    }

    private function uniqueSlugFromName(): string
    {
        $baseSlug = $this->safeDirectoryPart($this->name ?? 'organization');
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function safeDirectoryPart(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->slug('-')
            ->whenEmpty(fn () => Str::of('tenant'))
            ->toString();
    }
}
