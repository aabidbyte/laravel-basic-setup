<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache before each test
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Set default team_id for tests (teams are enabled by default)
    setPermissionsTeamId(1);
});

test('can create a permission', function () {
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    expect($permission)->toBeInstanceOf(Permission::class)
        ->and($permission->name)->toBe(Permissions::EDIT_ARTICLE)
        ->and($permission->uuid)->not->toBeNull()
        ->and($permission->guard_name)->toBe('web');
});

test('permission has uuid column', function () {
    $permission = Permission::create(['name' => Permissions::VIEW_DOCUMENT]);

    expect($permission->uuid)->not->toBeNull()
        ->and($permission->uuid)->toBeString()
        ->and(strlen($permission->uuid))->toBe(36); // UUID format length
});

test('can assign permission directly to user', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $user->givePermissionTo($permission);

    expect($user->hasDirectPermission(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->can(Permissions::EDIT_ARTICLE))->toBeTrue();
});

test('can assign multiple permissions to user', function () {
    $user = User::factory()->create();
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);

    $user->givePermissionTo([$editPermission, $deletePermission]);

    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue()
        ->and($user->getDirectPermissions())->toHaveCount(2);
});

test('can assign permission using string name', function () {
    $user = User::factory()->create();
    Permission::create(['name' => Permissions::VIEW_DOCUMENT]);

    $user->givePermissionTo(Permissions::VIEW_DOCUMENT);

    expect($user->hasPermissionTo(Permissions::VIEW_DOCUMENT))->toBeTrue();
});

test('can revoke permission from user', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $user->givePermissionTo($permission);
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    $user->revokePermissionTo($permission);
    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();
});

test('can sync permissions for user', function () {
    $user = User::factory()->create();
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);
    $publishPermission = Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);

    $user->givePermissionTo([$editPermission, $deletePermission]);
    expect($user->getDirectPermissions())->toHaveCount(2);

    $user->syncPermissions([$deletePermission, $publishPermission]);
    expect($user->getDirectPermissions())->toHaveCount(2)
        ->and($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse()
        ->and($user->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::PUBLISH_ARTICLE))->toBeTrue();
});

test('can assign permission to role', function () {
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);

    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();
});

test('user gets permission through role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->hasDirectPermission(Permissions::EDIT_ARTICLE))->toBeFalse()
        ->and($user->getPermissionsViaRoles())->toHaveCount(1);
});

test('user can have both direct and role-based permissions', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);

    $role->givePermissionTo($editPermission);
    $user->assignRole($role);
    $user->givePermissionTo($deletePermission);

    expect($user->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue()
        ->and($user->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue()
        ->and($user->getAllPermissions())->toHaveCount(2);
});

test('can revoke permission from role', function () {
    $role = Role::create(['name' => Roles::WRITER]);
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $role->givePermissionTo($permission);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeTrue();

    $role->revokePermissionTo($permission);
    expect($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse();
});

test('can sync permissions for role', function () {
    $role = Role::create(['name' => Roles::EDITOR]);
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);
    $publishPermission = Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);

    $role->givePermissionTo([$editPermission, $deletePermission]);
    expect($role->permissions)->toHaveCount(2);

    $role->syncPermissions([$deletePermission, $publishPermission]);
    expect($role->permissions)->toHaveCount(2)
        ->and($role->hasPermissionTo(Permissions::EDIT_ARTICLE))->toBeFalse()
        ->and($role->hasPermissionTo(Permissions::DELETE_ARTICLE))->toBeTrue()
        ->and($role->hasPermissionTo(Permissions::PUBLISH_ARTICLE))->toBeTrue();
});

test('can query users by permission', function () {
    $userWithPermission = User::factory()->create();
    $userWithoutPermission = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $userWithPermission->givePermissionTo($permission);

    $usersWithPermission = User::permission(Permissions::EDIT_ARTICLE)->get();
    expect($usersWithPermission)->toHaveCount(1)
        ->and($usersWithPermission->first()->id)->toBe($userWithPermission->id);
});

test('can query users without permission', function () {
    $userWithPermission = User::factory()->create();
    $userWithoutPermission = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);

    $userWithPermission->givePermissionTo($permission);

    $usersWithoutPermission = User::withoutPermission(Permissions::EDIT_ARTICLE)->get();
    expect($usersWithoutPermission->pluck('id')->toArray())->toContain($userWithoutPermission->id)
        ->and($usersWithoutPermission->pluck('id')->toArray())->not->toContain($userWithPermission->id);
});

test('permission names must be unique per guard', function () {
    Permission::create(['name' => Permissions::EDIT_ARTICLE, 'guard_name' => 'web']);

    expect(fn () => Permission::create(['name' => Permissions::EDIT_ARTICLE, 'guard_name' => 'web']))
        ->toThrow(\Spatie\Permission\Exceptions\PermissionAlreadyExists::class);
});

test('can create permission with different guard', function () {
    $webPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE, 'guard_name' => 'web']);
    $apiPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE, 'guard_name' => 'api']);

    expect($webPermission->guard_name)->toBe('web')
        ->and($apiPermission->guard_name)->toBe('api')
        ->and($webPermission->id)->not->toBe($apiPermission->id);
});

test('can get all permissions for user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);
    $publishPermission = Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);

    $role->givePermissionTo([$editPermission, $deletePermission]);
    $user->assignRole($role);
    $user->givePermissionTo($publishPermission);

    $allPermissions = $user->getAllPermissions();
    expect($allPermissions)->toHaveCount(3)
        ->and($allPermissions->pluck('name')->toArray())->toContain(Permissions::EDIT_ARTICLE)
        ->and($allPermissions->pluck('name')->toArray())->toContain(Permissions::DELETE_ARTICLE)
        ->and($allPermissions->pluck('name')->toArray())->toContain(Permissions::PUBLISH_ARTICLE);
});

test('can check multiple permissions at once', function () {
    $user = User::factory()->create();
    $editPermission = Permission::create(['name' => Permissions::EDIT_ARTICLE]);
    $deletePermission = Permission::create(['name' => Permissions::DELETE_ARTICLE]);
    $publishPermission = Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);

    $user->givePermissionTo([$editPermission, $deletePermission]);

    expect($user->hasAllPermissions([Permissions::EDIT_ARTICLE, Permissions::DELETE_ARTICLE]))->toBeTrue()
        ->and($user->hasAllPermissions([Permissions::EDIT_ARTICLE, Permissions::PUBLISH_ARTICLE]))->toBeFalse()
        ->and($user->hasAnyPermission([Permissions::EDIT_ARTICLE, Permissions::PUBLISH_ARTICLE]))->toBeTrue();
});
