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
    // Create permissions
    $viewPermission = Permission::create(['name' => Permissions::VIEW_USERS()]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_USERS()]);

    $adminRole = Role::create(['name' => 'admin']);
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

    \Illuminate\Support\Carbon::setTestNow($now);

    Livewire::actingAs($this->admin)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->assertSee(__('actions.delete'))
        ->assertSee(__('users.updated_at'))
        ->assertSee(__('users.email_verified_at'))
        ->assertSee(__('users.notification_preferences'))
        ->assertSee(__('users.created_users_count'))
        ->assertSee('Mail')
        ->assertSee('Database')
        ->assertSee($this->targetUser->updated_at->diffForHumans());

    \Illuminate\Support\Carbon::setTestNow();
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
    $viewerRole = Role::create(['name' => 'viewer']);
    $viewerRole->givePermissionTo(Permission::where('name', Permissions::VIEW_USERS())->first());

    $viewer = User::factory()->create();
    $viewer->assignRole($viewerRole);

    Livewire::actingAs($viewer)
        ->test('pages::users.show', ['user' => $this->targetUser])
        ->assertDontSee(__('actions.delete'));
});
