<?php

declare(strict_types=1);

namespace Database\Seeders\CentralSeeders\Development;

use App\Enums\Plan\PlanTier;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have the Lifetime Plan
        $lifetimePlan = Plan::where('tier', PlanTier::LIFETIME)->first();

        if (! $lifetimePlan) {
            $this->command->error('Lifetime plan not found. Please run PlanSeeder first.');

            return;
        }

        // 2. Find the first tenant (usually org1 from CentralUserSeeder)
        $firstTenant = Tenant::first();

        if ($firstTenant) {
            // Check if it already has a subscription
            $hasSubscription = Subscription::where('tenant_id', $firstTenant->id)->exists();

            if (! $hasSubscription) {
                Subscription::create([
                    'tenant_id' => $firstTenant->id,
                    'plan_id' => $lifetimePlan->id,
                    'status' => SubscriptionStatus::ACTIVE,
                    'starts_at' => now(),
                    'extras' => ['note' => 'Default lifetime plan for first tenant'],
                ]);

                $this->command->info("Assigned Lifetime Plan to tenant: {$firstTenant->name} ({$firstTenant->id})");
            }
        } else {
            $this->command->warn('No tenants found to assign plans to.');
        }

        // 3. Optionally assign some random plans to other tenants if they exist
        $otherTenants = Tenant::where('id', '!=', $firstTenant?->id)->get();
        $randomPlans = Plan::where('tier', '!=', PlanTier::LIFETIME)->get();

        if ($otherTenants->isNotEmpty() && $randomPlans->isNotEmpty()) {
            foreach ($otherTenants as $tenant) {
                if (! Subscription::where('tenant_id', $tenant->id)->exists()) {
                    Subscription::create([
                        'tenant_id' => $tenant->id,
                        'plan_id' => $randomPlans->random()->id,
                        'status' => SubscriptionStatus::ACTIVE,
                        'starts_at' => now(),
                    ]);
                }
            }
        }
    }
}
