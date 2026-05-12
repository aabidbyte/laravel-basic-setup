<?php

use App\Livewire\Bases\BasePageComponent;

new class extends BasePageComponent {
    public ?string $pageTitle = 'plans.title';
    public ?string $pageSubtitle = 'plans.subtitle';
}; ?>
 
 <x-layouts.page>
     <x-slot:topActions>
         <x-ui.button variant="primary"
                      href="{{ route('plans.create') }}"
                      wire:navigate>
             <x-ui.icon name="plus"
                        size="sm" />
             {{ __('plans.create_plan') }}
         </x-ui.button>
     </x-slot:topActions>
 
     <x-ui.card>
         <livewire:tables.plan-table />
     </x-ui.card>
 </x-layouts.page>
