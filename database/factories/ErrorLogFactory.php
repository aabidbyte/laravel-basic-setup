<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ErrorLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ErrorLogFactory extends Factory
{
    protected $model = ErrorLog::class;

    public function definition(): array
    {
        return [
            'reference_id' => 'ERR-' . strtoupper(Str::random(8)),
            'exception_class' => 'Exception',
            'message' => $this->faker->sentence(),
            'stack_trace' => $this->faker->text(),
            'url' => $this->faker->url(),
            'method' => 'GET',
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
