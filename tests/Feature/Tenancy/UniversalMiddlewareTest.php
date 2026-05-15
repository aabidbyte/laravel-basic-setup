<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;

test('web routes initialize tenancy on tenant domains', function (): void {
    $tenant = Tenant::factory()->create([
        'id' => 'tenant-domain-check',
        'name' => 'Tenant Domain Check',
        'plan' => 'pro',
    ]);
    $tenant->domains()->create([
        'domain' => 'tenant-domain-check.test',
    ]);

    $user = User::factory()->create();
    $user->tenants()->attach($tenant->tenant_id);

    $this->actingAs($user)
        ->withHeader('Host', 'tenant-domain-check.test')
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Tenant Domain Check')
        ->assertSee('Pro');
});
