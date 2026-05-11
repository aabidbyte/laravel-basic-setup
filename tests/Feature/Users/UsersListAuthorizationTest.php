<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('unauthenticated user is redirected to login for users.index', function () {
    $response = asTenant()->get(route('users.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user without VIEW_USERS permission receives 403', function () {
    $user = User::factory()->create();

    $response = asTenant()->actingAs($user)->get(route('users.index'));

    $response->assertForbidden();
});

test('authenticated user with VIEW_USERS permission gets 200', function () {
    $user = User::factory()->create();

    // Create permission and role
    $permission = Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    $role = Role::firstOrCreate(['name' => 'viewer']);
    $role->givePermissionTo($permission);

    // Assign role to user
    $user->assignRole($role);

    $response = asTenant()->actingAs($user)->get(route('users.index'));

    $response->assertOk();
});
