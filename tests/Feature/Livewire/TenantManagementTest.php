<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

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

it('shows tenant user counts and supports tenant row clicks', function () {
    $tenantId = 'table-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Table Tenant',
        'plan' => $this->plan->uuid,
    ]);
    $tenant->users()->attach([
        User::factory()->create()->id,
        User::factory()->create()->id,
    ]);

    Livewire::actingAs($this->user)
        ->test('tables.tenant-table')
        ->assertSee('Table Tenant')
        ->assertSee('2')
        ->call('handleRowClick', $tenant->tenant_id)
        ->assertRedirect(route('tenants.show', $tenant->tenant_id));
});

it('can access the tenant show page', function () {
    $tenantId = 'test-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Test Tenant',
        'plan' => $this->plan->uuid,
    ]);

    actingAs($this->user)
        ->get(route('tenants.show', $tenant->tenant_id))
        ->assertOk()
        ->assertSee('Test Tenant')
        ->assertSee(__('tenancy.overview'))
        ->assertSee(__('tenancy.switch'))
        ->assertSee(__('actions.edit'))
        ->assertSee(__('actions.delete'))
        ->assertDontSeeLivewire('tables.tenant-user-assignment-table')
        ->assertDontSeeLivewire('tables.tenant-assignable-user-table')
        ->assertDontSeeLivewire('tables.domain-table');

    Livewire::actingAs($this->user)
        ->test('pages::tenants.show', ['tenant' => $tenant])
        ->set('activeTab', 'users')
        ->assertSeeLivewire('tables.tenant-user-assignment-table')
        ->assertSeeLivewire('tables.tenant-assignable-user-table');
});

it('can render the tenant domains table', function () {
    $tenantId = 'domains-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Domain Tenant',
        'plan' => $this->plan->uuid,
    ]);
    $tenant->domains()->create(['domain' => "{$tenantId}.test"]);

    Livewire::actingAs($this->user)
        ->test('tables.domain-table', ['tenantId' => $tenant->tenant_id])
        ->assertHasNoErrors();
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
        ->get(route('tenants.settings.edit', $tenant->tenant_id))
        ->assertOk()
        ->assertSee(__('tenancy.edit_tenant'))
        ->assertSeeLivewire('tables.domain-table');
});

it('can create a tenant', function () {
    $tenantSlug = 'new-' . Str::random(8);
    $domain = "{$tenantSlug}.example.test";

    Livewire::actingAs($this->user)
        ->test('pages::tenants.edit', ['tenant' => null])
        ->set('slug', $tenantSlug)
        ->set('name', 'New Tenant')
        ->set('plan', $this->plan->uuid)
        ->set('color', 'primary')
        ->set('primary_domain', $domain)
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas(Tenant::class, [
        'slug' => $tenantSlug,
        'name' => 'New Tenant',
        'plan' => $this->plan->uuid,
        'color' => 'primary',
    ]);
    $this->assertDatabaseHas('domains', [
        'domain' => $domain,
    ]);
});

it('can update a tenant', function () {
    $tenantId = 'upd-' . Str::random(8);
    $domain = "{$tenantId}.old.test";
    $updatedDomain = "{$tenantId}.new.test";
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Original Name',
        'plan' => $this->plan->uuid,
    ]);
    $tenant->domains()->create(['domain' => $domain]);

    Livewire::actingAs($this->user)
        ->test('pages::tenants.edit', ['tenant' => $tenant])
        ->set('name', 'Updated Name')
        ->set('color', 'secondary')
        ->set('primary_domain', $updatedDomain)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenants.show', $tenant->tenant_id));

    $this->assertDatabaseHas('tenants', [
        'tenant_id' => $tenantId,
        'name' => 'Updated Name',
        'color' => 'secondary',
    ]);
    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenant->tenant_id,
        'domain' => $updatedDomain,
    ]);
});

it('can add a domain from the tenant edit page', function () {
    $tenantId = 'add-domain-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Domain Add Tenant',
        'plan' => $this->plan->uuid,
    ]);
    $tenant->domains()->create(['domain' => "{$tenantId}.test"]);

    Livewire::actingAs($this->user)
        ->test('pages::tenants.edit', ['tenant' => $tenant])
        ->set('newDomain', "{$tenantId}.extra.test")
        ->call('addDomain')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenant->tenant_id,
        'domain' => "{$tenantId}.extra.test",
    ]);
});
