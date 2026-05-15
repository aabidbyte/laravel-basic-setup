<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\Tables\UserTable;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    $deletePermission = Permission::firstOrCreate(['name' => Permissions::DELETE_USERS()]);
    $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
    $viewerRole->givePermissionTo($permission);
    $viewerRole->givePermissionTo($deletePermission);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::SUPER_ADMIN);
    $this->admin->assignRole($viewerRole);

    $this->createVisibleUser = function (array $attributes = []): User {
        return User::factory()->create($attributes);
    };
});

// Note: UserTable's rowActions no longer includes a view_modal action.
// If modal actions need testing, create a dedicated test table component.

it('can close the details modal', function () {
    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER);

    $component->set('modalComponent', 'components.users.view-modal')
        ->set('modalProps', ['user' => User::factory()->make()])
        ->call('closeActionModal')
        ->assertSet('modalComponent', null)
        ->assertSet('modalProps', []);

    $component->assertDispatched("datatable:close-modal:{$component->id()}");
});

it('returns confirmation config for row action', function () {
    $user = ($this->createVisibleUser)();

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->call('getActionConfirmation', 'delete', $user->uuid)
        ->assertReturned([
            'required' => true,
            'type' => 'message',
            'message' => __('actions.confirm_delete'),
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
            'message' => __('actions.confirm_bulk_delete'),
        ]);
});

it('refreshes rows after row deletion', function () {
    $user = ($this->createVisibleUser)(['name' => 'To Be Deleted']);

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER)
        ->assertSee('To Be Deleted')
        ->call('executeAction', 'delete', $user->uuid);

    Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER)
        ->assertDontSee('To Be Deleted');

    expect(User::where('uuid', $user->uuid)->exists())->toBeFalse();
});

it('refreshes rows after bulk deletion', function () {
    $users = User::factory()->count(2)->create(['name' => 'Bulk Delete']);
    $uuids = $users->pluck('uuid')->toArray();

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER)
        ->set('selected', $uuids)
        ->assertSee('Bulk Delete')
        ->call('executeBulkAction', 'delete');

    expect(User::whereIn('uuid', $uuids)->count())->toBe(0);

    $component->assertSet('selected', []);
});

it('redirects when row is clicked and route exists', function () {
    // Register a temporary route for testing
    Route::get('/users/{user}', fn () => 'User page')->name('users.show');

    $user = ($this->createVisibleUser)([
        'name' => 'Row Click User',
        'email' => 'rowclick@example.com',
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER);

    // rowClick should redirect to the user show page
    $component->call('handleRowClick', $user->uuid)
        ->assertRedirect(route('users.show', $user->uuid));
});

it('does nothing when row is clicked but route does not exist', function () {
    // Clear routes to ensure users.show doesn't exist
    Route::getRoutes()->refreshNameLookups();

    $user = ($this->createVisibleUser)([
        'name' => 'Row Click User',
        'email' => 'rowclick@example.com',
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER);

    // rowClick should not dispatch anything when route doesn't exist
    $component->call('handleRowClick', $user->uuid)
        ->assertNotDispatched('open-datatable-modal');
});

it('detects rows are clickable when rowClick is overridden', function () {
    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER);

    expect($component->instance()->rowsAreClickable())->toBeTrue();
});

it('detects row click opens modal when action has modal', function () {
    // Register a temporary route for testing - without this, rowClick returns null
    Route::get('/users/{user}', fn () => 'User page')->name('users.show');

    // Create at least one user for the sample row check
    ($this->createVisibleUser)();

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    // UserTable's rowClick now returns a route action, not a modal action
    // So rowClickOpensModal should return false
    expect($component->instance()->rowClickOpensModal())->toBeFalse();
});

it('returns false for rowClickOpensModal when no rows exist', function () {
    // Clear all users except the admin
    User::where('id', '!=', $this->admin->id)->forceDelete();

    $component = Livewire::actingAs($this->admin)
        ->test('tables.user-table');

    // With no rows, rowClickOpensModal should return false
    expect($component->instance()->rowClickOpensModal())->toBeFalse();
});

it('ActionModal can map options from event payload', function () {
    $options = [
        'viewType' => 'confirm',
        'viewProps' => [
            'title' => 'Test Title',
            'content' => 'Test Content',
        ],
        'datatableId' => 'test-dt',
    ];

    Livewire::test('data-table.action-modal')
        ->dispatch('open-datatable-modal', options: $options)
        ->assertSet('isOpen', true)
        ->assertSet('modalTitle', 'Test Title')
        ->assertSet('datatableId', 'test-dt');
});
