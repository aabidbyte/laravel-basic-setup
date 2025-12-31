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

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');
    
    $component->call('openActionModal', 'view_modal', $user->uuid)
        ->assertSet('modalComponent', 'components.users.view-modal')
        ->assertSet('modalType', 'blade')
        ->assertSet('modalProps', function ($props) use ($user) {
            return isset($props['user']) && $props['user']->is($user);
        });

    $component->assertDispatched("datatable:open-modal:{$component->id()}")
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

it('can close the details modal', function () {
    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');
    
    $component->set('modalComponent', 'components.users.view-modal')
        ->set('modalProps', ['user' => User::factory()->make()])
        ->call('closeActionModal')
        ->assertSet('modalComponent', null)
        ->assertSet('modalProps', []);

    $component->assertDispatched("datatable:close-modal:{$component->id()}");
});

it('returns confirmation config for row action', function () {
    $user = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->call('getActionConfirmation', 'delete', $user->uuid)
        ->assertReturned([
            'required' => true,
            'type' => 'message',
            'message' => __('ui.actions.confirm_delete'),
        ]);
});

it('returns confirmation config for bulk action', function () {
    $users = User::factory()->count(2)->create();
    $uuids = $users->pluck('uuid')->toArray();

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('selected', $uuids)
        ->call('getBulkActionConfirmation', 'delete')
        ->assertReturned([
            'required' => true,
            'type' => 'message',
            'message' => __('ui.actions.confirm_bulk_delete'),
        ]);
});

it('refreshes rows after row deletion', function () {
    $user = User::factory()->create(['name' => 'To Be Deleted']);

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->assertSee('To Be Deleted')
        ->call('executeAction', 'delete', $user->uuid)
        ->assertDontSee('To Be Deleted');
    
    expect(User::where('uuid', $user->uuid)->exists())->toBeFalse();
});

it('refreshes rows after bulk deletion', function () {
    $users = User::factory()->count(2)->create(['name' => 'Bulk Delete']);
    $uuids = $users->pluck('uuid')->toArray();

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('selected', $uuids)
        ->assertSee('Bulk Delete')
        ->call('executeBulkAction', 'delete')
        ->assertDontSee('Bulk Delete');
    
    expect(User::whereIn('uuid', $uuids)->count())->toBe(0);
});



