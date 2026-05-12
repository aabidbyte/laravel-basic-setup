<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Plan\PlanTier;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => [
                    'en_US' => 'Basic',
                    'fr_FR' => 'Basique',
                ],
                'tier' => PlanTier::BASIC,
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    ['key' => 'max_users', 'value' => '5'],
                    ['key' => 'storage', 'value' => '1GB'],
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Pro',
                    'fr_FR' => 'Pro',
                ],
                'tier' => PlanTier::PRO,
                'price' => 29.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    ['key' => 'max_users', 'value' => '20'],
                    ['key' => 'storage', 'value' => '10GB'],
                    ['key' => 'api_access', 'value' => 'Yes'],
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Enterprise',
                    'fr_FR' => 'Entreprise',
                ],
                'tier' => PlanTier::ENTERPRISE,
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    ['key' => 'max_users', 'value' => 'Unlimited'],
                    ['key' => 'storage', 'value' => '100GB'],
                    ['key' => 'api_access', 'value' => 'Yes'],
                    ['key' => 'priority_support', 'value' => 'Yes'],
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Lifetime',
                    'fr_FR' => 'À Vie',
                ],
                'tier' => PlanTier::LIFETIME,
                'price' => 499.00,
                'billing_cycle' => 'lifetime',
                'features' => [
                    ['key' => 'max_users', 'value' => 'Unlimited'],
                    ['key' => 'storage', 'value' => '1TB'],
                    ['key' => 'api_access', 'value' => 'Yes'],
                    ['key' => 'priority_support', 'value' => 'Yes'],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            // Find by name in English or current locale
            $existing = Plan::query()
                ->where('name->en_US', $plan['name']['en_US'])
                ->first();

            if ($existing) {
                $existing->update($plan);
            } else {
                Plan::create($plan);
            }
        }

        // Seed some random plans if in development
        if (! \app()->environment('production')) {
            for ($i = 1; $i <= 5; $i++) {
                Plan::create([
                    'name' => [
                        'en_US' => "Dev Plan {$i}",
                        'fr_FR' => "Plan Dev {$i}",
                    ],
                    'tier' => \fake()->randomElement(PlanTier::cases()),
                    'price' => \fake()->randomFloat(2, 10, 200),
                    'billing_cycle' => \fake()->randomElement(['monthly', 'yearly']),
                    'features' => [['key' => 'dev_feature', 'value' => 'True']],
                    'is_active' => true,
                ]);
            }
        }
    }
}
