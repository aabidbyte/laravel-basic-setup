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
                'name' => 'Basic',
                'tier' => PlanTier::BASIC,
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'features' => ['max_users' => 5, 'storage' => '1GB'],
            ],
            [
                'name' => 'Pro',
                'tier' => PlanTier::PRO,
                'price' => 29.00,
                'billing_cycle' => 'monthly',
                'features' => ['max_users' => 20, 'storage' => '10GB', 'api_access' => true],
            ],
            [
                'name' => 'Enterprise',
                'tier' => PlanTier::ENTERPRISE,
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'features' => ['max_users' => -1, 'storage' => '100GB', 'api_access' => true, 'priority_support' => true],
            ],
            [
                'name' => 'Lifetime',
                'tier' => PlanTier::LIFETIME,
                'price' => 499.00,
                'billing_cycle' => 'lifetime',
                'features' => ['max_users' => -1, 'storage' => '1TB', 'api_access' => true, 'priority_support' => true],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
