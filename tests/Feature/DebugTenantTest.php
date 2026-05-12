<?php

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Str;

it('can persist a tenant', function () {
    $plan = Plan::factory()->create();
    $tenantId = 'debug-' . Str::random(8);
    $tenant = Tenant::create([
        'id' => $tenantId,
        'name' => 'Debug Tenant',
        'plan' => $plan->uuid,
    ]);
    $this->assertDatabaseHas(Tenant::class, ['id' => $tenantId]);
});
