<?php

namespace Database\Factories;

use App\Enums\Ui\ThemeColorTypes;
use App\Models\TeamRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamRole>
 */
class TeamRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
            'display_name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color' => fake()->randomElement(ThemeColorTypes::values()),
            'is_admin' => false,
            'is_default' => false,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
