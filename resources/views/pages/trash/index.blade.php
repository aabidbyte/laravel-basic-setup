@php
    use App\Services\Trash\TrashRegistry;

    $registry = app(TrashRegistry::class);
    $config = $registry->getEntity($entityType);

    setPageTitle(
        __('pages.trash.index.title', ['type' => $config['labelPlural'] ?? $entityType]),
        __('pages.trash.index.description'),
    );
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        {{-- Entity type tabs --}}
        <x-slot:header>
            <div class="flex flex-wrap gap-2">
                @foreach ($registry->getAccessibleEntities() as $type => $typeConfig)
                    <x-ui.button href="{{ route('trash.index', ['entityType' => $type]) }}"
                                 wire:navigate
                                 :variant="$entityType === $type ? 'primary' : 'ghost'"
                                 size="sm">
                        <x-ui.icon name="{{ $typeConfig['icon'] }}"
                                   size="sm"></x-ui.icon>
                        {{ $typeConfig['labelPlural'] }}
                    </x-ui.button>
                @endforeach
            </div>
        </x-slot:header>

        <livewire:tables.trash-data-table :entityType="$entityType"
                                          lazy></livewire:tables.trash-data-table>
    </x-layouts.page>
</x-layouts.app>
