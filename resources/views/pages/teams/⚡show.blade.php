<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'card';

    #[Locked]
    public string $teamUuid = '';

    public ?Team $team = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Team $team): void
    {
        $this->authorize(Permissions::VIEW_TEAMS);

        $this->teamUuid = $team->uuid;
        $this->team = $team;

        $this->pageSubtitle = __('pages.common.show.description', ['type' => __('types.team')]);
    }

    public function getPageTitle(): string
    {
        return $this->team?->label() ?? __('types.team');
    }
}; ?>

<section class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Team Details Card --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-start justify-between">
                <x-ui.title level="2"
                            class="mb-6">{{ $this->getPageTitle() }}</x-ui.title>

                @can(Permissions::EDIT_TEAMS)
                    <x-ui.button href="{{ route('teams.edit', $teamUuid) }}"
                                 wire:navigate
                                 variant="ghost"
                                 class="gap-2">
                        <x-ui.icon name="pencil"
                                   size="sm"></x-ui.icon>
                        {{ __('actions.edit') }}
                    </x-ui.button>
                @endcan
            </div>

            <div class="space-y-4">
                <x-ui.title level="3"
                            class="text-base-content/70">{{ __('teams.show.basic_info') }}</x-ui.title>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-base-content/60 text-sm font-medium">{{ __('teams.name') }}</dt>
                        <dd class="text-base-content font-semibold">{{ $team->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60 text-sm font-medium">{{ __('teams.description') }}</dt>
                        <dd class="text-base-content">{{ $team->description ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Team Members --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title level="3"
                        class="mb-4">{{ __('teams.members') }}</x-ui.title>

            <livewire:tables.team-user-table :team-uuid="$teamUuid"
                                             lazy></livewire:tables.team-user-table>
        </div>
    </div>

    {{-- Back Button --}}
    <div class="flex justify-start">
        <x-ui.button href="{{ route('teams.index') }}"
                     wire:navigate
                     variant="ghost"
                     class="gap-2">
            <x-ui.icon name="arrow-left"
                       size="sm"></x-ui.icon>
            {{ __('actions.back_to_list') }}
        </x-ui.button>
    </div>
</section>
