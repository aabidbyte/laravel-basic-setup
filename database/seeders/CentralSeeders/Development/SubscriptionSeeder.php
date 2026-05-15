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
        $lifetimePlan = Plan::where('tier', PlanTier::LIFETIME)->first();

        if (! $lifetimePlan) {
            $this->command->error('Lifetime plan not found. Please run PlanSeeder first.');

            return;
        }

        $primaryOrganization = Tenant::whereHas('domains', function ($query): void {
            $query->where('domain', 'acme.laravel-basic-setup.test');
        })->first();

        if ($primaryOrganization) {
            $hasSubscription = Subscription::where('tenant_id', $primaryOrganization->tenant_id)->exists();

            if (! $hasSubscription) {
                Subscription::create([
                    'tenant_id' => $primaryOrganization->tenant_id,
                    'plan_id' => $lifetimePlan->id,
                    'status' => SubscriptionStatus::ACTIVE,
                    'starts_at' => now(),
                    'extras' => ['note' => 'Default lifetime plan for primary seeded organization'],
                ]);

                $this->command->info("Assigned Lifetime Plan to organization: {$primaryOrganization->name} ({$primaryOrganization->tenant_id})");
            }
        } else {
            $this->command->warn('No primary seeded organization found to assign plans to.');
        }

        $otherTenants = Tenant::where('tenant_id', '!=', $primaryOrganization?->tenant_id)->get();
        $randomPlans = Plan::where('tier', '!=', PlanTier::LIFETIME)->get();

        if ($otherTenants->isNotEmpty() && $randomPlans->isNotEmpty()) {
            foreach ($otherTenants as $tenant) {
                if (! Subscription::where('tenant_id', $tenant->tenant_id)->exists()) {
                    Subscription::create([
                        'tenant_id' => $tenant->tenant_id,
                        'plan_id' => $randomPlans->random()->id,
                        'status' => SubscriptionStatus::ACTIVE,
                        'starts_at' => now(),
                    ]);
                }
            }
        }
    }
}
