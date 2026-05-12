<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['is_super_admin' => true]);
    $role = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
    $this->user->assignRole($role);

    // Ensure permissions exist
    Permission::firstOrCreate(['name' => Permissions::VIEW_TENANTS()]);
    Permission::firstOrCreate(['name' => Permissions::CREATE_TENANTS()]);
    Permission::firstOrCreate(['name' => Permissions::EDIT_TENANTS()]);
    Permission::firstOrCreate(['name' => Permissions::DELETE_TENANTS()]);

    $this->user->assignPermission(
        Permissions::VIEW_TENANTS(),
        Permissions::CREATE_TENANTS(),
        Permissions::EDIT_TENANTS(),
        Permissions::DELETE_TENANTS(),
    );

    // Create a plan for all tests
    $this->plan = Plan::factory()->create(['is_active' => true]);
});

it('can access the tenants index page', function () {
    actingAs($this->user)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertSeeLivewire('tables.tenant-table');
});

it('can access the tenant show page', function () {
    $tenantId = 'test-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Test Tenant',
        'plan' => $this->plan->uuid,
    ]);

    actingAs($this->user)
        ->get(route('tenants.show', $tenant->id))
        ->assertOk()
        ->assertSee('Test Tenant')
        ->assertSeeLivewire('tables.tenant-user-table');
});

it('can access the tenant create page', function () {
    actingAs($this->user)
        ->get(route('tenants.create'))
        ->assertOk()
        ->assertSee(__('tenancy.create_tenant'));
});

it('can access the tenant edit page', function () {
    $tenantId = 'edit-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Edit Tenant',
        'plan' => $this->plan->uuid,
    ]);

    actingAs($this->user)
        ->get(route('tenants.settings.edit', $tenant->id))
        ->assertOk()
        ->assertSee(__('tenancy.edit_tenant'));
});

it('can create a tenant', function () {
    $tenantId = 'new-' . Str::random(8);

    Livewire::actingAs($this->user)
        ->test('pages::tenants.edit', ['tenant' => null])
        ->set('tenant_id', $tenantId)
        ->set('name', 'New Tenant')
        ->set('plan', $this->plan->uuid)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenants.show', $tenantId));

    $this->assertDatabaseHas(Tenant::class, [
        'id' => $tenantId,
        'name' => 'New Tenant',
        'plan' => $this->plan->uuid,
    ]);
});

it('can update a tenant', function () {
    $tenantId = 'upd-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Original Name',
        'plan' => $this->plan->uuid,
    ]);

    Livewire::actingAs($this->user)
        ->test('pages::tenants.edit', ['tenant' => $tenant])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenants.show', $tenant->id));

    $this->assertDatabaseHas('tenants', [
        'id' => $tenantId,
        'name' => 'Updated Name',
    ]);
});
