<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Validation\Rule;
use Exception;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 2;

    #[Locked]
    public string $teamUuid = '';

    // Form fields
    public string $name = '';
    public ?string $description = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Team $team): void
    {
        $this->authorize(Permissions::EDIT_TEAMS);

        $this->teamUuid = $team->uuid;
        $this->name = $team->name;
        $this->description = $team->description;

        $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.team')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.edit.title', ['type' => __('types.team'), 'name' => $this->name]);
    }

    /**
     * Get the team being edited.
     */
    protected function getTeam(): ?Team
    {
        return Team::where('uuid', $this->teamUuid)->first();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $team = $this->getTeam();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Team::class)->ignore($team?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Update the team.
     */
    public function updateTeam(): void
    {
        $this->validate();

        $team = $this->getTeam();

        if (!$team) {
            NotificationBuilder::make()
                ->title('pages.common.not_found', ['type' => __('types.team')])
                ->error()
                ->send();

            return;
        }

        try {
            $team->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            NotificationBuilder::make()
                ->title('pages.common.edit.success', ['name' => $team->label()])
                ->success()
                ->persist()
                ->send();

            $this->redirect(route('teams.show', $team->uuid), navigate: true);
        } catch (Exception $e) {
            NotificationBuilder::make()
                ->title('pages.common.edit.error', ['type' => __('types.team')])
                ->content($e->getMessage())
                ->error()
                ->send();
        }
    }
}; ?>

<section class="mx-auto w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title level="2"
                        class="mb-6">{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form wire:submit="updateTeam"
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

                {{-- Submit --}}
                <div class="divider"></div>
                <div class="flex justify-end gap-4">
                    <x-ui.button href="{{ route('teams.show', $teamUuid) }}"
                                 style="ghost"
                                 wire:navigate>{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button type="submit"
                                 variant="primary">
                        <x-ui.loading wire:loading
                                      wire:target="updateTeam"
                                      size="sm"></x-ui.loading>
                        {{ __('pages.common.edit.submit', ['type' => __('types.team')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
