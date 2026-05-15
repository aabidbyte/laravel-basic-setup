<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = $this->faker->unique()->slug(2);

        return [
            'key' => $key,
            'name' => [
                'en_US' => str($key)->replace('-', ' ')->title()->toString(),
                'fr_FR' => str($key)->replace('-', ' ')->title()->toString(),
            ],
            'description' => [
                'en_US' => $this->faker->sentence(),
                'fr_FR' => $this->faker->sentence(),
            ],
            'type' => FeatureValueType::STRING,
            'default_value' => null,
            'is_active' => true,
        ];
    }
}
