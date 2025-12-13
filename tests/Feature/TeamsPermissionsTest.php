<?php

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache before each test
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});

test('can create role with team id', function () {
    $role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);

    expect($role->team_id)->toBe(1)
        ->and($role->name)->toBe(Roles::WRITER);
});

test('can create global role without team id', function () {
    $role = Role::create(['name' => Roles::ADMIN, 'team_id' => null]);

    expect($role->team_id)->toBeNull()
        ->and($role->name)->toBe(Roles::ADMIN);
});

test('same role name can exist for different teams', function () {
    $team1Role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Role = Role::create(['name' => Roles::WRITER, 'team_id' => 2]);

    expect($team1Role->id)->not->toBe($team2Role->id)
        ->and($team1Role->team_id)->toBe(1)
        ->and($team2Role->team_id)->toBe(2);
});

test('user can have different roles for different teams', function () {
    $user = User::factory()->create();
    $team1Role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Role = Role::create(['name' => Roles::EDITOR, 'team_id' => 2]);

    setPermissionsTeamId(1);
    $user->assignRole($team1Role);
    expect($user->hasRole(Roles::WRITER))->toBeTrue()
        ->and($user->hasRole(Roles::EDITOR))->toBeFalse();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $user->assignRole($team2Role);
    expect($user->hasRole(Roles::WRITER))->toBeFalse()
        ->and($user->hasRole(Roles::EDITOR))->toBeTrue();
});

test('user permissions are team-specific', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    setPermissionsTeamId(1);
    $user->givePermissionTo($permission);
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();
});

test('role permissions are team-specific', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);

    setPermissionsTeamId(1);
    $user->assignRole($role);
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();
});

test('can switch teams and check permissions', function () {
    $user = User::factory()->create();
    $team1Role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Role = Role::create(['name' => Roles::EDITOR, 'team_id' => 2]);
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);

    $team1Role->givePermissionTo($editPermission);
    $team2Role->givePermissionTo($deletePermission);

    // Team 1
    setPermissionsTeamId(1);
    $user->assignRole($team1Role);
    expect($user->hasRole(Roles::WRITER))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeFalse();

    // Switch to Team 2
    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $user->assignRole($team2Role);
    expect($user->hasRole(Roles::EDITOR))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse()
        ->and($user->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue();
});

test('global role works across all teams', function () {
    $user = User::factory()->create();
    $globalRole = Role::create(['name' => Roles::ADMIN, 'team_id' => null]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $globalRole->givePermissionTo($permission);

    setPermissionsTeamId(1);
    $user->assignRole($globalRole);
    expect($user->hasRole(Roles::ADMIN))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    // Note: Global roles (team_id = null) are assigned per team context
    // When switching teams, the role assignment is team-specific
    // So we need to assign the role again for the new team context
    $user->assignRole($globalRole);
    expect($user->hasRole(Roles::ADMIN))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();
});

test('can query roles by team', function () {
    $team1Role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Role = Role::create(['name' => Roles::WRITER, 'team_id' => 2]);
    $globalRole = Role::create(['name' => Roles::ADMIN, 'team_id' => null]);

    setPermissionsTeamId(1);
    $team1Roles = Role::where('team_id', 1)->get();
    expect($team1Roles)->toHaveCount(1)
        ->and($team1Roles->first()->id)->toBe($team1Role->id);

    setPermissionsTeamId(2);
    $team2Roles = Role::where('team_id', 2)->get();
    expect($team2Roles)->toHaveCount(1)
        ->and($team2Roles->first()->id)->toBe($team2Role->id);
});

test('user can have same role name in different teams', function () {
    $user = User::factory()->create();
    $team1Writer = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Writer = Role::create(['name' => Roles::WRITER, 'team_id' => 2]);

    setPermissionsTeamId(1);
    $user->assignRole($team1Writer);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $user->assignRole($team2Writer);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();
});

test('can remove role from specific team', function () {
    $user = User::factory()->create();
    $team1Role = Role::create(['name' => Roles::WRITER, 'team_id' => 1]);
    $team2Role = Role::create(['name' => Roles::EDITOR, 'team_id' => 2]);

    setPermissionsTeamId(1);
    $user->assignRole($team1Role);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $user->assignRole($team2Role);
    expect($user->hasRole(Roles::EDITOR))->toBeTrue();

    setPermissionsTeamId(1);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $user->removeRole($team1Role);
    expect($user->hasRole(Roles::WRITER))->toBeFalse();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasRole(Roles::EDITOR))->toBeTrue();
});

test('permissions are isolated per team in pivot tables', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    setPermissionsTeamId(1);
    $user->givePermissionTo($permission);

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();

    $user->givePermissionTo($permission);
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    // Both teams should have the permission now
    setPermissionsTeamId(1);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles')->unsetRelation('permissions');
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();
});
