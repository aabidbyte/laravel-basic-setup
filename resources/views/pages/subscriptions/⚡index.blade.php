<?php

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Subscription;

new class extends BasePageComponent {
    public ?string $pageTitle = 'subscriptions.index_title';
    public ?string $pageSubtitle = 'subscriptions.index_subtitle';

    /**
     * Mount the page and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Subscription::class);
    }
}; ?>

<x-layouts.page>
    <x-slot:topActions>
        <x-ui.button color="primary"
                     icon="plus"
                     href="{{ route('subscriptions.create') }}"
                     wire:navigate>
            {{ __('subscriptions.create_subscription') }}
        </x-ui.button>
    </x-slot:topActions>

    <x-ui.card>
        <livewire:tables.subscription-table />
    </x-ui.card>
</x-layouts.page>
