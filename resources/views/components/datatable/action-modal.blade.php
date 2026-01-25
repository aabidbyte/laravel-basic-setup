{{--
    Alpine x-data wrapper provides:
    - modalIsOpen: entangled with Livewire isOpen, accessible to child views
    - isLoading: immediate loading state shown before server response
    
    Child views can use: @click="modalIsOpen = false" to close modal
--}}
<div x-data="actionModal()">
    <x-ui.base-modal open-state="modalIsOpen"
                     use-parent-state="true"
                     :title="$modalType === 'confirm' ? null : ($modalTitle ?? __('table.action_modal_title'))"
                     on-close="closeModal()"
                     :custom-close="true"
                     :show-close-button="$modalType !== 'confirm'">

        {{-- Loading State --}}
        <div x-show="isLoading"
             x-cloak>
            <x-ui.loading></x-ui.loading>
        </div>

        {{-- Content (always rendered by Blade, visibility toggled by Alpine) --}}
        <div x-show="!isLoading"
             x-cloak
             wire:loading.class="opacity-50">
            @if ($isOpen && $modalView)
                @if ($modalType === 'blade')
                    @include($modalView, $modalProps)
                @else
                    @livewire($modalView, $modalProps, 'modal-' . $modalView . '-' . uniqid())
                @endif
            @endif
        </div>
    </x-ui.base-modal>
</div>
