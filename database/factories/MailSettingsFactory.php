<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MailSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailSettingsFactory extends Factory
{
    protected $model = MailSettings::class;

    public function definition(): array
    {
        return [
            'provider' => 'smtp',
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => $this->faker->userName(),
            'password' => 'secret',
            'encryption' => 'tls',
            'from_address' => $this->faker->safeEmail(),
            'from_name' => $this->faker->name(),
            'is_active' => true,
        ];
    }

    public function forApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'settable_type' => 'app',
            'settable_id' => null,
        ]);
    }
}
