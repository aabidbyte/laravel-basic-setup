<?php

declare(strict_types=1);

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Feature\FeatureValueType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Feature;
use App\Services\Features\FeatureValueNormalizer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public ?string $modelTypeLabel = 'features.singular';

    public ?Illuminate\Database\Eloquent\Model $model = null;

    public ?string $key = null;
    public ?string $name_en_US = null;
    public ?string $name_fr_FR = null;
    public ?string $description_en_US = null;
    public ?string $description_fr_FR = null;
    public string $type = 'string';
    public ?string $default_value = null;
    public bool $is_active = true;

    public function mount(?Feature $feature = null): void
    {
        $feature = $feature?->exists ? $feature : null;

        $this->authorizeAccess($feature);
        $this->initializeUnifiedModel($feature, fn ($model) => $this->loadExistingFeature($model), fn () => $this->prepareNewFeature());
        $this->updatePageHeader();
    }

    protected function authorizeAccess(?Feature $feature): void
    {
        $ability = $feature ? PolicyAbilities::UPDATE : PolicyAbilities::CREATE;
        $this->authorize($ability, $feature ?? Feature::class);
    }

    protected function loadExistingFeature(Feature $feature): void
    {
        $this->model = $feature;
        $this->key = $feature->key;
        $this->name_en_US = $feature->getTranslation('name', 'en_US', false);
        $this->name_fr_FR = $feature->getTranslation('name', 'fr_FR', false);
        $this->description_en_US = $feature->getTranslation('description', 'en_US', false);
        $this->description_fr_FR = $feature->getTranslation('description', 'fr_FR', false);
        $this->type = $feature->type?->value ?? FeatureValueType::STRING->value;
        $this->default_value = $feature->default_value === null ? null : (string) $feature->default_value;
        $this->is_active = $feature->is_active;
    }

    protected function prepareNewFeature(): void
    {
        $this->model = new Feature();
    }

    protected function updatePageHeader(): void
    {
        $this->pageTitle = $this->isCreateMode ? 'features.create_title' : 'features.edit_title';
        $this->pageSubtitle = $this->isCreateMode ? 'features.create_subtitle' : 'features.edit_subtitle';
    }

    public function getPageSubtitle(): ?string
    {
        if ($this->isCreateMode) {
            return __('features.create_subtitle');
        }

        return __('features.edit_subtitle', ['name' => $this->model?->label()]);
    }

    protected function rules(): array
    {
        $featureId = $this->model?->getKey();

        return [
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('central.features', 'key')->ignore($featureId)],
            'name_en_US' => ['required', 'string', 'max:255'],
            'name_fr_FR' => ['required', 'string', 'max:255'],
            'description_en_US' => ['nullable', 'string', 'max:1000'],
            'description_fr_FR' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', Rule::in(collect(FeatureValueType::cases())->pluck('value')->toArray())],
            'default_value' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->validate();

        $this->model = Feature::create($this->prepareData());

        $this->sendSuccessNotification($this->model, 'pages.common.create.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    public function save(): void
    {
        $this->validate();

        $this->model->update($this->prepareData());

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    protected function prepareData(): array
    {
        return [
            'key' => $this->key,
            'name' => [
                'en_US' => $this->name_en_US,
                'fr_FR' => $this->name_fr_FR,
            ],
            'description' => [
                'en_US' => $this->description_en_US,
                'fr_FR' => $this->description_fr_FR,
            ],
            'type' => $this->type,
            'default_value' => app(FeatureValueNormalizer::class)->normalize($this->type, $this->default_value),
            'is_active' => $this->is_active,
        ];
    }

    #[Computed]
    public function typeOptions(): array
    {
        return collect(FeatureValueType::cases())
            ->mapWithKeys(fn (FeatureValueType $type) => [$type->value => $type->label()])
            ->toArray();
    }

    #[Computed]
    public function cancelUrl(): string
    {
        return route('features.index');
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
                         form="feature-form"
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
                       id="feature-form">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <x-ui.input label="{{ __('features.fields.key') }}"
                                    wire:model="key"
                                    required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('features.fields.type') }}"
                                     wire:model="type"
                                     :options="$this->typeOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('features.fields.name_en_US') }}"
                                    wire:model="name_en_US"
                                    required />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('features.fields.name_fr_FR') }}"
                                    wire:model="name_fr_FR"
                                    required />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.input label="{{ __('features.fields.default_value') }}"
                                    wire:model="default_value" />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('features.fields.description_en_US') }}"
                                    wire:model="description_en_US"
                                    type="textarea"
                                    rows="4" />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('features.fields.description_fr_FR') }}"
                                    wire:model="description_fr_FR"
                                    type="textarea"
                                    rows="4" />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.toggle label="{{ __('features.fields.is_active') }}"
                                     wire:model="is_active"
                                     color="success" />
                    </div>
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
