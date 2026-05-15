<?php

declare(strict_types=1);

namespace App\Services\Features;

use App\Enums\Feature\FeatureKey;
use App\Models\Feature;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use Illuminate\Support\Collection;

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
        return $this->resolve($tenant, $key)['value'];
    }

    /**
     * @return array{feature: Feature|null, value: mixed, source: string|null, enabled: bool}
     */
    public function resolve(Tenant $tenant, FeatureKey|string $key): array
    {
        $feature = $this->feature($key);

        if (! $feature?->is_active) {
            return ['feature' => $feature, 'value' => null, 'source' => null, 'enabled' => false];
        }

        $override = $this->activeOverride($tenant, $feature);

        if ($override) {
            return [
                'feature' => $feature,
                'value' => $override->enabled ? ($override->value ?? true) : false,
                'source' => 'tenant_override',
                'enabled' => $override->enabled,
            ];
        }

        $planFeature = $this->planFeature($tenant, $feature);

        if ($planFeature) {
            return [
                'feature' => $feature,
                'value' => $planFeature->enabled ? ($planFeature->value ?? true) : false,
                'source' => 'plan',
                'enabled' => $planFeature->enabled,
            ];
        }

        return [
            'feature' => $feature,
            'value' => $feature->default_value,
            'source' => 'default',
            'enabled' => $feature->default_value !== false && $feature->default_value !== null,
        ];
    }

    /**
     * @return Collection<int, array{feature: Feature, value: mixed, source: string|null, enabled: bool}>
     */
    public function effectiveFeatures(Tenant $tenant): Collection
    {
        return Feature::query()
            ->where('is_active', true)
            ->orderBy('key')
            ->get()
            ->map(fn (Feature $feature) => $this->resolve($tenant, $feature->key))
            ->filter(fn (array $resolved) => $resolved['feature'] instanceof Feature)
            ->values();
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
