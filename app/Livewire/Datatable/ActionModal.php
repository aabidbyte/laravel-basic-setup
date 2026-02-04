<?php

declare(strict_types=1);

namespace App\Livewire\DataTable;

use App\Livewire\Bases\LivewireBaseComponent;
use App\Support\UI\ModalOptions;
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
     */
    #[On('open-datatable-modal')]
    public function openModal(ModalOptions|array $options): void
    {
        $options = $options instanceof ModalOptions ? $options : ModalOptions::fromArray($options);

        // Handle confirmation modal type by mapping it to a Blade view
        if ($options->viewType === 'confirm') {
            $viewType = 'blade'; // Normalize to blade for view rendering compatibility
            $viewPath = $options->viewPath ?? 'components.ui.confirm-dialog-body';
            $viewTitle = $options->viewTitle ?? $options->viewProps['title'] ?? __('modals.confirm.title');

            // Inject behavior callback strings for the Blade component
            $viewProps = \array_merge([
                'title' => $viewTitle,
                'content' => $options->viewProps['content'] ?? '',
                'confirmLabel' => $options->viewProps['confirmLabel'] ?? __('actions.confirm'),
                'cancelLabel' => $options->viewProps['cancelLabel'] ?? __('actions.cancel'),
                'onConfirm' => 'confirmAction()',
                'onCancel' => 'closeModal()',
                // Explicitly preserve action keys
                'actionKey' => $options->viewProps['actionKey'] ?? null,
                'uuid' => $options->viewProps['uuid'] ?? null,
                'isBulk' => $options->viewProps['isBulk'] ?? false,
            ], $options->viewProps);
        } else {
            $viewPath = $options->viewPath;
            $viewType = $options->viewType;
            $viewProps = $options->viewProps;
            $viewTitle = $options->viewTitle;
        }

        $this->modalView = $viewPath;
        $this->modalType = $viewType;
        $this->modalProps = $viewProps;
        $this->modalTitle = $viewTitle;
        $this->datatableId = $options->datatableId;
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
