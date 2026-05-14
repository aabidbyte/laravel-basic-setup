<?php

declare(strict_types=1);

use App\Constants\Auth\Roles;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->superAdminRole = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
    $suffix = Str::random(4);
    $this->tenantA = Tenant::factory()->create(['id' => 'swa' . $suffix]);
    $this->tenantB = Tenant::factory()->create(['id' => 'swb' . $suffix]);
    $this->tenantA->domains()->create(['domain' => "swa-{$suffix}.test"]);
    $this->tenantB->domains()->create(['domain' => "swb-{$suffix}.test"]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->member = User::factory()->create();
    $this->member->tenants()->attach($this->tenantA->id);
});

it('allows super admins to switch to a tenant they are not attached to', function (): void {
    $domain = $this->tenantB->domains()->first()->domain;

    $this->actingAs($this->superAdmin)
        ->get(route('tenants.switch', $this->tenantB))
        ->assertRedirect('http://' . $domain . '/dashboard');
});

it('forbids members from switching to tenants they do not belong to', function (): void {
    $this->actingAs($this->member)
        ->get(route('tenants.switch', $this->tenantB))
        ->assertForbidden();
});
