<?php

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\User;
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

    // Now dispatches to global modal component instead of setting local properties
    $component->call('openActionModal', 'view_modal', $user->uuid)
        ->assertDispatched('open-datatable-modal', function ($eventName, $params) use ($user) {
            return $params['viewPath'] === 'components.users.view-modal'
                && $params['viewType'] === 'blade'
                && isset($params['viewProps']['userUuid'])
                && $params['viewProps']['userUuid'] === $user->uuid;
        });
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

it('opens modal when row is clicked via handleRowClick', function () {
    $user = User::factory()->create([
        'name' => 'Row Click User',
        'email' => 'rowclick@example.com',
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    // Now dispatches to global modal component instead of setting local properties
    $component->call('handleRowClick', $user->uuid)
        ->assertDispatched('open-datatable-modal', function ($eventName, $params) {
            return $params['viewPath'] === 'components.users.view-modal'
                && $params['viewType'] === 'blade';
        });
});

it('detects rows are clickable when rowClick is overridden', function () {
    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    expect($component->instance()->rowsAreClickable())->toBeTrue();
});

it('detects row click opens modal when action has modal', function () {
    // Create at least one user for the sample row check
    User::factory()->create();

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    // UserTable's rowClick returns an action with bladeModal, so this should be true
    expect($component->instance()->rowClickOpensModal())->toBeTrue();
});

it('returns false for rowClickOpensModal when no rows exist', function () {
    // Clear all users
    User::query()->forceDelete();

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    // With no rows, rowClickOpensModal should return false
    expect($component->instance()->rowClickOpensModal())->toBeFalse();
});
