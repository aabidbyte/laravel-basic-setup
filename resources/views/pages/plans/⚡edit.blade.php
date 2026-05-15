<?php

declare(strict_types=1);

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Plan\PlanBillingCycle;
use App\Enums\Plan\PlanTier;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\Features\FeatureValueNormalizer;
use App\Services\Plans\PlanFeatureSyncer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public ?string $modelTypeLabel = 'plans.plan';

    public ?Illuminate\Database\Eloquent\Model $model = null;

    /**
     * Form fields
     */
    public ?string $name = null;
    public ?string $tier = 'basic';
    public ?float $price = 0.0;
    public ?string $currency = 'USD';
    public ?string $billing_cycle = 'monthly';
    public array $features = [];
    public bool $is_active = true;

    /**
     * Initialize the component.
     */
    public function mount(?Plan $plan = null): void
    {
        $this->authorizeAccess($plan);
        $this->initializeUnifiedModel($plan, fn ($p) => $this->loadExistingPlan($p), fn () => $this->prepareNewPlan());
        $this->updatePageHeader();
    }

    /**
     * Authorize access based on mode.
     */
    protected function authorizeAccess(?Plan $plan): void
    {
        $ability = $plan ? PolicyAbilities::UPDATE : PolicyAbilities::CREATE;
        $this->authorize($ability, $plan ?? Plan::class);
    }

    /**
     * Load existing plan data.
     */
    protected function loadExistingPlan(Plan $plan): void
    {
        $this->model = $plan;
        $this->name = (string) $plan->name;
        $this->tier = $plan->tier?->value;
        $this->price = (float) $plan->price;
        $this->currency = (string) $plan->currency;
        $this->billing_cycle = $plan->billing_cycle?->value ?? PlanBillingCycle::MONTHLY->value;
        $this->features = $this->featureRowsForPlan($plan);
        $this->is_active = (bool) $plan->is_active;
    }

    /**
     * Prepare new plan.
     */
    protected function prepareNewPlan(): void
    {
        $this->model = new Plan();
        $this->features = [];
    }

    /**
     * Update page header.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = $this->isCreateMode ? 'plans.create_title' : 'plans.edit_title';
        $this->pageSubtitle = $this->isCreateMode ? 'plans.create_subtitle' : 'plans.edit_subtitle';
    }

    public function getPageSubtitle(): ?string
    {
        if ($this->isCreateMode) {
            return __('plans.create_subtitle');
        }

        return __('plans.edit_subtitle', ['name' => $this->model?->name]);
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['required', 'string', Rule::in(collect(PlanTier::cases())->pluck('value')->toArray())],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'string', Rule::in(collect(PlanBillingCycle::cases())->pluck('value')->toArray())],
            'features' => ['array'],
            'features.*.feature_id' => ['required', 'integer', 'distinct', Rule::exists('central.features', 'id')],
            'features.*.value' => ['nullable', 'string', 'max:255'],
            'features.*.enabled' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Prepare data for saving.
     */
    protected function prepareData(): array
    {
        return [
            'name' => $this->name,
            'tier' => $this->tier,
            'price' => $this->price,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'features' => $this->isCreateMode ? $this->legacyFeatureRows() : $this->model->features,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Create a new plan.
     */
    public function create(): void
    {
        $this->validate();

        $this->model = Plan::create($this->prepareData());
        $this->syncPlanFeatures();

        $this->sendSuccessNotification($this->model, 'pages.common.create.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Save existing plan.
     */
    public function save(): void
    {
        $this->validate();

        $this->model->update($this->prepareData());
        app(PlanFeatureSyncer::class)->syncLegacyFeatures($this->model->fresh());

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Add a feature row.
     */
    public function addFeature(): void
    {
        $this->features[] = ['feature_id' => null, 'value' => null, 'enabled' => true];
    }

    /**
     * Remove a feature row.
     */
    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    #[Computed]
    public function tierOptions(): array
    {
        return collect(PlanTier::cases())->mapWithKeys(fn ($tier) => [$tier->value => $tier->label()])->toArray();
    }

    #[Computed]
    public function billingCycleOptions(): array
    {
        return collect(PlanBillingCycle::cases())->mapWithKeys(fn ($cycle) => [$cycle->value => $cycle->label()])->toArray();
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
    public function cancelUrl(): string
    {
        return route('plans.index');
    }

    protected function featureRowsForPlan(Plan $plan): array
    {
        $rows = $plan->planFeatures()
            ->with('feature')
            ->get()
            ->map(fn (PlanFeature $planFeature) => [
                'feature_id' => $planFeature->feature_id,
                'value' => $planFeature->value === null ? null : (string) $planFeature->value,
                'enabled' => $planFeature->enabled,
            ])
            ->values()
            ->toArray();

        if ($rows !== []) {
            return $rows;
        }

        return collect($plan->features ?? [])
            ->map(function (array $feature) {
                $featureModel = Feature::query()->where('key', $feature['key'] ?? null)->first();

                return $featureModel ? [
                    'feature_id' => $featureModel->id,
                    'value' => $feature['value'] ?? null,
                    'enabled' => true,
                ] : null;
            })
            ->filter()
            ->values()
            ->toArray();
    }

    protected function syncPlanFeatures(): void
    {
        $featureIds = [];

        foreach ($this->features as $featureRow) {
            $feature = Feature::query()->find($featureRow['feature_id'] ?? null);

            if (! $feature) {
                continue;
            }

            $featureIds[] = $feature->id;

            app(PlanFeatureSyncer::class)->assign([
                'plan' => $this->model,
                'feature' => $feature,
                'value' => app(FeatureValueNormalizer::class)->normalize($feature, $featureRow['value'] ?? null),
                'enabled' => (bool) ($featureRow['enabled'] ?? true),
            ]);
        }

        PlanFeature::query()
            ->where('plan_id', $this->model->id)
            ->whereNotIn('feature_id', $featureIds)
            ->delete();
    }

    protected function legacyFeatureRows(): array
    {
        return collect($this->features)
            ->map(function (array $featureRow) {
                $feature = Feature::query()->find($featureRow['feature_id'] ?? null);

                if (! $feature) {
                    return null;
                }

                return [
                    'key' => $feature->key,
                    'value' => app(FeatureValueNormalizer::class)->normalize($feature, $featureRow['value'] ?? null),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}; ?>

<x-layouts.page backHref="{{ $this->cancelUrl }}">
    <x-slot:bottomActions>
        <div class="flex items-center justify-end gap-3">
            <x-ui.button :href="$this->cancelUrl"
                         wire:navigate
                         variant="ghost"
                         size="sm">
                <x-ui.icon name="x-mark"
                           size="sm" />
                {{ __('actions.cancel') }}
            </x-ui.button>

            <x-ui.button type="submit"
                         form="plan-form"
                         color="primary"
                         size="sm">
                <x-ui.icon name="check"
                           size="sm" />
                {{ $this->submitButtonText }}
            </x-ui.button>
        </div>
    </x-slot:bottomActions>

    <div class="mx-auto max-w-4xl">
        <x-ui.card>
            <x-ui.form wire:submit="{{ $this->submitAction }}"
                       id="plan-form">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-ui.input label="{{ __('plans.name') }}"
                                    wire:model="name"
                                    required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('plans.tier') }}"
                                     wire:model="tier"
                                     :options="$this->tierOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('plans.billing_cycle') }}"
                                     wire:model="billing_cycle"
                                     :options="$this->billingCycleOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('plans.price') }}"
                                    wire:model="price"
                                    type="number"
                                    step="0.01"
                                    required>
                            <x-slot:append>
                                <span
                                      class="text-base-content/40 bg-base-200 border-base-300 flex h-full items-center border-l px-3 text-xs">
                                    {{ $currency }}
                                </span>
                            </x-slot:append>
                        </x-ui.input>
                    </div>

                    <div>
                        <x-ui.input label="{{ __('plans.currency') }}"
                                    wire:model="currency"
                                    required />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.toggle label="{{ __('plans.is_active') }}"
                                     wire:model="is_active"
                                     color="success" />
                    </div>

                    <div class="space-y-4 md:col-span-2">
                        @if ($this->isCreateMode || ! $model?->uuid)
                            <div class="flex items-center justify-between">
                                <x-ui.title level="4"
                                            class="text-base-content/70">
                                    {{ __('plans.features') }}
                                </x-ui.title>
                                <x-ui.button variant="ghost"
                                             size="sm"
                                             wire:click="addFeature"
                                             type="button">
                                    <x-ui.icon name="plus"
                                               size="xs" />
                                    {{ __('plans.add_feature') }}
                                </x-ui.button>
                            </div>

                            <div class="space-y-3">
                                @foreach ($features as $index => $feature)
                                    <div class="bg-base-200/50 border-base-300 grid grid-cols-1 gap-3 rounded-lg border p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]"
                                         wire:key="feature-{{ $index }}">
                                        <x-ui.select label="{{ __('plans.feature') }}"
                                                     wire:model="features.{{ $index }}.feature_id"
                                                     :options="$this->featureOptions"
                                                     :searchable="false" />
                                        <x-ui.input placeholder="{{ __('plans.feature_value_placeholder') }}"
                                                    label="{{ __('plans.feature_value') }}"
                                                    wire:model="features.{{ $index }}.value" />
                                        <x-ui.toggle label="{{ __('plans.feature_enabled') }}"
                                                     wire:model="features.{{ $index }}.enabled" />
                                        <x-ui.button variant="ghost"
                                                     size="sm"
                                                     color="error"
                                                     wire:click="removeFeature({{ $index }})"
                                                     type="button"
                                                     class="btn-square">
                                            <x-ui.icon name="trash"
                                                       size="xs" />
                                        </x-ui.button>
                                    </div>
                                @endforeach

                                @if (empty($features))
                                    <div
                                         class="text-base-content/40 border-base-300 rounded-lg border-2 border-dashed py-6 text-center">
                                        {{ __('plans.no_features_added') }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="space-y-2">
                                <x-ui.title level="4"
                                            class="text-base-content/70">
                                    {{ __('plans.assigned_features') }}
                                </x-ui.title>
                                <p class="text-base-content/60 text-sm">
                                    {{ __('plans.assigned_features_description') }}
                                </p>
                            </div>

                            <livewire:tables.plan-feature-assignment-table :planUuid="$model->uuid"
                                                                           :key="'plan-'.$model->uuid.'-assigned-features'" />

                            <div class="space-y-2 pt-2">
                                <x-ui.title level="4"
                                            class="text-base-content/70">
                                    {{ __('plans.available_features') }}
                                </x-ui.title>
                                <p class="text-base-content/60 text-sm">
                                    {{ __('plans.available_features_description') }}
                                </p>
                            </div>

                            <livewire:tables.plan-assignable-feature-table :planUuid="$model->uuid"
                                                                           :key="'plan-'.$model->uuid.'-available-features'" />
                        @endif
                    </div>
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
