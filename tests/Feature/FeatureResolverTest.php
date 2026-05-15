<?php

use App\Enums\Feature\FeatureKey;
use App\Enums\Feature\FeatureValueType;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use App\Services\Features\FeatureResolver;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->resolver = app(FeatureResolver::class);

    $this->makeTenant = function (string $name): Tenant {
        return Tenant::withoutEvents(function () use ($name): Tenant {
            $tenant = Tenant::factory()->create([
                'tenant_id' => (string) Str::uuid(),
                'name' => $name,
                'plan' => null,
            ]);

            $tenant->setInternal('db_name', $this->testing_databases()->reusableTenantDatabaseName());
            $tenant->save();

            return $tenant;
        });
    };
});

it('resolves feature values from the active subscription plan', function () {
    $tenant = ($this->makeTenant)('Acme');
    $feature = featureForResolver(FeatureKey::MAX_USERS, FeatureValueType::INTEGER);
    $plan = subscribedPlanForResolver($tenant);

    PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => 10,
    ]);

    expect($this->resolver->value($tenant, FeatureKey::MAX_USERS))->toBe(10)
        ->and($this->resolver->allows($tenant, FeatureKey::MAX_USERS))->toBeTrue();
});

it('prefers active tenant overrides over plan defaults', function () {
    $tenant = ($this->makeTenant)('Acme');
    $feature = featureForResolver(FeatureKey::MAX_USERS, FeatureValueType::INTEGER);
    $plan = subscribedPlanForResolver($tenant);

    PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => 5,
    ]);

    TenantFeatureOverride::factory()->create([
        'tenant_id' => $tenant->tenant_id,
        'feature_id' => $feature->id,
        'value' => 25,
        'enabled' => true,
    ]);

    expect($this->resolver->value($tenant, FeatureKey::MAX_USERS))->toBe(25);
});

it('allows a tenant override to deny a plan feature', function () {
    $tenant = ($this->makeTenant)('Acme');
    $feature = featureForResolver(FeatureKey::API_ACCESS, FeatureValueType::BOOLEAN);
    $plan = subscribedPlanForResolver($tenant);

    PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => true,
        'enabled' => true,
    ]);

    TenantFeatureOverride::factory()->create([
        'tenant_id' => $tenant->tenant_id,
        'feature_id' => $feature->id,
        'value' => null,
        'enabled' => false,
    ]);

    expect($this->resolver->value($tenant, FeatureKey::API_ACCESS))->toBeFalse()
        ->and($this->resolver->allows($tenant, FeatureKey::API_ACCESS))->toBeFalse();
});

it('ignores expired tenant overrides', function () {
    $tenant = ($this->makeTenant)('Acme');
    $feature = featureForResolver(FeatureKey::API_ACCESS, FeatureValueType::BOOLEAN);
    $plan = subscribedPlanForResolver($tenant);

    PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => true,
        'enabled' => true,
    ]);

    TenantFeatureOverride::factory()->create([
        'tenant_id' => $tenant->tenant_id,
        'feature_id' => $feature->id,
        'value' => false,
        'enabled' => false,
        'starts_at' => now()->subDays(3),
        'ends_at' => now()->subDay(),
    ]);

    expect($this->resolver->allows($tenant, FeatureKey::API_ACCESS))->toBeTrue();
});

it('keeps tenant feature overrides isolated by tenant', function () {
    $tenantA = ($this->makeTenant)('Acme');
    $tenantB = ($this->makeTenant)('Globex');
    $feature = featureForResolver(FeatureKey::MAX_USERS, FeatureValueType::INTEGER);
    $plan = Plan::factory()->create(['is_active' => true]);

    subscribedPlanForResolver($tenantA, $plan);
    subscribedPlanForResolver($tenantB, $plan);

    PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => 5,
    ]);

    TenantFeatureOverride::factory()->create([
        'tenant_id' => $tenantA->tenant_id,
        'feature_id' => $feature->id,
        'value' => 99,
        'enabled' => true,
    ]);

    expect($this->resolver->value($tenantA, FeatureKey::MAX_USERS))->toBe(99)
        ->and($this->resolver->value($tenantB, FeatureKey::MAX_USERS))->toBe(5);
});

it('seeds central plan feature rows from existing plan feature arrays', function () {
    $basicPlan = Plan::query()
        ->where('name->en_US', 'Basic')
        ->with('planFeatures.feature')
        ->firstOrFail();

    $enterprisePlan = Plan::query()
        ->where('name->en_US', 'Enterprise')
        ->with('planFeatures.feature')
        ->firstOrFail();

    expect($basicPlan->features)->toContain(['key' => 'max_users', 'value' => '5'])
        ->and($basicPlan->planFeatures->pluck('feature.key')->all())->toContain('max_users', 'storage')
        ->and($basicPlan->planFeatures->firstWhere('feature.key', 'max_users')->value)->toBe(5)
        ->and($enterprisePlan->planFeatures->firstWhere('feature.key', 'max_users')->value)->toBe('Unlimited');
});

function featureForResolver(FeatureKey $key, FeatureValueType $type): Feature
{
    return Feature::query()->updateOrCreate([
        'key' => $key->value,
    ], [
        'name' => $key->nameTranslations(),
        'type' => $type,
        'default_value' => $key->defaultValue(),
        'is_active' => true,
    ]);
}

function subscribedPlanForResolver(Tenant $tenant, ?Plan $plan = null): Plan
{
    $plan ??= Plan::factory()->create(['is_active' => true]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->tenant_id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addMonth(),
    ]);

    return $plan;
}
