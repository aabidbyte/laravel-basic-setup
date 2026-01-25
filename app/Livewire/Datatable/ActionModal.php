<?php

declare(strict_types=1);

namespace App\Livewire\DataTable;

use App\Livewire\Bases\LivewireBaseComponent;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

/**
 * Global Datatable Action Modal Component
 *
 * Handles all datatable action modals globally.
 * Listens for the 'open-datatable-modal' event and renders the appropriate content.
 */
class ActionModal extends LivewireBaseComponent
{
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
    public function openModal(?string $viewPath = null, string $viewType = 'blade', array $viewProps = [], ?string $viewTitle = null, ?string $datatableId = null): void
    {
        // Handle confirmation modal type by mapping it to a Blade view
        if ($viewType === 'confirm') {
            $viewType = 'blade'; // Normalize to blade for view rendering compatibility
            $viewPath = $viewPath ?? 'components.ui.confirm-dialog-body';
            $viewTitle = $viewTitle ?? $viewProps['title'] ?? __('modals.confirm.title');

            // Inject behavior callback strings for the Blade component
            $viewProps = array_merge([
                'title' => $viewTitle,
                'content' => $viewProps['content'] ?? '',
                'confirmLabel' => $viewProps['confirmLabel'] ?? __('actions.confirm'),
                'cancelLabel' => $viewProps['cancelLabel'] ?? __('actions.cancel'),
                'onConfirm' => 'confirmAction()',
                'onCancel' => 'closeModal()',
                // Explicitly preserve action keys
                'actionKey' => $viewProps['actionKey'] ?? null,
                'uuid' => $viewProps['uuid'] ?? null,
                'isBulk' => $viewProps['isBulk'] ?? false,
            ], $viewProps);
        }

        $this->modalView = $viewPath;
        $this->modalType = $viewType;
        $this->modalProps = $viewProps;
        $this->modalTitle = $viewTitle;
        $this->datatableId = $datatableId;
        $this->isOpen = true;
    }

    /**
     * Handle confirmation action
     */
    #[On('datatable-confirm')]
    public function confirm(): void
    {
        // Extract action details from props
        $payload = [
            'actionKey' => $this->modalProps['actionKey'] ?? null,
            'uuid' => $this->modalProps['uuid'] ?? null,
            'isBulk' => $this->modalProps['isBulk'] ?? false,
        ];

        // Dispatch event for datatable.js to pick up
        if ($this->datatableId) {
            $this->dispatch("datatable:action-confirmed:{$this->datatableId}", $payload);
        }

        $this->closeModal();
    }

    /**
     * Close the modal and reset state
     */
    /**
     * Close the modal and reset state
     */
    #[On('datatable-close-modal')]
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

    public function render(): View
    {
        return view('components.datatable.action-modal');
    }
}
