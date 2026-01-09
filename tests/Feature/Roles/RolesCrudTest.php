<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access roles pages', function () {
    $this->get(route('roles.index'))->assertRedirect(route('login'));
    $this->get(route('roles.create'))->assertRedirect(route('login'));
    
    $role = Role::create(['name' => 'test-role']);
    $this->get(route('roles.edit', $role->uuid))->assertRedirect(route('login'));
    $this->get(route('roles.show', $role->uuid))->assertRedirect(route('login'));
});

test('unauthorized user cannot access roles pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
    $this->actingAs($user)->get(route('roles.create'))->assertForbidden();

    $role = Role::create(['name' => 'test-role']);
    $this->actingAs($user)->get(route('roles.edit', $role->uuid))->assertForbidden();
    $this->actingAs($user)->get(route('roles.show', $role->uuid))->assertForbidden();
});

test('authorized user can list roles', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::VIEW_ROLES]);
    $role = Role::create(['name' => 'viewer']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\Tables\RoleTable::class);
});

test('authorized user can create role', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::CREATE_ROLES]);
    $role = Role::create(['name' => 'creator']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::roles.create')
        ->set('name', 'new-role')
        ->set('display_name', 'New Role')
        ->call('createRole')
        ->assertRedirect(route('roles.index'));

    expect(Role::where('name', 'new-role')->exists())->toBeTrue();
});

test('authorized user can edit role', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_ROLES]);
    $role = Role::create(['name' => 'editor']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $targetRole = Role::create(['name' => 'target-role', 'display_name' => 'Target Role']);

    $this->actingAs($user)
        ->get(route('roles.edit', $targetRole->uuid))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::roles.edit', ['role' => $targetRole])
        ->set('display_name', 'Updated Role')
        ->call('updateRole');

    expect($targetRole->fresh()->display_name)->toBe('Updated Role');
});

test('authorized user can delete role', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::DELETE_ROLES]);
    $viewerPermission = Permission::create(['name' => Permissions::VIEW_ROLES]); // Needed for redirection typically or index view
    $role = Role::create(['name' => 'deleter']);
    $role->givePermissionTo($permission, $viewerPermission);
    $user->assignRole($role);

    $targetRole = Role::create(['name' => 'target-role']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Tables\RoleTable::class)
        ->call('executeAction', 'delete', $targetRole->uuid);

    expect(Role::where('name', 'target-role')->exists())->toBeFalse();
});

test('protected roles cannot be deleted', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::DELETE_ROLES]);
    $viewerPermission = Permission::create(['name' => Permissions::VIEW_ROLES]);
    $role = Role::create(['name' => 'deleter']);
    $role->givePermissionTo($permission, $viewerPermission);
    $user->assignRole($role);

    // Create super_admin role manually if it doesn't exist (it should from seeding, but we are refreshing db)
    // Actually RefreshDatabase wipes it, so we need to create it to test protection logic which relies on NAME
    $superAdmin = Role::create(['name' => \App\Constants\Auth\Roles::SUPER_ADMIN]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Tables\RoleTable::class)
        ->call('executeAction', 'delete', $superAdmin->uuid);

    // Should still exist
    expect(Role::where('name', \App\Constants\Auth\Roles::SUPER_ADMIN)->exists())->toBeTrue();
});
