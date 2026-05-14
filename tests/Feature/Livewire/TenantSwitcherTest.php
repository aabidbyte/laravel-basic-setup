<?php

use App\Constants\Auth\Roles;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    // Create roles
    $this->superAdminRole = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
    $this->adminRole = Role::firstOrCreate(['name' => Roles::ADMIN]);

    // Create tenants with random IDs to avoid database name collisions
    // And random domains to avoid collisions
    $suffix = Str::random(4);
    $this->tenant1 = Tenant::factory()->create(['name' => 'Tenant 1', 'id' => 't1' . $suffix, 'plan' => 'pro']);
    $this->tenant2 = Tenant::factory()->create(['name' => 'Tenant 2', 'id' => 't2' . $suffix]);
    $this->tenant3 = Tenant::factory()->create(['name' => 'Tenant 3', 'id' => 't3' . $suffix]);

    // Create a domain for each tenant to allow redirection
    $this->tenant1->domains()->create(['domain' => "tenant1-{$suffix}.test"]);
    $this->tenant2->domains()->create(['domain' => "tenant2-{$suffix}.test"]);
    $this->tenant3->domains()->create(['domain' => "tenant3-{$suffix}.test"]);

    // Create users
    $this->superAdmin = User::factory()->create(['name' => 'Super Admin']);
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->normalUser = User::factory()->create(['name' => 'Normal User']);
    $this->normalUser->tenants()->attach($this->tenant1);
});

it('shows only associated tenants for normal users', function () {
    Livewire::actingAs($this->normalUser)
        ->test('tables.tenant-table')
        ->assertSee('Tenant 1')
        ->assertDontSee('Tenant 2')
        ->assertDontSee('Tenant 3');
});

it('shows all tenants for super admins', function () {
    Livewire::actingAs($this->superAdmin)
        ->test('tables.tenant-table')
        ->assertSee('Tenant 1')
        ->assertSee('Tenant 2')
        ->assertSee('Tenant 3');
});

it('hides current tenant from the list', function () {
    // Simulate being in tenant1 context
    tenancy()->initialize($this->tenant1);

    Livewire::actingAs($this->superAdmin)
        ->test('tables.tenant-table')
        ->assertDontSee('Tenant 1')
        ->assertSee('Tenant 2')
        ->assertSee('Tenant 3');

    tenancy()->end();
});

it('protects from switching to the current tenant', function () {
    // Simulate being in tenant1 context
    tenancy()->initialize($this->tenant1);

    Livewire::actingAs($this->superAdmin)
        ->test('tables.tenant-table', ['isSwitcher' => true])
        ->call('handleRowClick', $this->tenant1->id)
        ->assertDispatched('notify');

    // Primary assertion: tenant context remains unchanged
    expect(tenant('id'))->toBe($this->tenant1->id);

    tenancy()->end();
});

it('redirects to the correct domain when switching tenant', function () {
    $domain = $this->tenant1->domains()->first()->domain;
    Livewire::actingAs($this->normalUser)
        ->test('tables.tenant-table', ['isSwitcher' => true])
        ->call('handleRowClick', $this->tenant1->id)
        ->assertRedirect("http://{$domain}/dashboard");
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

it('shows tenant metadata in the tenant switcher trigger', function () {
    tenancy()->initialize($this->tenant1);

    Livewire::actingAs($this->superAdmin)
        ->test('tenancy.tenant-switcher')
        ->assertSee('Tenant 1')
        ->assertSee('pro')
        ->assertSee($this->tenant1->domains()->first()->domain)
        ->assertSee('T'); // Initials

    tenancy()->end();
});

it('shows impersonation details in the banner', function () {
    $impersonatedUser = User::factory()->create(['name' => 'Impersonated User']);

    $this->actingAs($this->superAdmin)
        ->withSession(['impersonator_id' => $this->superAdmin->id])
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('Impersonated User')
        ->assertSee(__('tenancy.system_override_mode'))
        ->assertSee(__('tenancy.stop_impersonating'));
});
