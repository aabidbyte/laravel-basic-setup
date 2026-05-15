<?php

declare(strict_types=1);

namespace App\Services\Features;

use App\Enums\Feature\FeatureKey;
use App\Models\Feature;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;

class FeatureResolver
{
    public function allows(Tenant $tenant, FeatureKey|string $key): bool
    {
        $value = $this->value($tenant, $key);

        if (\is_bool($value)) {
            return $value;
        }

        if (\is_numeric($value)) {
            return (float) $value > 0;
        }

        if (\is_string($value)) {
            return ! \in_array(\strtolower(\trim($value)), ['', '0', 'false', 'no', 'off', 'disabled'], true);
        }

        return $value !== null;
    }

    public function value(Tenant $tenant, FeatureKey|string $key): mixed
    {
        $feature = $this->feature($key);

        if (! $feature?->is_active) {
            return null;
        }

        $override = $this->activeOverride($tenant, $feature);

        if ($override) {
            return $override->enabled ? ($override->value ?? true) : false;
        }

        $planFeature = $this->planFeature($tenant, $feature);

        if ($planFeature) {
            return $planFeature->enabled ? ($planFeature->value ?? true) : false;
        }

        return $feature->default_value;
    }

    private function feature(FeatureKey|string $key): ?Feature
    {
        return Feature::query()
            ->where('key', $this->keyValue($key))
            ->first();
    }

    private function activeOverride(Tenant $tenant, Feature $feature): ?TenantFeatureOverride
    {
        return TenantFeatureOverride::query()
            ->active()
            ->where('tenant_id', $tenant->tenant_id)
            ->where('feature_id', $feature->id)
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }

    private function planFeature(Tenant $tenant, Feature $feature): ?PlanFeature
    {
        $subscription = $tenant->currentSubscription()
            ->with([
                'plan.planFeatures' => fn ($query) => $query->where('feature_id', $feature->id),
            ])
            ->first();

        return $subscription?->plan?->planFeatures->first();
    }

    private function keyValue(FeatureKey|string $key): string
    {
        if ($key instanceof FeatureKey) {
            return $key->value;
        }

        return $key;
    }
}
