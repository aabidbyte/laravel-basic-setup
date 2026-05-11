<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Subscription;
use App\Constants\Auth\Permissions;
use App\Enums\Subscription\SubscriptionStatus;
use Livewire\Volt\Volt;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignPermission(Permissions::VIEW_TENANTS());
    $this->admin->assignPermission(Permissions::VIEW_PLANS());
    $this->admin->assignPermission(Permissions::VIEW_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::CREATE_SUBSCRIPTIONS());
    
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

    $subscription = Subscription::where('tenant_id', $this->tenant->id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->plan_id)->toBe($this->plan->id)
        ->and($subscription->status)->toBe(SubscriptionStatus::ACTIVE);
});

it('deactivates old subscriptions when subscribing to a new one', function () {
    $oldPlan = Plan::factory()->create();
    Subscription::factory()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $oldPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
    ]);

    Volt::test('pages::tenants.subscriptions', ['tenant' => $this->tenant])
        ->set('selectedPlanId', $this->plan->id)
        ->call('subscribe');

    expect(Subscription::where('tenant_id', $this->tenant->id)
        ->where('plan_id', $oldPlan->id)
        ->first()->status)->toBe(SubscriptionStatus::CANCELED);
});
