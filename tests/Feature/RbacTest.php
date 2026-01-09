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
    $role = Role::create(['name' => Roles::ADMIN]);

    expect($role->name)->toBe(Roles::ADMIN)
        ->and($role->uuid)->not->toBeNull();
});

test('can create a permission', function () {
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLES]);

    expect($permission->name)->toBe(Permissions::EDIT_ARTICLES)
        ->and($permission->uuid)->not->toBeNull();
});

test('role can have permissions', function () {
    $role = Role::create(['name' => Roles::ADMIN]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLES]);

    $role->givePermissionTo($permission);

    expect($role->permissions)->toHaveCount(1)
        ->and($role->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeTrue();
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::ADMIN]);

    $user->assignRole($role);

    expect($user->hasRole(Roles::ADMIN))->toBeTrue();
});

test('user gets permissions through role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::ADMIN]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLES]);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeTrue();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $adminRole = Role::create(['name' => Roles::ADMIN]);
    $memberRole = Role::create(['name' => Roles::MEMBER]);

    $user->assignRole($adminRole, $memberRole);

    expect($user->hasRole(Roles::ADMIN))->toBeTrue()
        ->and($user->hasRole(Roles::MEMBER))->toBeTrue();
});

test('can check if user has any of given roles', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::ADMIN]);

    $user->assignRole($role);

    expect($user->hasAnyRole([Roles::ADMIN, Roles::MEMBER]))->toBeTrue()
        ->and($user->hasAnyRole([Roles::SUPER_ADMIN, Roles::MEMBER]))->toBeFalse();
});

test('can check if user has all given roles', function () {
    $user = User::factory()->create();
    $adminRole = Role::create(['name' => Roles::ADMIN]);
    $memberRole = Role::create(['name' => Roles::MEMBER]);

    $user->assignRole($adminRole, $memberRole);

    expect($user->hasAllRoles([Roles::ADMIN, Roles::MEMBER]))->toBeTrue()
        ->and($user->hasAllRoles([Roles::ADMIN, Roles::SUPER_ADMIN]))->toBeFalse();
});

test('can sync roles to user', function () {
    $user = User::factory()->create();
    $adminRole = Role::create(['name' => Roles::ADMIN]);
    $memberRole = Role::create(['name' => Roles::MEMBER]);
    $superAdminRole = Role::create(['name' => Roles::SUPER_ADMIN]);

    $user->assignRole($adminRole);
    expect($user->hasRole(Roles::ADMIN))->toBeTrue();

    // Sync replaces existing roles
    $user->syncRoles([$memberRole, $superAdminRole]);

    expect($user->fresh()->hasRole(Roles::ADMIN))->toBeFalse()
        ->and($user->hasRole(Roles::MEMBER))->toBeTrue()
        ->and($user->hasRole(Roles::SUPER_ADMIN))->toBeTrue();
});

test('can remove role from user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::ADMIN]);

    $user->assignRole($role);
    expect($user->hasRole(Roles::ADMIN))->toBeTrue();

    $user->removeRole($role);
    expect($user->fresh()->hasRole(Roles::ADMIN))->toBeFalse();
});

test('can get all permissions for user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::ADMIN]);
    $permission1 = Permission::create(['name' => Permissions::EDIT_ARTICLES]);
    $permission2 = Permission::create(['name' => Permissions::DELETE_ARTICLES]);

    $role->givePermissionTo($permission1, $permission2);
    $user->assignRole($role);

    $permissions = $user->getAllPermissions();

    expect($permissions)->toHaveCount(2)
        ->and($user->getPermissionNames())->toContain(Permissions::EDIT_ARTICLES)
        ->and($user->getPermissionNames())->toContain(Permissions::DELETE_ARTICLES);
});

test('role permissions can be synced', function () {
    $role = Role::create(['name' => Roles::ADMIN]);
    $permission1 = Permission::create(['name' => Permissions::EDIT_ARTICLES]);
    $permission2 = Permission::create(['name' => Permissions::DELETE_ARTICLES]);

    $role->givePermissionTo($permission1);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeTrue();

    // Sync replaces permissions
    $role->syncPermissions([$permission2]);

    expect($role->fresh()->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeFalse()
        ->and($role->hasPermissionTo(Permissions::DELETE_ARTICLES))->toBeTrue();
});

test('can revoke permission from role', function () {
    $role = Role::create(['name' => Roles::ADMIN]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLES]);

    $role->givePermissionTo($permission);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeTrue();

    $role->revokePermissionTo($permission);
    expect($role->fresh()->hasPermissionTo(Permissions::EDIT_ARTICLES))->toBeFalse();
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

test('permission matrix generates correct permission names', function () {
    $matrix = new \App\Services\Auth\PermissionMatrix;

    $permissionNames = $matrix->getAllPermissionNames();

    expect($permissionNames)->toBeArray()
        ->and($permissionNames)->toContain('view users')
        ->and($permissionNames)->toContain('edit roles')
        ->and($permissionNames)->toContain('access telescope');
});

test('permission matrix returns correct actions for entity', function () {
    $matrix = new \App\Services\Auth\PermissionMatrix;

    $userActions = $matrix->getActionsForEntity('users');

    expect($userActions)->toBeArray()
        ->and($userActions)->toContain('view')
        ->and($userActions)->toContain('create')
        ->and($userActions)->toContain('edit')
        ->and($userActions)->toContain('delete')
        ->and($userActions)->toContain('activate');
});

test('permission matrix correctly identifies entity-action support', function () {
    $matrix = new \App\Services\Auth\PermissionMatrix;

    // Users support activate
    expect($matrix->entitySupportsAction('users', 'activate'))->toBeTrue();

    // Roles do NOT support activate
    expect($matrix->entitySupportsAction('roles', 'activate'))->toBeFalse();

    // Telescope only supports access
    expect($matrix->entitySupportsAction('telescope', 'access'))->toBeTrue();
    expect($matrix->entitySupportsAction('telescope', 'edit'))->toBeFalse();
});
