<?php

declare(strict_types=1);

namespace App\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConfirmModal extends Component
{
    public string $modalStateId;

    public bool $useExternalState;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id = 'confirm-modal',
        public string $confirmVariant = 'solid',
        public string $confirmColor = 'error',
        public string $cancelVariant = 'ghost',
        public ?string $cancelColor = null,
        public string $maxWidth = 'md',
        public ?string $placement = null,
        public bool $showIcon = true,
        public ?string $openState = null,
        public bool $closeOnOutsideClick = true,
        public bool $closeOnEscape = true,
        public bool $backdropTransition = true,
    ) {
        $this->modalStateId = $this->openState ?? ('confirmModalIsOpen_' . \str_replace('-', '_', $this->id));
        $this->useExternalState = $this->openState !== null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ui.confirm-modal');
    }
}
