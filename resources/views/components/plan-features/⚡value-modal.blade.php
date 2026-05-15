<?php

declare(strict_types=1);

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\Features\FeatureValueNormalizer;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Plans\PlanFeatureSyncer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public ?string $planFeatureUuid = null;

    #[Locked]
    public ?string $planUuid = null;

    #[Locked]
    public ?string $featureUuid = null;

    public ?PlanFeature $planFeature = null;

    public ?Plan $plan = null;

    public ?Feature $feature = null;

    public ?string $value = null;

    public bool $enabled = true;

    public function mount(?string $planFeatureUuid = null, ?string $planUuid = null, ?string $featureUuid = null): void
    {
        $this->planFeatureUuid = $planFeatureUuid;
        $this->planUuid = $planUuid;
        $this->featureUuid = $featureUuid;

        $this->loadModels();
        $this->authorize(PolicyAbilities::UPDATE, $this->plan);

        $this->value = $this->planFeature?->value === null
            ? $this->stringValue($this->feature?->default_value)
            : $this->stringValue($this->planFeature->value);
        $this->enabled = $this->planFeature?->enabled ?? true;
    }

    protected function rules(): array
    {
        $valueRules = ['nullable', 'string', 'max:255'];

        if ($this->feature?->type === FeatureValueType::INTEGER) {
            $valueRules[] = 'integer';
        }

        if ($this->feature?->type === FeatureValueType::DECIMAL) {
            $valueRules[] = 'numeric';
        }

        if ($this->feature?->type === FeatureValueType::BOOLEAN) {
            $valueRules = ['required', Rule::in(['1', '0', 'true', 'false', 'yes', 'no', 'on', 'off', 'enabled', 'disabled'])];
        }

        return [
            'value' => $valueRules,
            'enabled' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();
        $this->authorize(PolicyAbilities::UPDATE, $this->plan);

        $syncer = app(PlanFeatureSyncer::class);

        if ($this->planFeature instanceof PlanFeature) {
            $syncer->update($this->planFeature, [
                'value' => $this->value,
                'enabled' => $this->enabled,
            ]);
        } else {
            $syncer->assign([
                'plan' => $this->plan,
                'feature' => $this->feature,
                'value' => $this->value,
                'enabled' => $this->enabled,
            ]);
        }

        $this->dispatch('plan-features-updated');
        $this->dispatch('datatable-close-modal');

        NotificationBuilder::make()
            ->title('features.plan_value_saved_successfully')
            ->success()
            ->send();
    }

    #[Computed]
    public function valuePreview(): string
    {
        return app(FeatureValueNormalizer::class)->display(
            app(FeatureValueNormalizer::class)->normalize($this->feature, $this->value),
        );
    }

    protected function loadModels(): void
    {
        if ($this->planFeatureUuid) {
            $this->planFeature = PlanFeature::query()
                ->with(['plan', 'feature'])
                ->where('uuid', $this->planFeatureUuid)
                ->firstOrFail();
            $this->plan = $this->planFeature->plan;
            $this->feature = $this->planFeature->feature;

            return;
        }

        $this->plan = Plan::query()->where('uuid', $this->planUuid)->firstOrFail();
        $this->feature = Feature::query()->where('uuid', $this->featureUuid)->firstOrFail();
    }

    protected function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}; ?>

<div class="space-y-5">
    <div class="space-y-1">
        <x-ui.title level="4">
            {{ __('features.plan_value_modal_title', ['feature' => $feature?->label()]) }}
        </x-ui.title>
        <p class="text-base-content/60 text-sm">
            {{ __('features.plan_value_modal_description', ['plan' => $plan?->label()]) }}
        </p>
    </div>

    <x-ui.form wire:submit="save"
               id="plan-feature-value-form">
        <div class="space-y-4">
            @if ($feature?->type === FeatureValueType::BOOLEAN)
                <x-ui.select label="{{ __('plans.feature_value') }}"
                             wire:model="value"
                             :options="[
                                 'true' => __('common.yes'),
                                 'false' => __('common.no'),
                             ]"
                             :searchable="false"
                             required />
            @else
                <x-ui.input label="{{ __('plans.feature_value') }}"
                            wire:model="value"
                            :type="$feature?->type === FeatureValueType::STRING ? 'text' : 'number'"
                            step="{{ $feature?->type === FeatureValueType::DECIMAL ? '0.01' : '1' }}"
                            placeholder="{{ __('plans.feature_value_placeholder') }}" />
            @endif

            <x-ui.toggle label="{{ __('plans.feature_enabled') }}"
                         wire:model="enabled"
                         color="success" />

            <div class="bg-base-200/50 border-base-300 rounded-md border px-3 py-2 text-sm">
                <span class="text-base-content/50">{{ __('features.normalized_value') }}:</span>
                <span class="font-medium">{{ $this->valuePreview }}</span>
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-ui.button type="button"
                             variant="ghost"
                             size="sm"
                             x-on:click="$dispatch('datatable-close-modal')">
                    {{ __('actions.cancel') }}
                </x-ui.button>
                <x-ui.button type="submit"
                             color="primary"
                             size="sm">
                    <x-ui.icon name="check"
                               size="xs" />
                    {{ __('actions.save') }}
                </x-ui.button>
            </div>
        </div>
    </x-ui.form>
</div>
