<?php

use App\Constants\Auth\Permissions;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Livewire\Volt\Volt;

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
    Volt::test('pages::plans.edit')
        ->set('name', 'New Test Plan')
        ->set('tier', 'pro')
        ->set('price', 29.99)
        ->set('currency', 'USD')
        ->set('billing_cycle', 'monthly')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('plans.index'));

    expect(Plan::where('name', 'New Test Plan')->exists())->toBeTrue();
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

    Volt::test('pages::plans.edit', ['plan' => $plan])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($plan->fresh()->name)->toBe('Updated Name');
});

it('can delete a plan from the table', function () {
    $plan = Plan::factory()->create();

    Livewire::test('tables.plan-table')
        ->call('executeAction', 'delete', $plan->uuid);

    expect(Plan::find($plan->id))->toBeNull();
});
