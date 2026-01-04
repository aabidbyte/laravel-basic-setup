<?php
/**
 * Global Datatable Action Modal Component
 *
 * This is a standalone Livewire SFC that handles all datatable action modals globally.
 * It listens for the 'open-datatable-modal' event and renders the appropriate content.
 *
 * Uses <x-ui.base-modal> internally and exposes modalIsOpen to child views.
 */

declare(strict_types=1);

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    /**
     * Modal view/component path
     */
    public ?string $modalView = null;

    /**
     * Modal props (will be passed to the included view)
     *
     * @var array<string, mixed>
     */
    public array $modalProps = [];

    /**
     * Modal type: 'blade' or 'livewire'
     */
    public string $modalType = 'blade';

    /**
     * Modal title
     */
    public ?string $modalTitle = null;

    /**
     * Whether modal is open
     */
    public bool $isOpen = false;

    /**
     * ID of the datatable that opened this modal (for callbacks)
     */
    public ?string $datatableId = null;

    /**
     * Handle open modal event from datatable components
     *
     * @param  string  $viewPath  The view or component path
     * @param  string  $viewType  'blade' or 'livewire'
     * @param  array<string, mixed>  $viewProps  Props to pass to the modal content
     * @param  string|null  $viewTitle  Optional modal title
     * @param  string|null  $datatableId  ID of the datatable for callbacks
     */
    #[On('open-datatable-modal')]
    public function openModal(string $viewPath, string $viewType = 'blade', array $viewProps = [], ?string $viewTitle = null, ?string $datatableId = null): void
    {
        $this->modalView = $viewPath;
        $this->modalType = $viewType;
        $this->modalProps = $viewProps;
        $this->modalTitle = $viewTitle;
        $this->datatableId = $datatableId;
        $this->isOpen = true;
    }

    /**
     * Close the modal and reset state
     */
    public function closeModal(): void
    {
        $this->isOpen = false;

        // Dispatch close event back to datatable if needed
        if ($this->datatableId) {
            $this->dispatch("datatable:modal-closed:{$this->datatableId}");
        }

        // Reset all modal state
        $this->reset(['modalView', 'modalProps', 'modalType', 'modalTitle', 'datatableId']);
    }
};
?>

{{--
    Alpine x-data wrapper provides:
    - modalIsOpen: entangled with Livewire isOpen, accessible to child views
    - isLoading: immediate loading state shown before server response
    
    Child views can use: @click="modalIsOpen = false" to close modal
--}}
<div x-data="{
    modalIsOpen: $wire.$entangle('isOpen'),
    isLoading: false,
    _loadingListener: null,

    init() {
        // Store listener reference for cleanup
        this._loadingListener = () => {
            this.isLoading = true;
            this.modalIsOpen = true;
        };

        // Listen for loading trigger (dispatched from datatable before server request)
        window.addEventListener('datatable-modal-loading', this._loadingListener);

        // Use Livewire hook to detect when component has finished updating
        // This fires after every Livewire update, so we check if modal is open with content
        Livewire.hook('morph.updated', ({ component }) => {
            if (component.id === $wire.id && this.modalIsOpen && $wire.modalView) {
                this.isLoading = false;
            }
        });
    },

    destroy() {
        // Clean up event listener on component destroy
        if (this._loadingListener) {
            window.removeEventListener('datatable-modal-loading', this._loadingListener);
        }
    }
}">
    <x-ui.base-modal
        open-state="modalIsOpen"
        use-parent-state="true"
        :title="$modalTitle ?? __('ui.table.action_modal_title')"
        on-close="$wire.closeModal()"
        :show-close-button="true"
    >

        {{-- Loading State --}}
        <div
            x-show="isLoading"
            x-cloak
        >
            <x-ui.loading></x-ui.loading>
        </div>

        {{-- Content (always rendered by Blade, visibility toggled by Alpine) --}}
        <div
            x-show="!isLoading"
            x-cloak
            wire:loading.class="opacity-50"
        >
            @if ($isOpen && $modalView)
                @if ($modalType === 'blade')
                    @include($modalView, $modalProps)
                @else
                    <livewire:is
                        :component="$modalView"
                        v-bind="$modalProps"
                        :key="'modal-' . $modalView . '-' . uniqid()"
                    />
                @endif
            @endif
        </div>
    </x-ui.base-modal>
</div>
