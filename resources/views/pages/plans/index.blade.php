<x-layouts.page title="{{ __('plans.title') }}" subtitle="{{ __('plans.subtitle') }}">
    <x-slot:actions>
        <x-ui.button variant="primary" href="{{ route('plans.create') }}" wire:navigate>
            <x-ui.icon name="plus" size="sm" />
            {{ __('plans.create_plan') }}
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card>
        <livewire:tables.plan-table />
    </x-ui.card>
</x-layouts.page>
