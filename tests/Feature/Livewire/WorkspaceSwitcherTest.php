<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Create roles
    $this->superAdminRole = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
    $this->adminRole = Role::firstOrCreate(['name' => Roles::ADMIN]);

    // Create tenants with random IDs to avoid database name collisions
    $this->tenant1 = Tenant::factory()->create(['name' => 'Tenant 1', 'id' => 't1' . Str::random(8)]);
    $this->tenant2 = Tenant::factory()->create(['name' => 'Tenant 2', 'id' => 't2' . Str::random(8)]);
    $this->tenant3 = Tenant::factory()->create(['name' => 'Tenant 3', 'id' => 't3' . Str::random(8)]);

    // Create a domain for each tenant to allow redirection
    $this->tenant1->domains()->create(['domain' => 'tenant1.test']);
    $this->tenant2->domains()->create(['domain' => 'tenant2.test']);
    $this->tenant3->domains()->create(['domain' => 'tenant3.test']);

    // Create users
    $this->superAdmin = User::factory()->create(['name' => 'Super Admin']);
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->normalUser = User::factory()->create(['name' => 'Normal User']);
    $this->normalUser->tenants()->attach($this->tenant1);
});

it('shows only associated tenants for normal users', function () {
    Livewire::actingAs($this->normalUser)
        ->test('tables.workspace-table')
        ->assertSee('Tenant 1')
        ->assertDontSee('Tenant 2')
        ->assertDontSee('Tenant 3');
});

it('shows all tenants for super admins', function () {
    Livewire::actingAs($this->superAdmin)
        ->test('tables.workspace-table')
        ->assertSee('Tenant 1')
        ->assertSee('Tenant 2')
        ->assertSee('Tenant 3');
});

it('hides current tenant from the list', function () {
    // Simulate being in tenant1 context
    tenancy()->initialize($this->tenant1);

    Livewire::actingAs($this->superAdmin)
        ->test('tables.workspace-table')
        ->assertDontSee('Tenant 1')
        ->assertSee('Tenant 2')
        ->assertSee('Tenant 3');

    tenancy()->end();
});

it('protects from switching to the current tenant', function () {
    // Simulate being in tenant1 context
    tenancy()->initialize($this->tenant1);

    Livewire::actingAs($this->superAdmin)
        ->test('tables.workspace-table')
        ->call('rowClick', $this->tenant1->id)
        ->assertDispatched('notify', function ($name, $data) {
            return $data['type'] === 'info' && $data['message'] === __('tenancy.already_in_workspace');
        });

    tenancy()->end();
});

it('redirects to the correct domain when switching workspace', function () {
    Livewire::actingAs($this->normalUser)
        ->test('tables.workspace-table')
        ->call('rowClick', $this->tenant1->id)
        ->assertRedirect('http://tenant1.test/dashboard');
});

it('shows role filter in impersonate user table', function () {
    Livewire::actingAs($this->superAdmin)
        ->test('tables.impersonate-user-table')
        ->assertSee(__('roles.role'));
});

it('hides self from impersonate user table', function () {
    Livewire::actingAs($this->superAdmin)
        ->test('tables.impersonate-user-table')
        ->assertDontSee($this->superAdmin->name)
        ->assertSee($this->normalUser->name);
});
