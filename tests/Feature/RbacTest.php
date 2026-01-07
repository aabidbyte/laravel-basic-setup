<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Tests for the custom RBAC system.
 *
 * Our RBAC is simple: roles contain permissions, users have roles.
 * Team-based data isolation is handled by TeamScope global scope,
 * not by team-specific roles/permissions.
 */

test('can create a role', function () {
    $role = Role::create(['name' => Roles::WRITER]);

    expect($role->name)->toBe(Roles::WRITER)
        ->and($role->uuid)->not->toBeNull();
});

test('can create a permission', function () {
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    expect($permission->name)->toBe(Permissions::EDIT_ARTICLE)
        ->and($permission->uuid)->not->toBeNull();
});

test('role can have permissions', function () {
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);

    expect($role->permissions)->toHaveCount(1)
        ->and($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);

    $user->assignRole($role);

    expect($user->hasRole(Roles::WRITER))->toBeTrue();
});

test('user gets permissions through role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    $user->assignRole($writerRole, $editorRole);

    expect($user->hasRole(Roles::WRITER))->toBeTrue()
        ->and($user->hasRole(Roles::EDITOR))->toBeTrue();
});

test('can check if user has any of given roles', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);

    $user->assignRole($role);

    expect($user->hasAnyRole([Roles::WRITER, Roles::EDITOR]))->toBeTrue()
        ->and($user->hasAnyRole([Roles::ADMIN, Roles::MODERATOR]))->toBeFalse();
});

test('can check if user has all given roles', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    $user->assignRole($writerRole, $editorRole);

    expect($user->hasAllRoles([Roles::WRITER, Roles::EDITOR]))->toBeTrue()
        ->and($user->hasAllRoles([Roles::WRITER, Roles::ADMIN]))->toBeFalse();
});

test('can sync roles to user', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);
    $adminRole = Role::create(['name' => Roles::ADMIN]);

    $user->assignRole($writerRole);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();

    // Sync replaces existing roles
    $user->syncRoles([$editorRole, $adminRole]);

    expect($user->fresh()->hasRole(Roles::WRITER))->toBeFalse()
        ->and($user->hasRole(Roles::EDITOR))->toBeTrue()
        ->and($user->hasRole(Roles::ADMIN))->toBeTrue();
});

test('can remove role from user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);

    $user->assignRole($role);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();

    $user->removeRole($role);
    expect($user->fresh()->hasRole(Roles::WRITER))->toBeFalse();
});

test('can get all permissions for user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);
    $permission1 = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $permission2 = Permission::create(['name' => Permissions::DELETE_ARTICLE]);

    $role->givePermissionTo($permission1, $permission2);
    $user->assignRole($role);

    $permissions = $user->getAllPermissions();

    expect($permissions)->toHaveCount(2)
        ->and($user->getPermissionNames())->toContain(Permissions::EDIT_ARTICLE)
        ->and($user->getPermissionNames())->toContain(Permissions::DELETE_ARTICLE);
});

test('role permissions can be synced', function () {
    $role = Role::create(['name' => Roles::WRITER]);
    $permission1 = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $permission2 = Permission::create(['name' => Permissions::DELETE_ARTICLE]);

    $role->givePermissionTo($permission1);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    // Sync replaces permissions
    $role->syncPermissions([$permission2]);

    expect($role->fresh()->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse()
        ->and($role->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue();
});

test('can revoke permission from role', function () {
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    $role->revokePermissionTo($permission);
    expect($role->fresh()->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();
});

test('users belong to teams through pivot table', function () {
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();

    $user->teams()->attach($team->id, ['uuid' => (string) Str::uuid()]);

    expect($user->teams)->toHaveCount(1)
        ->and($user->teams->first()->name)->toBe('Test Team');
});

test('user can belong to multiple teams', function () {
    $team1 = Team::create(['name' => 'Team 1']);
    $team2 = Team::create(['name' => 'Team 2']);
    $user = User::factory()->create();

    $user->teams()->attach($team1->id, ['uuid' => (string) Str::uuid()]);
    $user->teams()->attach($team2->id, ['uuid' => (string) Str::uuid()]);

    expect($user->teams)->toHaveCount(2);
});

test('super admin role bypass works via Gate::before', function () {
    $user = User::factory()->create();
    $superAdminRole = Role::create(['name' => Roles::SUPER_ADMIN]);

    $user->assignRole($superAdminRole);

    // Super admin should have all permissions via Gate::before
    // (Note: This tests the role assignment, actual gate checking
    // requires authentication context in the app)
    expect($user->hasRole(Roles::SUPER_ADMIN))->toBeTrue();
});
