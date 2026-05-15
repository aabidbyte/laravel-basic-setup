<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Subscription\SubscriptionStatus;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use App\Services\Features\FeatureResolver;
use App\Services\Subscriptions\SubscriptionPeriodCalculator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public Tenant $tenant;
    public ?Subscription $currentSubscription = null;

    public string|int|null $selectedPlanId = null;
    public string|int|null $selectedFeatureId = null;
    public ?string $overrideValue = null;
    public bool $overrideEnabled = true;
    public ?string $overrideStartsAt = null;
    public ?string $overrideEndsAt = null;
    public ?string $overrideReason = null;

    public function mount(Tenant $tenant): void
    {
        $this->authorize(Permissions::VIEW_SUBSCRIPTIONS());

        $this->tenant = $tenant;
        $this->refreshSubscription();
        $this->pageTitle = 'subscriptions.title';
        $this->pageSubtitle = 'subscriptions.subtitle';
        $this->selectedPlanId = $this->currentSubscription?->plan_id;
        $this->selectDefaultFeature();
    }

    public function subscribe(): void
    {
        $this->authorize(Permissions::CREATE_SUBSCRIPTIONS());

        $this->validate([
            'selectedPlanId' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::find($this->selectedPlanId);

        // Deactivate current active subscriptions
        Subscription::where('tenant_id', $this->tenant->tenant_id)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::CANCELED]);

        $period = app(SubscriptionPeriodCalculator::class)->forPlan($plan);

        Subscription::create([
            'tenant_id' => $this->tenant->tenant_id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => $period['starts_at'],
            'ends_at' => $period['ends_at'],
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('subscriptions.subscribed_successfully'),
        ]);

        $this->refreshSubscription();
    }

    public function updatedSelectedFeatureId(): void
    {
        $this->hydrateOverrideForm();
    }

    public function saveFeatureOverride(): void
    {
        $this->authorize(Permissions::EDIT_SUBSCRIPTIONS());

        $this->validate($this->featureOverrideRules());

        TenantFeatureOverride::query()->updateOrCreate([
            'tenant_id' => $this->tenant->tenant_id,
            'feature_id' => $this->selectedFeatureId,
        ], [
            'value' => $this->normalizedOverrideValue(),
            'enabled' => $this->overrideEnabled,
            'starts_at' => $this->overrideStartsAt ?: null,
            'ends_at' => $this->overrideEndsAt ?: null,
            'reason' => $this->normalizedOverrideReason(),
        ]);

        $this->refreshTenantOverrides();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('subscriptions.feature_override_saved'),
        ]);
    }

    public function deleteFeatureOverride(string $overrideUuid): void
    {
        $this->authorize(Permissions::EDIT_SUBSCRIPTIONS());

        TenantFeatureOverride::query()
            ->where('tenant_id', $this->tenant->tenant_id)
            ->where('uuid', $overrideUuid)
            ->firstOrFail()
            ->delete();

        $this->hydrateOverrideForm();
        $this->refreshTenantOverrides();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('subscriptions.feature_override_removed'),
        ]);
    }

    #[Computed]
    public function plans(): Collection
    {
        return Plan::where('is_active', true)->get();
    }

    #[Computed]
    public function featureOptions(): array
    {
        return Feature::query()
            ->where('is_active', true)
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (Feature $feature) => [$feature->id => $feature->label()])
            ->toArray();
    }

    #[Computed]
    public function currentPlanFeatures(): Collection
    {
        if (! $this->currentSubscription) {
            return collect();
        }

        return PlanFeature::query()
            ->with('feature')
            ->where('plan_id', $this->currentSubscription->plan_id)
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function tenantOverrides(): Collection
    {
        return TenantFeatureOverride::query()
            ->with('feature')
            ->where('tenant_id', $this->tenant->tenant_id)
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function effectiveFeatures(): Collection
    {
        return app(FeatureResolver::class)->effectiveFeatures($this->tenant);
    }

    public function getPageSubtitle(): ?string
    {
        return __('subscriptions.subtitle', ['name' => $this->tenant->name]);
    }

    private function refreshSubscription(): void
    {
        $this->currentSubscription = $this->tenant->currentSubscription()
            ->with('plan')
            ->first();
    }

    private function selectDefaultFeature(): void
    {
        if ($this->selectedFeatureId) {
            return;
        }

        $this->selectedFeatureId = Feature::query()
            ->where('is_active', true)
            ->orderBy('key')
            ->value('id');

        $this->hydrateOverrideForm();
    }

    private function hydrateOverrideForm(): void
    {
        $override = TenantFeatureOverride::query()
            ->where('tenant_id', $this->tenant->tenant_id)
            ->where('feature_id', $this->selectedFeatureId)
            ->first();

        $this->overrideValue = $override?->value === null ? null : (string) $override->value;
        $this->overrideEnabled = $override?->enabled ?? true;
        $this->overrideStartsAt = $override?->starts_at?->format('Y-m-d\TH:i');
        $this->overrideEndsAt = $override?->ends_at?->format('Y-m-d\TH:i');
        $this->overrideReason = $override?->reason;
    }

    private function refreshTenantOverrides(): void
    {
        unset($this->tenantOverrides);
        $this->hydrateOverrideForm();
    }

    private function featureOverrideRules(): array
    {
        return [
            'selectedFeatureId' => ['required', Rule::exists('central.features', 'id')],
            'overrideValue' => ['nullable', 'string', 'max:255'],
            'overrideEnabled' => ['boolean'],
            'overrideStartsAt' => ['nullable', 'date'],
            'overrideEndsAt' => ['nullable', 'date', 'after_or_equal:overrideStartsAt'],
            'overrideReason' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function normalizedOverrideValue(): mixed
    {
        if ($this->overrideValue === null || \trim($this->overrideValue) === '') {
            return null;
        }

        return \trim($this->overrideValue);
    }

    private function normalizedOverrideReason(): ?string
    {
        if ($this->overrideReason === null || \trim($this->overrideReason) === '') {
            return null;
        }

        return \trim($this->overrideReason);
    }
}; ?>

<x-layouts.page :title="__($pageTitle)"
                :subtitle="__($pageSubtitle, ['name' => $tenant->name])">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Current Subscription --}}
        <div class="space-y-8 lg:col-span-2">
            <x-ui.card title="{{ __('subscriptions.current_plan') }}">
                @if ($currentSubscription)
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold">{{ $currentSubscription->plan->name }}</h3>
                            <p class="text-base-content/60 text-sm">
                                {{ __('subscriptions.status') }}:
                                <span class="badge {{ $currentSubscription->status->color() }}">
                                    {{ $currentSubscription->status->label() }}
                                </span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ __('subscriptions.expires_at') }}</p>
                            <p class="text-base-content/60">
                                {{ $currentSubscription->ends_at?->format('Y-m-d') ?? __('subscriptions.no_expiry') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-ui.icon name="credit-card"
                                   size="lg"
                                   class="text-base-content/20 mx-auto mb-4" />
                        <p class="text-base-content/60">{{ __('subscriptions.no_active_subscription') }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="{{ __('subscriptions.history') }}">
                <livewire:tables.subscription-table :tenant="$tenant" />
            </x-ui.card>

            <x-ui.card title="{{ __('subscriptions.effective_features') }}"
                       description="{{ __('subscriptions.effective_features_description') }}">
                <div class="divide-base-200 divide-y">
                    @foreach ($this->effectiveFeatures as $resolvedFeature)
                        <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $resolvedFeature['feature']->label() }}</p>
                                <p class="text-base-content/60 text-xs">{{ $resolvedFeature['feature']->key }}</p>
                            </div>
                            <x-ui.badge :color="$resolvedFeature['enabled'] ? 'success' : 'ghost'"
                                        size="sm">
                                {{ app(FeatureValueNormalizer::class)->display($resolvedFeature['value']) }}
                            </x-ui.badge>
                            <x-ui.badge variant="outline"
                                        size="sm">
                                {{ __("features.sources.{$resolvedFeature['source']}") }}
                            </x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="{{ __('subscriptions.plan_features') }}"
                       description="{{ __('subscriptions.plan_features_description') }}">
                @if ($this->currentPlanFeatures->isNotEmpty())
                    <div class="divide-base-200 divide-y">
                        @foreach ($this->currentPlanFeatures as $planFeature)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $planFeature->feature->label() }}</p>
                                    <p class="text-base-content/60 text-xs">{{ $planFeature->feature->key }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <x-ui.badge :color="$planFeature->enabled ? 'success' : 'error'"
                                                size="sm">
                                        {{ $planFeature->enabled ? __('actions.activate') : __('actions.deactivate') }}
                                    </x-ui.badge>
                                    <x-ui.badge variant="outline"
                                                size="sm">
                                        {{ $planFeature->value ?? __('subscriptions.included') }}
                                    </x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center">
                        <x-ui.icon name="adjustments-horizontal"
                                   size="lg"
                                   class="text-base-content/20 mx-auto mb-3" />
                        <p class="text-base-content/60 text-sm">{{ __('subscriptions.no_plan_features') }}</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Subscribe/Upgrade --}}
        <div class="space-y-8">
            <x-ui.card title="{{ __('subscriptions.change_plan') }}">
                <x-ui.form wire:submit="subscribe">
                    <div class="space-y-4">
                        @foreach ($this->plans as $plan)
                            <label
                                   class="hover:bg-base-200 {{ $selectedPlanId === $plan->id ? 'border-primary bg-primary/5' : 'border-base-300' }} flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="radio"
                                           wire:model.live="selectedPlanId"
                                           value="{{ $plan->id }}"
                                           class="radio radio-primary" />
                                    <div>
                                        <p class="font-bold">{{ $plan->name }}</p>
                                        <p class="text-base-content/60 text-xs">{{ $plan->price }}
                                            {{ $plan->currency }} / {{ $plan->billing_cycle->label() }}
                                        </p>
                                    </div>
                                </div>
                                <span class="badge {{ $plan->tier->color() }}">{{ $plan->tier->label() }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        <x-ui.button type="submit"
                                     color="primary"
                                     class="w-full"
                                     icon="check-circle"
                                     wire:loading.attr="disabled">
                            {{ __('subscriptions.update_subscription') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            </x-ui.card>

            <x-ui.card title="{{ __('subscriptions.custom_features') }}"
                       description="{{ __('subscriptions.custom_features_description') }}">
                <x-ui.form wire:submit="saveFeatureOverride">
                    <x-ui.select label="{{ __('subscriptions.feature') }}"
                                 wire:model.live="selectedFeatureId"
                                 :options="$this->featureOptions"
                                 :searchable="false"
                                 required />

                    <x-ui.input label="{{ __('subscriptions.override_value') }}"
                                name="overrideValue"
                                wire:model="overrideValue"
                                placeholder="{{ __('subscriptions.override_value_placeholder') }}" />

                    <x-ui.toggle label="{{ __('subscriptions.override_enabled') }}"
                                 description="{{ __('subscriptions.override_enabled_description') }}"
                                 wire:model="overrideEnabled" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.input label="{{ __('subscriptions.starts_at') }}"
                                    name="overrideStartsAt"
                                    type="datetime-local"
                                    wire:model="overrideStartsAt" />

                        <x-ui.input label="{{ __('subscriptions.ends_at') }}"
                                    name="overrideEndsAt"
                                    type="datetime-local"
                                    wire:model="overrideEndsAt" />
                    </div>

                    <x-ui.input label="{{ __('subscriptions.reason') }}"
                                name="overrideReason"
                                wire:model="overrideReason"
                                placeholder="{{ __('subscriptions.reason_placeholder') }}" />

                    <x-ui.button type="submit"
                                 color="primary"
                                 class="w-full"
                                 wire:loading.attr="disabled">
                        <x-ui.icon name="check"
                                   size="sm" />
                        {{ __('subscriptions.save_feature_override') }}
                    </x-ui.button>
                </x-ui.form>

                @if ($this->tenantOverrides->isNotEmpty())
                    <div class="border-base-200 mt-6 border-t pt-4">
                        <div class="space-y-3">
                            @foreach ($this->tenantOverrides as $override)
                                <div class="border-base-200 rounded-box flex items-start justify-between gap-3 border p-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-medium">{{ $override->feature->label() }}</p>
                                            <x-ui.badge :color="$override->enabled ? 'success' : 'error'"
                                                        size="sm">
                                                {{ $override->enabled ? __('subscriptions.granted') : __('subscriptions.denied') }}
                                            </x-ui.badge>
                                        </div>
                                        <p class="text-base-content/60 mt-1 text-xs">
                                            {{ $override->value ?? __('subscriptions.boolean_override') }}
                                        </p>
                                        @if ($override->reason)
                                            <p class="text-base-content/60 mt-1 text-xs">{{ $override->reason }}</p>
                                        @endif
                                    </div>

                                    <x-ui.button type="button"
                                                 variant="ghost"
                                                 color="error"
                                                 size="sm"
                                                 circle
                                                 wire:click="deleteFeatureOverride('{{ $override->uuid }}')"
                                                 wire:loading.attr="disabled"
                                                 aria-label="{{ __('subscriptions.remove_feature_override') }}">
                                        <x-ui.icon name="trash"
                                                   size="sm" />
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.page>
