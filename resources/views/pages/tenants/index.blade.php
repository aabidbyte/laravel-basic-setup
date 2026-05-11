@php
    setPageTitle(__('tenancy.tenants_management'), __('tenancy.tenants_management_description'));
@endphp

<x-layouts.app>
    <x-layouts.page>
        <x-slot:actions>
            <x-ui.button variant="primary"
                         icon="plus"
                         href="{{ route('tenants.settings.edit') }}"
                         wire:navigate>
                {{ __('tenancy.create_tenant') }}
            </x-ui.button>
        </x-slot:actions>

        <div class="flex flex-col gap-6">
            <livewire:tables.tenant-table />
        </div>
    </x-layouts.page>
</x-layouts.app>
