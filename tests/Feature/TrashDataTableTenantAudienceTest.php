<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\Tables\TrashDataTable;
use App\Models\ErrorLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    Permission::firstOrCreate(['name' => Permissions::VIEW_ERROR_LOGS()]);
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(Roles::SUPER_ADMIN);
    $this->superAdmin->assignPermission(Permissions::VIEW_USERS(), Permissions::VIEW_ERROR_LOGS());
});

test('users trash table uses tenant membership audience filters', function (): void {
    $tenant = Tenant::factory()->create(['id' => 'trash-users-' . Str::random(8)]);
    $tenantUser = User::factory()->create(['name' => 'Trashed Tenant User']);
    $centralUser = User::factory()->create(['name' => 'Trashed Central User']);

    $tenant->users()->attach($tenantUser);
    $tenantUser->delete();
    $centralUser->delete();

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'users'])
        ->set('search', 'Trashed Tenant User')
        ->assertSee('Trashed Tenant User');

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'users'])
        ->set('search', 'Trashed Central User')
        ->assertDontSee('Trashed Central User');

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'users'])
        ->set('filters.tenant_id', TrashDataTable::CENTRAL_RECORDS_FILTER)
        ->set('search', 'Trashed Central User')
        ->assertSee('Trashed Central User')
        ->assertDontSee('Trashed Tenant User');
});

test('error log trash table uses direct tenant key audience filters', function (): void {
    $tenant = Tenant::factory()->create(['id' => 'trash-logs-' . Str::random(8)]);

    $tenantError = ErrorLog::create([
        'reference_id' => 'ERR-TRASH-TENANT',
        'exception_class' => RuntimeException::class,
        'message' => 'Trashed tenant error log',
        'stack_trace' => 'Trace',
        'tenant_id' => $tenant->tenant_id,
        'tenant_name' => $tenant->name,
    ]);

    $centralError = ErrorLog::create([
        'reference_id' => 'ERR-TRASH-CENTRAL',
        'exception_class' => RuntimeException::class,
        'message' => 'Trashed central error log',
        'stack_trace' => 'Trace',
    ]);

    $tenantError->delete();
    $centralError->delete();

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'error-logs'])
        ->set('search', 'Trashed tenant error log')
        ->assertSee('Trashed tenant error log');

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'error-logs'])
        ->set('search', 'Trashed central error log')
        ->assertDontSee('Trashed central error log');

    Livewire::actingAs($this->superAdmin)
        ->test('tables.trash-data-table', ['entityType' => 'error-logs'])
        ->set('filters.tenant_id', TrashDataTable::CENTRAL_RECORDS_FILTER)
        ->set('search', 'Trashed central error log')
        ->assertSee('Trashed central error log')
        ->assertDontSee('Trashed tenant error log');
});
