@php
    use App\Constants\Auth\Permissions;

    setPageTitle(
        __('pages.common.index.title', ['type' => __('types.teams')]),
        __('pages.common.index.description', ['type_plural' => __('types.teams')]),
    );
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        <x-slot:topActions>
            @can(Permissions::CREATE_TEAMS())
                <x-ui.button href="{{ route('teams.edit') }}"
                             wire:navigate
                             color="primary"
                             icon="plus">
                    {{ __('pages.common.create.title', ['type' => __('types.team')]) }}
                </x-ui.button>
            @endcan
        </x-slot:topActions>

        <livewire:tables.team-table lazy></livewire:tables.team-table>
    </x-layouts.page>
</x-layouts.app>
