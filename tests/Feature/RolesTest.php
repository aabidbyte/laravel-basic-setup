<?php

use App\Constants\Auth\Roles;
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

test('can create a role', function () {
    $role = Role::create(['name' => Roles::WRITER]);

    expect($role)->toBeInstanceOf(Role::class)
        ->and($role->name)->toBe(Roles::WRITER)
        ->and($role->uuid)->not->toBeNull()
        ->and($role->guard_name)->toBe('web');
});

test('role has uuid column', function () {
    $role = Role::create(['name' => Roles::ADMIN]);

    expect($role->uuid)->not->toBeNull()
        ->and($role->uuid)->toBeString()
        ->and(strlen($role->uuid))->toBe(36); // UUID format length
});

test('can assign role to user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);

    $user->assignRole($role);

    expect($user->hasRole(Roles::WRITER))->toBeTrue()
        ->and($user->roles)->toHaveCount(1)
        ->and($user->roles->first()->name)->toBe(Roles::WRITER);
});

test('can assign multiple roles to user', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    $user->assignRole([$writerRole, $editorRole]);

    expect($user->hasRole(Roles::WRITER))->toBeTrue()
        ->and($user->hasRole(Roles::EDITOR))->toBeTrue()
        ->and($user->roles)->toHaveCount(2);
});

test('can assign role using string name', function () {
    $user = User::factory()->create();
    Role::create(['name' => Roles::ADMIN]);

    $user->assignRole(Roles::ADMIN);

    expect($user->hasRole(Roles::ADMIN))->toBeTrue();
});

test('can check if user has role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::MODERATOR]);

    expect($user->hasRole(Roles::MODERATOR))->toBeFalse();

    $user->assignRole($role);

    expect($user->hasRole(Roles::MODERATOR))->toBeTrue();
});

test('can check if user has any role', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    expect($user->hasAnyRole([Roles::WRITER, Roles::EDITOR]))->toBeFalse();

    $user->assignRole($writerRole);

    expect($user->hasAnyRole([Roles::WRITER, Roles::EDITOR]))->toBeTrue();
});

test('can check if user has all roles', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    expect($user->hasAllRoles([Roles::WRITER, Roles::EDITOR]))->toBeFalse();

    $user->assignRole($writerRole);

    expect($user->hasAllRoles([Roles::WRITER, Roles::EDITOR]))->toBeFalse();

    $user->assignRole($editorRole);

    expect($user->hasAllRoles([Roles::WRITER, Roles::EDITOR]))->toBeTrue();
});

test('can remove role from user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => Roles::WRITER]);

    $user->assignRole($role);
    expect($user->hasRole(Roles::WRITER))->toBeTrue();

    $user->removeRole($role);
    expect($user->hasRole(Roles::WRITER))->toBeFalse();
});

test('can sync roles for user', function () {
    $user = User::factory()->create();
    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);
    $moderatorRole = Role::create(['name' => Roles::MODERATOR]);

    $user->assignRole([$writerRole, $editorRole]);
    expect($user->roles)->toHaveCount(2);

    $user->syncRoles([$editorRole, $moderatorRole]);
    expect($user->roles)->toHaveCount(2)
        ->and($user->hasRole(Roles::WRITER))->toBeFalse()
        ->and($user->hasRole(Roles::EDITOR))->toBeTrue()
        ->and($user->hasRole(Roles::MODERATOR))->toBeTrue();
});

test('can query users by role', function () {
    $writerUser = User::factory()->create();
    $editorUser = User::factory()->create();
    $regularUser = User::factory()->create();

    $writerRole = Role::create(['name' => Roles::WRITER]);
    $editorRole = Role::create(['name' => Roles::EDITOR]);

    $writerUser->assignRole($writerRole);
    $editorUser->assignRole($editorRole);

    $writers = User::role(Roles::WRITER)->get();
    expect($writers)->toHaveCount(1)
        ->and($writers->first()->id)->toBe($writerUser->id);

    $editors = User::role(Roles::EDITOR)->get();
    expect($editors)->toHaveCount(1)
        ->and($editors->first()->id)->toBe($editorUser->id);
});

test('can query users without role', function () {
    $writerUser = User::factory()->create();
    $regularUser = User::factory()->create();

    $writerRole = Role::create(['name' => Roles::WRITER]);
    $writerUser->assignRole($writerRole);

    $nonWriters = User::withoutRole(Roles::WRITER)->get();
    expect($nonWriters->pluck('id')->toArray())->toContain($regularUser->id)
        ->and($nonWriters->pluck('id')->toArray())->not->toContain($writerUser->id);
});

test('role names must be unique per guard', function () {
    Role::create(['name' => Roles::ADMIN, 'guard_name' => 'web']);

    expect(fn () => Role::create(['name' => Roles::ADMIN, 'guard_name' => 'web']))
        ->toThrow(\Spatie\Permission\Exceptions\RoleAlreadyExists::class);
});

test('can create role with different guard', function () {
    $webRole = Role::create(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $apiRole = Role::create(['name' => Roles::ADMIN, 'guard_name' => 'api']);

    expect($webRole->guard_name)->toBe('web')
        ->and($apiRole->guard_name)->toBe('api')
        ->and($webRole->id)->not->toBe($apiRole->id);
});
