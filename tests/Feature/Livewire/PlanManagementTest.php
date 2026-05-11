<?php

use App\Models\Plan;
use App\Models\User;
use App\Constants\Auth\Permissions;
use Livewire\Volt\Volt;
use Tests\TestCase;

beforeEach(function () {
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
        ->assertSeeLivewire('tables.plan-table');
});

it('can render the create plan page', function () {
    $this->get(route('plans.edit'))
        ->assertOk()
        ->assertSee(__('plans.create_title'));
});

it('can create a new plan', function () {
    Volt::test('pages::plans.edit')
        ->set('name', 'New Test Plan')
        ->set('tier', 'pro')
        ->set('price', 29.99)
        ->set('billing_cycle', 'monthly')
        ->call('save')
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

    Livewire::test('app.livewire.tables.plan-table')
        ->call('deletePlan', $plan->id)
        ->assertDispatched('notify');

    expect(Plan::find($plan->id))->toBeNull();
});
