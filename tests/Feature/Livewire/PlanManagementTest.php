<?php

use App\Constants\Auth\Permissions;
use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = User::factory()->create();

    // Use assignPermission as defined in HasRolesAndPermissions trait
    $this->admin->assignPermission(Permissions::VIEW_PLANS());
    $this->admin->assignPermission(Permissions::CREATE_PLANS());
    $this->admin->assignPermission(Permissions::EDIT_PLANS());
    $this->admin->assignPermission(Permissions::DELETE_PLANS());

    $this->actingAs($this->admin);
});

it('can render the plans index page', function () {
    $this->get(route('plans.index'))
        ->assertOk()
        ->assertSee(__('plans.title'));
});

it('can render the create plan page', function () {
    $this->get(route('plans.create'))
        ->assertOk();
});

it('can create a new plan', function () {
    Livewire::test('pages::plans.edit')
        ->set('name', 'New Test Plan')
        ->set('tier', 'pro')
        ->set('price', 29.99)
        ->set('currency', 'USD')
        ->set('billing_cycle', 'monthly')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('plans.index'));

    $createdPlan = Plan::query()->latest('id')->first();

    expect($createdPlan)->not->toBeNull();
    expect($createdPlan?->name)->toBe('New Test Plan');
});

it('can assign catalog features to a plan', function () {
    $feature = Feature::factory()->create([
        'key' => 'advanced_exports',
        'type' => FeatureValueType::BOOLEAN,
    ]);

    Livewire::test('pages::plans.edit')
        ->set('name', 'Feature Plan')
        ->set('tier', 'pro')
        ->set('price', 49.99)
        ->set('currency', 'USD')
        ->set('billing_cycle', 'monthly')
        ->set('features', [
            ['feature_id' => $feature->id, 'value' => 'true', 'enabled' => true],
        ])
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('plans.index'));

    $plan = Plan::query()->where('name->en_US', 'Feature Plan')->first();

    $planFeature = PlanFeature::query()
        ->where('plan_id', $plan->id)
        ->where('feature_id', $feature->id)
        ->first();

    expect($plan)->not->toBeNull()
        ->and($planFeature)->not->toBeNull()
        ->and($planFeature?->value)->toBeTrue();
});

it('can render the edit plan page', function () {
    $plan = Plan::factory()->create();

    $this->get(route('plans.edit', $plan))
        ->assertOk()
        ->assertSee($plan->name)
        ->assertSee(__('plans.edit_title'));
});

it('can update an existing plan', function () {
    $plan = Plan::factory()->create(['name' => 'Old Name']);

    Livewire::test('pages::plans.edit', ['plan' => $plan])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($plan->fresh()->name)->toBe('Updated Name');
});

it('can assign a boolean feature to an existing plan from the available feature table', function () {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create([
        'key' => 'boolean_plan_feature',
        'type' => FeatureValueType::BOOLEAN,
    ]);

    Livewire::test('tables.plan-assignable-feature-table', ['planUuid' => $plan->uuid])
        ->call('executeAction', 'assign', $feature->uuid);

    $planFeature = PlanFeature::query()
        ->where('plan_id', $plan->id)
        ->where('feature_id', $feature->id)
        ->first();

    expect($planFeature)->not->toBeNull()
        ->and($planFeature?->value)->toBeTrue()
        ->and($plan->fresh()->features)->toContain([
            'key' => 'boolean_plan_feature',
            'value' => true,
        ]);
});

it('can configure a custom feature value for an existing plan', function () {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create([
        'key' => 'max_projects',
        'type' => FeatureValueType::INTEGER,
    ]);

    Livewire::test('plan-features.value-modal', [
        'planUuid' => $plan->uuid,
        'featureUuid' => $feature->uuid,
    ])
        ->set('value', '25')
        ->set('enabled', true)
        ->call('save')
        ->assertHasNoErrors();

    $planFeature = PlanFeature::query()
        ->where('plan_id', $plan->id)
        ->where('feature_id', $feature->id)
        ->first();

    expect($planFeature)->not->toBeNull()
        ->and($planFeature?->value)->toBe(25)
        ->and($plan->fresh()->features)->toContain([
            'key' => 'max_projects',
            'value' => 25,
        ]);
});

it('can update and remove an assigned plan feature', function () {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create([
        'key' => 'storage_limit',
        'type' => FeatureValueType::INTEGER,
    ]);
    $planFeature = PlanFeature::factory()->create([
        'plan_id' => $plan->id,
        'feature_id' => $feature->id,
        'value' => 10,
        'enabled' => true,
    ]);

    Livewire::test('plan-features.value-modal', ['planFeatureUuid' => $planFeature->uuid])
        ->set('value', '50')
        ->set('enabled', false)
        ->call('save')
        ->assertHasNoErrors();

    $updatedPlanFeature = $planFeature->fresh();

    expect($updatedPlanFeature->value)->toBe(50)
        ->and($updatedPlanFeature->enabled)->toBeFalse()
        ->and($plan->fresh()->features)->toContain([
            'key' => 'storage_limit',
            'value' => false,
        ]);

    Livewire::test('tables.plan-feature-assignment-table', ['planUuid' => $plan->uuid])
        ->call('executeAction', 'remove', $planFeature->uuid);

    expect(PlanFeature::query()->whereKey($planFeature->id)->exists())->toBeFalse()
        ->and($plan->fresh()->features)->toBe([]);
});

it('can delete a plan from the table', function () {
    $plan = Plan::factory()->create();

    Livewire::test('tables.plan-table')
        ->call('executeAction', 'delete', $plan->uuid);

    expect(Plan::find($plan->id))->toBeNull();
});
