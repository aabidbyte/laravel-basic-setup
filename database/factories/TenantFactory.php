<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = $this->faker->unique()->slug(1);

        return [
            'id' => $id,
            'name' => $this->faker->company(),
            'plan' => $this->faker->randomElement(['free', 'pro', 'enterprise']),
            'should_seed' => false,
        ];
    }

    /**
     * Configure the factory to create a domain for the tenant.
     */
    public function withDomain(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            $tenant->domains()->create([
                'domain' => $tenant->id . '.' . config('tenancy.central_domains.0'),
            ]);
        });
    }
}
