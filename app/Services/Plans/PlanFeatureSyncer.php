<?php

declare(strict_types=1);

namespace App\Services\Plans;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\Features\FeatureValueNormalizer;

class PlanFeatureSyncer
{
    public function __construct(
        private readonly FeatureValueNormalizer $normalizer,
    ) {}

    /**
     * @param  array{plan: Plan, feature: Feature, value?: mixed, enabled?: bool}  $data
     */
    public function assign(array $data): PlanFeature
    {
        $planFeature = PlanFeature::query()
            ->withTrashed()
            ->firstOrNew([
                'plan_id' => $data['plan']->id,
                'feature_id' => $data['feature']->id,
            ]);

        $planFeature->fill([
            'value' => $this->normalizer->normalize($data['feature'], $data['value'] ?? null),
            'enabled' => (bool) ($data['enabled'] ?? true),
        ]);

        if ($planFeature->trashed()) {
            $planFeature->restore();
        }

        $planFeature->save();

        $this->syncLegacyFeatures($data['plan']->fresh());

        return $planFeature;
    }

    /**
     * @param  array{value?: mixed, enabled?: bool}  $data
     */
    public function update(PlanFeature $planFeature, array $data): PlanFeature
    {
        $planFeature->loadMissing(['feature', 'plan']);
        $planFeature->update([
            'value' => $this->normalizer->normalize($planFeature->feature, $data['value'] ?? null),
            'enabled' => (bool) ($data['enabled'] ?? true),
        ]);

        $this->syncLegacyFeatures($planFeature->plan);

        return $planFeature->fresh();
    }

    public function remove(PlanFeature $planFeature): void
    {
        $planFeature->loadMissing('plan');
        $plan = $planFeature->plan;

        $planFeature->delete();
        $this->syncLegacyFeatures($plan);
    }

    public function syncLegacyFeatures(?Plan $plan): void
    {
        if (! $plan) {
            return;
        }

        $features = $plan->planFeatures()
            ->with('feature')
            ->get()
            ->map(fn (PlanFeature $planFeature) => [
                'key' => $planFeature->feature?->key,
                'value' => $planFeature->enabled ? ($planFeature->value ?? true) : false,
            ])
            ->filter(fn (array $feature) => $feature['key'] !== null)
            ->values()
            ->toArray();

        $plan->forceFill(['features' => $features])->save();
    }
}
