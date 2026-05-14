<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Team;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.team';

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    #[Locked]
    public ?Team $team = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Team $team): void
    {
        $this->authorize(Permissions::VIEW_TEAMS());

        $this->team = $team;
        $this->updatePageHeader();
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = 'pages.common.show.title';
        $this->pageSubtitle = 'pages.common.show.subtitle';
    }

    /**
     * Override getPageTitle to provide dynamic parameters.
     */
    public function getPageTitle(): string
    {
        $title = parent::getPageTitle();
        return __($title, [
            'name' => $this->team->name,
            'type' => __($this->modelTypeLabel),
        ]);
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
     * Delete the team.
     */
    public function deleteTeam(): void
    {
        $this->authorize(Permissions::DELETE_TEAMS());

        $name = $this->team->name;
        $this->team->delete();

        $this->sendSuccessNotification(null, 'pages.common.messages.deleted', ['name' => $name]);

        $this->redirect(route('teams.index'), navigate: true);
    }
}; ?>

<x-layouts.page backHref="{{ route('teams.index') }}">
    <x-slot:topActions>
        @can(Permissions::EDIT_TEAMS())
            <x-ui.button href="{{ route('teams.edit', $team->uuid) }}"
                         wire:navigate
                         color="primary"
                         size="sm"
                         icon="pencil">
                {{ __('actions.edit') }}
            </x-ui.button>
        @endcan

        @can(Permissions::DELETE_TEAMS())
            <x-ui.button x-on:click="confirmModal({
                         title: @js(__('actions.delete')),
                         message: @js(__('actions.confirm_delete')),
                         callback: 'confirm-delete-team'
                     })"
                         color="error"
                         size="sm"
                         icon="trash">
                {{ __('actions.delete') }}
            </x-ui.button>
        @endcan
    </x-slot:topActions>

    <div class="mx-auto max-w-6xl space-y-8"
         x-on:confirm-delete-team.window="$wire.deleteTeam()">
        {{-- Team Details Card --}}
        <x-ui.card title="{{ __('teams.show.basic_info') }}">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('teams.name') }}</span>
                    <p class="text-lg font-semibold">{{ $team->name }}</p>
                </div>
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('teams.uuid') }}</span>
                    <p class="font-mono text-sm">{{ $team->uuid }}</p>
                </div>
                <div class="md:col-span-2">
                    <span class="text-base-content/60 text-sm">{{ __('teams.description') }}</span>
                    <p class="text-base-content">{{ $team->description ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('fields.color') }}</span>
                    <div class="mt-1">
                        <x-ui.badge :color="$team->color"
                                    size="sm">{{ __("fields.colors.{$team->color}") }}</x-ui.badge>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Team Members --}}
        <x-ui.card title="{{ __('teams.members') }}">
            <livewire:tables.team-user-table :team-uuid="$team->uuid"
                                             lazy />
        </x-ui.card>
    </div>
</x-layouts.page>
