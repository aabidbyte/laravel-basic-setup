<?php

use App\Constants\Auth\Permissions;
use App\Enums\Feature\FeatureKey;
use App\Enums\Feature\FeatureValueType;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignPermission(Permissions::VIEW_TENANTS());
    $this->admin->assignPermission(Permissions::VIEW_PLANS());
    $this->admin->assignPermission(Permissions::VIEW_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::CREATE_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::EDIT_SUBSCRIPTIONS());

    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin);
});

it('can render the tenant subscriptions page', function () {
    $this->get(route('tenants.subscriptions', $this->tenant))
        ->assertOk()
        ->assertSee($this->tenant->name);
});

it('can subscribe a tenant to a plan', function () {
    Volt::test('pages::tenants.subscriptions', ['tenant' => $this->tenant])
        ->set('selectedPlanId', $this->plan->id)
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    $subscription = Subscription::where('tenant_id', $this->tenant->tenant_id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->plan_id)->toBe($this->plan->id)
        ->and($subscription->status)->toBe(SubscriptionStatus::ACTIVE);
});

it('deactivates old subscriptions when subscribing to a new one', function () {
    $oldPlan = Plan::factory()->create();
    Subscription::factory()->create([
        'tenant_id' => $this->tenant->tenant_id,
        'plan_id' => $oldPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
    ]);

    Volt::test('pages::tenants.subscriptions', ['tenant' => $this->tenant])
        ->set('selectedPlanId', $this->plan->id)
        ->call('subscribe');

    expect(Subscription::where('tenant_id', $this->tenant->tenant_id)
        ->where('plan_id', $oldPlan->id)
        ->first()->status)->toBe(SubscriptionStatus::CANCELED);
});

it('can save a custom feature override for a tenant', function () {
    $feature = Feature::query()->updateOrCreate([
        'key' => FeatureKey::API_ACCESS->value,
    ], [
        'name' => FeatureKey::API_ACCESS->nameTranslations(),
        'type' => FeatureValueType::BOOLEAN,
        'default_value' => false,
        'is_active' => true,
    ]);

    Volt::test('pages::tenants.subscriptions', ['tenant' => $this->tenant])
        ->set('selectedFeatureId', $feature->id)
        ->set('overrideValue', 'true')
        ->set('overrideEnabled', true)
        ->set('overrideReason', 'Enterprise trial')
        ->call('saveFeatureOverride')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    $override = TenantFeatureOverride::query()
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('feature_id', $feature->id)
        ->first();

    expect($override)->not->toBeNull()
        ->and($override->value)->toBe('true')
        ->and($override->enabled)->toBeTrue()
        ->and($override->reason)->toBe('Enterprise trial');
});

it('can remove a tenant feature override', function () {
    $feature = Feature::factory()->create([
        'type' => FeatureValueType::BOOLEAN,
    ]);

    $override = TenantFeatureOverride::factory()->create([
        'tenant_id' => $this->tenant->tenant_id,
        'feature_id' => $feature->id,
        'value' => true,
        'enabled' => true,
    ]);

    Volt::test('pages::tenants.subscriptions', ['tenant' => $this->tenant])
        ->call('deleteFeatureOverride', $override->uuid)
        ->assertDispatched('notify');

    expect(TenantFeatureOverride::query()->whereKey($override->id)->exists())->toBeFalse();
});
