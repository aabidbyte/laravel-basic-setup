<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Validation\Rule;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 2;

    // Form fields
    public string $name = '';
    public ?string $description = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::CREATE_TEAMS);
        $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.team')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.create.title', ['type' => __('types.team')]);
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Team::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Create the team.
     */
    public function createTeam(): void
    {
        $this->validate();

        try {
            $team = Team::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            NotificationBuilder::make()
                ->title('pages.common.create.success', ['name' => $team->label()])
                ->success()
                ->persist()
                ->send();

            $this->redirect(route('teams.index'), navigate: true);
        } catch (\Exception $e) {
            NotificationBuilder::make()
                ->title('pages.common.create.error', ['type' => __('types.team')])
                ->content($e->getMessage())
                ->error()
                ->send();
        }
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title
                level="2"
                class="mb-6"
            >{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form
                wire:submit="createTeam"
                class="space-y-6"
            >
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70"
                    >{{ __('teams.create.basic_info') }}</x-ui.title>

                    <x-ui.input
                        type="text"
                        wire:model="name"
                        name="name"
                        :label="__('teams.name')"
                        required
                        autofocus
                    ></x-ui.input>

                    <x-ui.input
                        type="textarea"
                        wire:model="description"
                        name="description"
                        :label="__('teams.description')"
                        rows="3"
                    ></x-ui.input>
                </div>

                {{-- Submit --}}
                <div class="divider"></div>
                <div class="flex justify-end gap-4">
                    <x-ui.button
                        href="{{ route('teams.index') }}"
                        style="ghost"
                        wire:navigate
                    >{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        <x-ui.loading
                            wire:loading
                            wire:target="createTeam"
                            size="sm"
                        ></x-ui.loading>
                        {{ __('pages.common.create.submit', ['type' => __('types.team')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
