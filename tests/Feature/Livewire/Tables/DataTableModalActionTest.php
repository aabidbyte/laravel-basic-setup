<?php

use App\Models\User;
use App\Models\Permission;
use App\Constants\Auth\Permissions;
use Livewire\Livewire;

beforeEach(function () {
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    setPermissionsTeamId(1);

    $this->admin = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => Permissions::VIEW_USERS]);
    $this->admin->givePermissionTo($permission);
});

it('can open the details modal via row action', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->call('openActionModal', 'view_modal', $user->uuid)
        ->assertSet('modalComponent', 'components.users.view-modal')
        ->assertSet('modalType', 'blade')
        ->assertSet('modalProps', function ($props) use ($user) {
            return isset($props['user']) && $props['user']->is($user);
        })
        ->assertDispatched('open-datatable-modal')
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

it('can close the details modal', function () {
    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('modalComponent', 'components.users.view-modal')
        ->set('modalProps', ['user' => User::factory()->make()])
        ->call('closeActionModal')
        ->assertSet('modalComponent', null)
        ->assertSet('modalProps', [])
        ->assertDispatched('close-datatable-modal');
});
