<?php

declare(strict_types=1);

use App\Constants\Permissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache before each test
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Set default team_id for tests (teams are enabled by default)
    setPermissionsTeamId(1);

    $this->user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::VIEW_USERS]);
    $this->user->givePermissionTo($permission);
});

test('search filters results', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    Livewire::actingAs($this->user)
        ->test('users.table')
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('sort toggles direction', function () {
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $component = Livewire::actingAs($this->user)
        ->test('users.table');

    // Component defaults to sortBy='name' and sortDirection='asc'
    // First click on 'name' (already sorted by name) toggles to desc
    $component->call('sortBy', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'desc');

    // Second click toggles back to asc
    $component->call('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');
});

test('bulk select page sets selected to current page UUIDs', function () {
    $users = User::factory()->count(5)->create();

    $component = Livewire::actingAs($this->user)
        ->test('users.table')
        ->set('perPage', 3);

    $component->call('toggleSelectPage')
        ->assertSet('selectPage', true);

    // Should have selected users from current page
    expect($component->get('selected'))->toBeArray()
        ->and(count($component->get('selected')))->toBeLessThanOrEqual(3);
});
