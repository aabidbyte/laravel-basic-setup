<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    DB::connection('central')->statement('SET FOREIGN_KEY_CHECKS=0');

    // Create permission and role
    $permission = Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
    $viewerRole->givePermissionTo($permission);

    $this->user = User::factory()->create();
    $this->user->assignRole(Roles::SUPER_ADMIN);
    $this->user->assignRole($viewerRole);

    $this->tenantId = 'tu' . Str::random(4);

    DB::table('tenants')->insert([
        'tenant_id' => $this->tenantId,
        'slug' => 'users-table-' . Str::random(4),
        'name' => 'Users Table Tenant',
        'plan' => 'free',
        'color' => 'neutral',
        'should_seed' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    attachUserToTenantForUsersTableTest($this->user, $this->tenantId);
});

afterEach(function () {
    DB::connection('central')->statement('SET FOREIGN_KEY_CHECKS=1');
});

test('search filters results', function () {
    $john = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $jane = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    attachUserToTenantForUsersTableTest($john, $this->tenantId);
    attachUserToTenantForUsersTableTest($jane, $this->tenantId);

    Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->set('search', 'John')
        ->assertSee('John')
        ->assertSee('Doe')
        ->assertDontSee('Jane Smith');
});

test('sort toggles direction', function () {
    $users = User::factory()->count(2)->sequence(
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    )->create();

    $users->each(fn (User $user) => attachUserToTenantForUsersTableTest($user, $this->tenantId));

    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table');

    // Component defaults to sortBy='created_at' and sortDirection='desc' (from config)
    // First click on 'name' (different column) sets sortBy='name' and sortDirection='asc'
    $component->call('sort', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');

    // Second click on 'name' (same column) toggles to desc
    $component->call('sort', 'name')
        ->assertSet('sortDirection', 'desc');

    // Third click toggles back to asc
    $component->call('sort', 'name')
        ->assertSet('sortDirection', 'asc');
});

test('bulk select page sets selected to current page UUIDs', function () {
    $users = User::factory()->count(5)->create();

    $users->each(fn (User $user) => attachUserToTenantForUsersTableTest($user, $this->tenantId));

    $component = Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->set('perPage', 3);

    $component->call('toggleSelectAll')
        ->assertSet('isAllSelected', true);

    // Should have selected users from current page
    expect($component->get('selected'))->toBeArray()
        ->and(\count($component->get('selected')))->toBeLessThanOrEqual(3);
});

test('clear selection empties selected array', function () {
    $users = User::factory()->count(3)->create();
    $uuids = $users->pluck('uuid')->toArray();

    $users->each(fn (User $user) => attachUserToTenantForUsersTableTest($user, $this->tenantId));

    Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->set('selected', $uuids)
        ->call('clearSelection')
        ->assertSet('selected', []);
});

test('datatable shows status and last login columns', function () {
    User::query()->where('id', '!=', $this->user->id)->delete();
    $this->user->update([
        'is_active' => true,
        'last_login_at' => now()->subDays(1),
    ]);

    Livewire::actingAs($this->user)
        ->test('tables.user-table')
        ->assertSee(__('table.users.status'))
        ->assertSee(__('table.users.last_login_at'))
        ->assertSee(__('users.active'))
        ->assertSee('1 day ago');
});

function attachUserToTenantForUsersTableTest(User $user, string $tenantId): void
{
    DB::table('tenant_user')->insert([
        'tenant_id' => $tenantId,
        'user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
