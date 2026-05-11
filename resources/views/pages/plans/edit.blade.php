<?php

declare(strict_types=1);

use App\Enums\Plan\PlanTier;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $modelTypeLabel = 'plans.plan';
    public ?Plan $model = null;

    /**
     * Form fields
     */
    public string $name = '';
    public string $tier = 'basic';
    public float $price = 0.00;
    public string $currency = 'USD';
    public string $billing_cycle = 'monthly';
    public array $features = [];
    public bool $is_active = true;

    /**
     * Initialize the component.
     */
    public function mount(?Plan $plan = null): void
    {
        $this->initializeUnifiedModel($plan, fn ($p) => $this->loadExistingPlan($p), fn () => $this->prepareNewPlan());
        $this->updatePageHeader();
    }

    /**
     * Load existing plan data.
     */
    protected function loadExistingPlan(Plan $plan): void
    {
        $this->model = $plan;
        $this->name = $plan->name;
        $this->tier = $plan->tier->value;
        $this->price = (float) $plan->price;
        $this->currency = $plan->currency;
        $this->billing_cycle = $plan->billing_cycle;
        $this->features = $plan->features ?? [];
        $this->is_active = $plan->is_active;
    }

    /**
     * Prepare new plan.
     */
    protected function prepareNewPlan(): void
    {
        $this->model = new Plan();
        $this->features = [
            ['key' => 'max_users', 'value' => '5'],
            ['key' => 'storage', 'value' => '1GB']
        ];
    }

    /**
     * Update page header.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = $this->isCreateMode ? 'plans.create_title' : 'plans.edit_title';
        $this->pageSubtitle = $this->isCreateMode ? 'plans.create_subtitle' : 'plans.edit_subtitle';
    }

    /**
     * Handle form submission.
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'tier' => $this->tier,
            'price' => $this->price,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'features' => $this->features,
            'is_active' => $this->is_active,
        ];

        if ($this->isCreateMode) {
            $this->model = Plan::create($data);
            $messageKey = 'pages.common.create.success';
        } else {
            $this->model->update($data);
            $messageKey = 'pages.common.edit.success';
        }

        $this->sendSuccessNotification($this->model, $messageKey);
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['required', 'string', 'in:basic,pro,enterprise,lifetime,one_time_deal'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly,one_time,lifetime'],
            'features' => ['array'],
            'is_active' => ['boolean'],
        ];
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
        return collect(PlanTier::cases())
            ->mapWithKeys(fn ($tier) => [$tier->value => $tier->label()])
            ->toArray();
    }

    #[Computed]
    public function cancelUrl(): string
    {
        return route('plans.index');
    }
}; ?>

<x-layouts.page backHref="{{ $this->cancelUrl }}">
    <div class="max-w-4xl">
        <x-ui.card>
            <x-ui.form wire:submit="save">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-ui.input label="{{ __('plans.name') }}" wire:model="name" required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('plans.tier') }}" wire:model="tier" :options="$this->tierOptions" required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('plans.billing_cycle') }}" wire:model="billing_cycle" 
                            :options="[
                                'monthly' => __('plans.cycles.monthly'),
                                'yearly' => __('plans.cycles.yearly'),
                                'one_time' => __('plans.cycles.one_time'),
                                'lifetime' => __('plans.cycles.lifetime')
                            ]" required />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('plans.price') }}" wire:model="price" type="number" step="0.01" required>
                            <x-slot:append>
                                <span class="text-base-content/40 text-xs px-2">{{ $currency }}</span>
                            </x-slot:append>
                        </x-ui.input>
                    </div>

                    <div>
                        <x-ui.input label="{{ __('plans.currency') }}" wire:model="currency" required />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.toggle label="{{ __('plans.is_active') }}" wire:model="is_active" color="success" />
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold">{{ __('plans.features') }}</h3>
                            <x-ui.button variant="ghost" size="sm" wire:click="addFeature">
                                <x-ui.icon name="plus" size="xs" />
                                {{ __('plans.add_feature') }}
                            </x-ui.button>
                        </div>

                        <div class="space-y-2">
                            @foreach($features as $index => $feature)
                                <div class="flex items-center gap-2" wire:key="feature-{{ $index }}">
                                    <x-ui.input placeholder="Key (e.g. max_users)" wire:model="features.{{ $index }}.key" class="flex-1" />
                                    <x-ui.input placeholder="Value (e.g. 10)" wire:model="features.{{ $index }}.value" class="flex-1" />
                                    <x-ui.button variant="ghost" size="sm" color="error" wire:click="removeFeature({{ $index }})">
                                        <x-ui.icon name="trash" size="xs" />
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button variant="ghost" href="{{ $this->cancelUrl }}" wire:navigate>
                        {{ __('actions.cancel') }}
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        {{ $this->submitButtonText }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
