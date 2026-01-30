<?php

use App\Constants\Auth\Permissions;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Validation\Rule;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 2;

    public ?Team $model = null;

    // Form fields
    public string $name = '';
    public ?string $description = null;

    public function mount(?Team $team = null): void
    {
        $this->authorizeAccess($team);
        $this->initializeUnifiedModel($team, fn($t) => $this->loadExistingTeam($t), fn() => $this->prepareNewTeam());
        $this->updatePageHeader();
    }

    protected function authorizeAccess(?Team $team): void
    {
        $permission = $team ? Permissions::EDIT_TEAMS() : Permissions::CREATE_TEAMS();

        $this->authorize($permission);
    }

    protected function loadExistingTeam(Team $team): void
    {
        $this->model = $team;
        $this->name = $team->name;
        $this->description = $team->description;
    }

    protected function prepareNewTeam(): void
    {
        $this->model = new Team();
    }

    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => __('types.team')]);
            $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.team')]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => __('types.team'), 'name' => $this->name]);
            $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.team')]);
        }
    }

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

    public function getCancelUrlProperty(): string
    {
        return $this->isCreateMode ? route('teams.index') : route('teams.show', $this->model->uuid);
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="team-form"
                     color="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm"></x-ui.loading>
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <section class="mx-auto max-w-4xl">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.form wire:submit="{{ $this->submitAction }}"
                           id="team-form"
                           class="space-y-6">
                    {{-- Basic Information --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('teams.edit.basic_info') }}</x-ui.title>

                        <x-ui.input type="text"
                                    wire:model="name"
                                    name="name"
                                    :label="__('teams.name')"
                                    required
                                    autofocus></x-ui.input>

                        <x-ui.input type="textarea"
                                    wire:model="description"
                                    name="description"
                                    :label="__('teams.description')"
                                    rows="3"></x-ui.input>
                    </div>
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>
