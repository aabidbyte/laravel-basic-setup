<?php

namespace Database\Factories;

use App\Enums\Plan\PlanTier;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Plan',
            'tier' => $this->faker->randomElement(PlanTier::cases()),
            'price' => $this->faker->randomFloat(2, 0, 100),
            'currency' => 'USD',
            'billing_cycle' => $this->faker->randomElement(['monthly', 'yearly', 'one_time', 'lifetime']),
            'features' => [
                ['key' => 'max_users', 'value' => (string) $this->faker->numberBetween(1, 100)],
                ['key' => 'storage', 'value' => $this->faker->numberBetween(1, 10) . 'GB'],
            ],
            'is_active' => true,
        ];
    }
}
