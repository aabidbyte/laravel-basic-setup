<?php

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Feature;

new class extends BasePageComponent {
    public ?string $pageTitle = 'features.title';
    public ?string $pageSubtitle = 'features.subtitle';

    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Feature::class);
    }
}; ?>

<x-layouts.page>
    <x-slot:topActions>
        <x-ui.button color="primary"
                     icon="plus"
                     href="{{ route('features.create') }}"
                     wire:navigate>
            {{ __('features.create_feature') }}
        </x-ui.button>
    </x-slot:topActions>

    <x-ui.card>
        <livewire:tables.feature-table />
    </x-ui.card>
</x-layouts.page>
