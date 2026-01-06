@php
    use App\Constants\Auth\Permissions;
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        <x-slot:topActions>
            @can(Permissions::CREATE_USERS)
                <x-ui.button
                    href="{{ route('users.create') }}"
                    wire:navigate
                    color="primary"
                    class="gap-2"
                >
                    <x-ui.icon
                        name="plus"
                        size="sm"
                    ></x-ui.icon>
                    {{ __('ui.users.actions.create') }}
                </x-ui.button>
            @endcan
        </x-slot:topActions>

        <livewire:tables.user-table lazy></livewire:tables.user-table>
    </x-layouts.page>
</x-layouts.app>
