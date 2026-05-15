<?php

namespace Database\Factories;

use App\Models\TeamPermission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamPermission>
 */
class TeamPermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['view', 'invite', 'remove', 'manage']);
        $entity = fake()->randomElement(['team_members', 'team_roles', 'team_settings']);

        return [
            'name' => "{$action} {$entity}",
            'display_name' => "{$action} {$entity}",
            'description' => fake()->sentence(),
            'entity' => $entity,
            'action' => $action,
        ];
    }
}
