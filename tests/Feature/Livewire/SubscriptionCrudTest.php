<?php

use App\Constants\Auth\Permissions;
use App\Enums\Plan\PlanBillingCycle;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignPermission(Permissions::VIEW_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::CREATE_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::EDIT_SUBSCRIPTIONS());
    $this->admin->assignPermission(Permissions::DELETE_SUBSCRIPTIONS());

    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin);
});

it('can render the subscriptions index page', function () {
    $this->get(route('subscriptions.index'))
        ->assertOk()
        ->assertSee(__('subscriptions.index_title'));
});

it('can render the create subscription page', function () {
    $this->get(route('subscriptions.create'))
        ->assertOk()
        ->assertSee(__('subscriptions.create_title'));
});

it('validates required fields when creating a subscription', function () {
    Livewire::test('pages::subscriptions.edit')
        ->set('tenant_id', null)
        ->set('plan_id', null)
        ->set('status', '')
        ->call('create')
        ->assertHasErrors(['tenant_id', 'plan_id', 'status']);
});

it('can create a subscription', function () {
    Livewire::test('pages::subscriptions.edit')
        ->set('tenant_id', $this->tenant->tenant_id)
        ->set('plan_id', $this->plan->id)
        ->set('status', SubscriptionStatus::ACTIVE->value)
        ->set('note', 'Manual admin assignment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('subscriptions.index'));

    $subscription = Subscription::query()
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('plan_id', $this->plan->id)
        ->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(SubscriptionStatus::ACTIVE)
        ->and($subscription->extras)->toBe(['note' => 'Manual admin assignment']);
});

it('sets subscription end date from billing cycle when no end date is provided', function () {
    $monthlyPlan = Plan::factory()->create([
        'is_active' => true,
        'billing_cycle' => PlanBillingCycle::MONTHLY,
    ]);

    Livewire::test('pages::subscriptions.edit')
        ->set('tenant_id', $this->tenant->tenant_id)
        ->set('plan_id', $monthlyPlan->id)
        ->set('status', SubscriptionStatus::ACTIVE->value)
        ->set('starts_at', '2026-05-15T10:00')
        ->set('ends_at', null)
        ->call('create')
        ->assertHasNoErrors();

    $subscription = Subscription::query()
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('plan_id', $monthlyPlan->id)
        ->first();

    expect($subscription?->ends_at?->format('Y-m-d H:i'))->toBe('2026-06-15 10:00');
});

it('keeps one time subscriptions non expiring when no end date is provided', function () {
    $oneTimePlan = Plan::factory()->create([
        'is_active' => true,
        'billing_cycle' => PlanBillingCycle::ONE_TIME,
    ]);

    Livewire::test('pages::subscriptions.edit')
        ->set('tenant_id', $this->tenant->tenant_id)
        ->set('plan_id', $oneTimePlan->id)
        ->set('status', SubscriptionStatus::ACTIVE->value)
        ->set('starts_at', '2026-05-15T10:00')
        ->set('ends_at', null)
        ->call('create')
        ->assertHasNoErrors();

    $subscription = Subscription::query()
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('plan_id', $oneTimePlan->id)
        ->first();

    expect($subscription?->ends_at)->toBeNull();
});

it('can render the edit subscription page', function () {
    $subscription = Subscription::factory()->create([
        'tenant_id' => $this->tenant->tenant_id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::TRIAL,
    ]);

    $this->get(route('subscriptions.edit', $subscription))
        ->assertOk()
        ->assertSee(__('subscriptions.edit_title'));
});

it('can update a subscription', function () {
    $subscription = Subscription::factory()->create([
        'tenant_id' => $this->tenant->tenant_id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::TRIAL,
    ]);

    Livewire::test('pages::subscriptions.edit', ['subscription' => $subscription])
        ->set('status', SubscriptionStatus::CANCELED->value)
        ->set('note', 'Canceled by admin')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('subscriptions.index'));

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::CANCELED)
        ->and($subscription->fresh()->extras)->toBe(['note' => 'Canceled by admin']);
});

it('can delete a subscription from the table', function () {
    $subscription = Subscription::factory()->create([
        'tenant_id' => $this->tenant->tenant_id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::test('tables.subscription-table')
        ->call('executeAction', 'delete', $subscription->uuid);

    expect(Subscription::query()->whereKey($subscription->id)->exists())->toBeFalse();
});
