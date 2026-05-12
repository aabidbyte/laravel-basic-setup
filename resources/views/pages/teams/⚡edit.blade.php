<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.team';

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 2;

    /**
     * Form fields
     */
    public string $name = '';
    public ?string $description = null;

    /**
     * Initialize the component.
     */
    public function mount(?Team $team = null): void
    {
        $this->authorizeAccess($team);
        $this->initializeUnifiedModel($team, fn ($t) => $this->loadExistingTeam($t), fn () => $this->prepareNewTeam());
        $this->updatePageHeader();
    }

    /**
     * Authorize access to the component.
     */
    protected function authorizeAccess(?Team $team): void
    {
        $permission = $team ? Permissions::EDIT_TEAMS() : Permissions::CREATE_TEAMS();
        $this->authorize($permission);
    }

    /**
     * Load existing team data into form fields.
     */
    protected function loadExistingTeam(Team $team): void
    {
        $this->model = $team;
        $this->name = $team->name;
        $this->description = $team->description;
    }

    /**
     * Prepare a new team model with defaults.
     */
    protected function prepareNewTeam(): void
    {
        $this->model = new Team();
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = 'pages.common.create.title';
            $this->pageSubtitle = 'pages.common.create.description';
        } else {
            $this->pageTitle = 'pages.common.edit.title';
            $this->pageSubtitle = 'pages.common.edit.description';
        }
    }

    /**
     * Override getPageTitle to provide type parameter.
     */
    public function getPageTitle(): string
    {
        $title = parent::getPageTitle();
        $params = ['type' => __($this->modelTypeLabel)];

        if (!$this->isCreateMode && $this->name) {
            $params['name'] = $this->name;
        }

        return __($title, $params);
    }

    /**
     * Override getPageSubtitle to provide type parameter.
     */
    public function getPageSubtitle(): ?string
    {
        $subtitle = parent::getPageSubtitle();
        return $subtitle ? __($subtitle, ['type' => __($this->modelTypeLabel)]) : null;
    }

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        $rules = [
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->isCreateMode) {
            $rules['name'] = ['required', 'string', 'max:255', Rule::unique(Team::class)];
        } else {
            $rules['name'] = ['required', 'string', 'max:255', Rule::unique(Team::class)->ignore($this->model->id)];
        }

        return $rules;
    }

    /**
     * Handle model creation.
     */
    public function create(): void
    {
        $this->validate();

        $team = Team::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->sendSuccessNotification($team, 'pages.common.create.success');
        $this->redirect(route('teams.index'), navigate: true);
    }

    /**
     * Handle model update.
     */
    public function save(): void
    {
        $this->validate();

        $this->model->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect(route('teams.show', $this->model->uuid), navigate: true);
    }

    /**
     * Get the URL to redirect back to.
     */
    #[Computed]
    public function cancelUrl(): string
    {
        return $this->getCancelUrl('teams.index', 'teams.show', $this->model);
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="team-form"
                     variant="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm" />
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <div class="mx-auto w-full max-w-4xl">
        <x-ui.card>
            <x-ui.form wire:submit="{{ $this->submitAction }}"
                       id="team-form">
                {{-- Basic Information --}}
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('teams.edit.basic_info') }}</x-ui.title>

                    <x-ui.input type="text"
                                wire:model="name"
                                name="name"
                                :label="__('teams.name')"
                                required
                                autofocus />

                    <x-ui.input type="textarea"
                                wire:model="description"
                                name="description"
                                :label="__('teams.description')"
                                rows="3" />
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
