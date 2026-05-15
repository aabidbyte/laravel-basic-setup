<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Feature\FeatureKey;
use App\Enums\Plan\PlanBillingCycle;
use App\Enums\Plan\PlanTier;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\Features\FeatureValueNormalizer;
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
                'billing_cycle' => PlanBillingCycle::MONTHLY,
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
                'billing_cycle' => PlanBillingCycle::MONTHLY,
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
                'billing_cycle' => PlanBillingCycle::MONTHLY,
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
                'billing_cycle' => PlanBillingCycle::LIFETIME,
                'features' => [
                    ['key' => 'max_users', 'value' => 'Unlimited'],
                    ['key' => 'storage', 'value' => '1TB'],
                    ['key' => 'api_access', 'value' => 'Yes'],
                    ['key' => 'priority_support', 'value' => 'Yes'],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            $features = $plan['features'];
            $existing = Plan::query()
                ->where('name->en_US', $plan['name']['en_US'])
                ->first();

            if ($existing) {
                $existing->update($plan);
                $this->syncPlanFeatures($existing, $features);
            } else {
                $this->syncPlanFeatures(Plan::create($plan), $features);
            }
        }

        if (! \app()->environment('production')) {
            for ($i = 1; $i <= 5; $i++) {
                $plan = Plan::create([
                    'name' => [
                        'en_US' => "Dev Plan {$i}",
                        'fr_FR' => "Plan Dev {$i}",
                    ],
                    'tier' => \fake()->randomElement(PlanTier::cases()),
                    'price' => \fake()->randomFloat(2, 10, 200),
                    'billing_cycle' => \fake()->randomElement([PlanBillingCycle::MONTHLY, PlanBillingCycle::YEARLY]),
                    'features' => [['key' => 'dev_feature', 'value' => 'True']],
                    'is_active' => true,
                ]);

                $this->syncPlanFeatures($plan, [['key' => 'dev_feature', 'value' => 'True']]);
            }
        }
    }

    /**
     * @param  array<int, array{key: string, value: mixed}>  $features
     */
    private function syncPlanFeatures(Plan $plan, array $features): void
    {
        foreach ($features as $feature) {
            $featureModel = $this->featureForKey((string) $feature['key']);

            PlanFeature::query()->updateOrCreate([
                'plan_id' => $plan->id,
                'feature_id' => $featureModel->id,
            ], [
                'value' => $this->normalizedValue($featureModel, $feature['value'] ?? null),
                'enabled' => true,
            ]);
        }
    }

    private function featureForKey(string $key): Feature
    {
        $featureKey = FeatureKey::tryFrom($key);

        return Feature::query()->updateOrCreate([
            'key' => $key,
        ], [
            'name' => $featureKey?->nameTranslations() ?? [
                'en_US' => str($key)->replace('_', ' ')->title()->toString(),
                'fr_FR' => str($key)->replace('_', ' ')->title()->toString(),
            ],
            'type' => $featureKey?->valueType()?->value ?? 'string',
            'default_value' => $featureKey?->defaultValue(),
            'is_active' => true,
        ]);
    }

    private function normalizedValue(Feature $feature, mixed $value): mixed
    {
        return app(FeatureValueNormalizer::class)->normalize($feature, $value);
    }
}
