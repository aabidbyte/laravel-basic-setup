<?php

declare(strict_types=1);

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Plan\PlanTier;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Plan;
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
        $this->billing_cycle = (string) $plan->billing_cycle;
        $this->features = $plan->features ?? [];
        // Ensure features are in the correct [key, value] format for the form
        if (! empty($this->features) && ! isset($this->features[0]['key'])) {
            $formattedFeatures = [];
            foreach ($this->features as $key => $value) {
                if (is_array($value) && isset($value['key'])) {
                    $formattedFeatures[] = $value;
                } else {
                    $formattedFeatures[] = ['key' => (string) $key, 'value' => (string) $value];
                }
            }
            $this->features = $formattedFeatures;
        }
        $this->is_active = (bool) $plan->is_active;
    }

    /**
     * Prepare new plan.
     */
    protected function prepareNewPlan(): void
    {
        $this->model = new Plan();
        $this->features = [['key' => 'max_users', 'value' => '5'], ['key' => 'storage', 'value' => '1GB']];
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
            'billing_cycle' => ['required', 'string', Rule::in(['monthly', 'yearly', 'one_time', 'lifetime'])],
            'features' => ['array'],
            'features.*.key' => ['required_with:features.*.value', 'string'],
            'features.*.value' => ['required_with:features.*.key', 'string'],
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
            'features' => array_values(array_filter($this->features, fn ($f) => !empty($f['key']))),
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

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Add a feature row.
     */
    public function addFeature(): void
    {
        $this->features[] = ['key' => '', 'value' => ''];
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
    public function cancelUrl(): string
    {
        return route('plans.index');
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
                                     :options="[
                                         'monthly' => __('plans.cycles.monthly'),
                                         'yearly' => __('plans.cycles.yearly'),
                                         'one_time' => __('plans.cycles.one_time'),
                                         'lifetime' => __('plans.cycles.lifetime'),
                                     ]"
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
                                <div class="bg-base-200/50 border-base-300 flex items-center gap-3 rounded-lg border p-3"
                                     wire:key="feature-{{ $index }}">
                                    <x-ui.input placeholder="{{ __('plans.feature_key_placeholder') }}"
                                                wire:model="features.{{ $index }}.key"
                                                class="flex-1" />
                                    <x-ui.input placeholder="{{ __('plans.feature_value_placeholder') }}"
                                                wire:model="features.{{ $index }}.value"
                                                class="flex-1" />
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
                    </div>
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
