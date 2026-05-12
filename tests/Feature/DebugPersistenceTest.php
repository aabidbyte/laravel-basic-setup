<?php

use App\Models\Plan;

it('can persist a plan', function () {
    $plan = Plan::factory()->create();
    $this->assertDatabaseHas(Plan::class, ['uuid' => $plan->uuid]);
});
