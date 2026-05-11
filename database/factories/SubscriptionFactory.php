<?php

namespace Database\Factories;

use App\Enums\Subscription\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => $this->faker->randomElement(SubscriptionStatus::cases()),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
            'extras' => [],
        ];
    }
}
