<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Create permissions
    $viewPermission = Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    $deletePermission = Permission::firstOrCreate(['name' => Permissions::DELETE_USERS()]);

    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $adminRole->givePermissionTo($viewPermission);
    $adminRole->givePermissionTo($deletePermission);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);

    $this->targetUser = User::factory()->create(['name' => 'Target User']);
});

test('authorized user can see all user details on show page', function () {
    $now = now();
    $this->targetUser->forceFill([
        'email_verified_at' => $now->copy()->subDays(1),
        'updated_at' => $now->copy()->subSeconds(10),
        'notification_preferences' => ['mail' => true, 'database' => false],
    ])->save();

    // Create a user by this user
    User::factory()->create(['created_by_user_id' => $this->targetUser->id]);

    Carbon::setTestNow($now);

    Livewire::actingAs($this->admin)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->assertSee(__('actions.delete'))
        ->assertSee(__('tenancy.overview'))
        ->assertSee(__('users.access'))
        ->assertSee(__('users.preferences'))
        ->assertSee(__('users.created_at'))
        ->assertSee(__('users.email_verified_at'))
        ->assertSee($this->targetUser->created_at->diffForHumans())
        ->assertSee($this->targetUser->email_verified_at->diffForHumans());

    Carbon::setTestNow();
});

test('authorized user can navigate show page tabs', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->set('activeTab', 'access')
        ->assertSee(__('users.roles'))
        ->assertSee(__('users.teams'))
        ->assertSee(__('users.direct_permissions'))
        ->set('activeTab', 'preferences')
        ->assertSee(__('users.timezone'))
        ->assertSee(__('users.locale'));
});

test('authorized user can delete a user from show page', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->assertSee(__('actions.delete'))
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted('users', ['id' => $this->targetUser->id]);
});

test('unauthorized user cannot see delete button on show page', function () {
    $viewerRole = Role::create(['name' => 'user-show-viewer-role']);
    $viewerRole->givePermissionTo(Permission::where('name', Permissions::VIEW_USERS())->first());

    $viewer = User::factory()->create();
    $viewer->assignRole($viewerRole);

    Livewire::actingAs($viewer)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->assertDontSee(__('actions.delete'));
});
