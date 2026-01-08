@php
    use App\Constants\Auth\Permissions;

    setPageTitle(
        __('errors.management.title'),
        __('errors.management.description'),
    );
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        <livewire:tables.error-log-table lazy></livewire:tables.error-log-table>
    </x-layouts.page>
</x-layouts.app>
