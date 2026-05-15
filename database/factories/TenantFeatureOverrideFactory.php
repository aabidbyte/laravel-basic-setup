<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantFeatureOverride>
 */
class TenantFeatureOverrideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => fn (): string => Tenant::factory()->create()->tenant_id,
            'feature_id' => Feature::factory(),
            'value' => $this->faker->word(),
            'enabled' => true,
            'starts_at' => null,
            'ends_at' => null,
            'reason' => $this->faker->sentence(),
        ];
    }
}
