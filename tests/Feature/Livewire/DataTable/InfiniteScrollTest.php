<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::create(['name' => Permissions::VIEW_USERS]);
    $viewerRole = Role::create(['name' => 'viewer']);
    $viewerRole->givePermissionTo($permission);

    $this->user = User::factory()->create();
    $this->user->assignRole($viewerRole);
});

test('it initializes with visibleRows set to 20', function () {
    Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->assertSet('visibleRows', 20);
});

test('loadMore increments visibleRows by 20', function () {
    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table');

    $component->call('loadMore')
        ->assertSet('visibleRows', 40);

    $component->call('loadMore')
        ->assertSet('visibleRows', 60);
});

test('refreshing table resets visibleRows to 20', function () {
    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table');

    $component->call('loadMore')
        ->assertSet('visibleRows', 40);

    // Trigger a refresh via search (which calls applyChanges -> refreshTable)
    $component->set('search', 'foo')
        ->assertSet('visibleRows', 20);
});

test('sorting resets visibleRows to 20', function () {
    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table');

    $component->call('loadMore')
        ->assertSet('visibleRows', 40);

    // Trigger sort
    $component->call('sort', 'name')
        ->assertSet('visibleRows', 20);
});

test('performGotoPage updates page and resets input', function () {
    User::factory()->count(30)->create(); // Ensure we have enough pages (default perPage is 15)

    Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->set('gotoPageInput', 2)
        ->call('performGotoPage')
        ->assertSet('paginators.page', 2)
        ->assertSet('gotoPageInput', null);
});

test('performGotoPage validation ignores invalid pages', function () {
    User::factory()->count(30)->create(); // 2 pages

    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table');

    $component->set('gotoPageInput', 5) // Invalid page
        ->call('performGotoPage')
        ->assertSet('paginators.page', 1)
        ->assertSet('gotoPageInput', 5); // Should remain set as feedback/unchanged

    $component->set('gotoPageInput', 0) // Invalid page
        ->call('performGotoPage')
        ->assertSet('paginators.page', 1);
});
