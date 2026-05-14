<?php

declare(strict_types=1);

namespace Database\Factories\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_layout' => false,
            'type' => EmailTemplateType::SYSTEM,
            'status' => EmailTemplateStatus::DRAFT,
            'is_system' => false,
            'is_default' => false,
            'all_teams' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailTemplateStatus::PUBLISHED,
        ]);
    }

    public function layout(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_layout' => true,
        ]);
    }
}
