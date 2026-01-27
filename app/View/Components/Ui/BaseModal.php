<?php

declare(strict_types=1);

namespace App\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Js;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;

class BaseModal extends Component
{
    public string $modalId;

    public string $titleId;

    public string $descriptionId;

    public string $containerBaseClasses;

    public string $dialogClasses;

    public ComponentAttributeBag $dialogAttributes;

    /**
     * @var array<string, string>
     */
    public array $containerAttributeDefaults;

    public string $closeAction;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $id = null,
        ?string $modalId = null,
        public bool $open = false,
        public string $openState = 'modalIsOpen',
        public bool $useParentState = false,
        public ?string $title = null,
        ?string $titleId = null,
        public ?string $description = null,
        ?string $descriptionId = null,
        public string $variant = 'default',
        public ?string $color = null,
        public ?string $maxWidth = null,
        public ?string $placement = null,
        public string $class = '',
        public string $dialogClass = '',
        public string $headerClass = '',
        public string $bodyClass = '',
        public string $footerClass = '',
        public bool $closeOnOutsideClick = true,
        public bool $closeOnEscape = true,
        public bool $trapFocus = true,
        public bool $preventScroll = true,
        public bool $autoOpen = false,
        public string $transition = 'scale-up',
        public int $transitionDuration = 100,
        public int $transitionDelay = 0,
        public bool $backdropTransition = true,
        public bool $showCloseButton = true,
        public string $closeButtonLabel = 'Close modal',
        public string $closeButtonClass = '',
        public bool $showFooter = true,
        public string $role = 'dialog',
        public bool $ariaModal = true,
        public ?string $onOpen = null,
        public ?string $onClose = null,
        public bool $persistent = false,
        public int $backdropOpacity = 60,
        public string $backdropBlur = 'md',
        public string $backdropClass = '',
        public bool $customClose = false,
        public ?string $maxHeight = null,
        public string $backgroundClass = 'bg-base-100',
        public string $paddingClass = 'py-2 px-4',
    ) {
        $this->openState = $this->sanitizeAlpineIdentifier($this->openState, 'modalIsOpen');
        $this->placement = $this->sanitizePlacement($this->placement);
        $this->transition = $this->sanitizeTransition($this->transition);
        $this->variant = $this->sanitizeVariant($this->variant);
        $this->color = $this->sanitizeColor($this->color);
        $this->backdropOpacity = $this->sanitizeOpacity($this->backdropOpacity);
        $this->backdropBlur = $this->sanitizeBackdropBlur($this->backdropBlur);

        $this->modalId = $this->id ?? ($modalId ?? uniqid('modal-', true));
        $this->titleId = $titleId ?? ($this->modalId . '-title');
        $this->descriptionId = $descriptionId ?? ($this->modalId . '-description');

        $this->closeAction = $this->buildCloseAction();
        $this->containerBaseClasses = $this->buildContainerBaseClasses();
        $this->dialogClasses = $this->buildDialogClasses();
        $this->dialogAttributes = $this->buildDialogAttributes();
        $this->containerAttributeDefaults = $this->buildContainerAttributeDefaults();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ui.base-modal');
    }

    private function buildCloseAction(): string
    {
        if ($this->persistent) {
            return '';
        }

        if ($this->customClose) {
            return $this->onClose ?? ($this->openState . ' = false');
        }

        $closeAction = $this->openState . ' = false';

        if ($this->onClose) {
            return $closeAction . '; ' . $this->onClose;
        }

        return $closeAction;
    }

    private function buildContainerBaseClasses(): string
    {
        $base = [
            'fixed',
            'inset-0',
            'z-[9999]',
            'flex',
            'w-full',
            'p-4',
        ];

        $placementClasses = $this->placement
            ? $this->buildPlacementClasses($this->placement)
            : 'items-end justify-center pb-8 sm:items-center sm:pb-0';

        $backdropClasses = $this->backdropClass !== ''
            ? $this->backdropClass
            : trim(sprintf(
                'bg-base-300/%d %s',
                $this->backdropOpacity,
                $this->backdropBlur === 'none' ? '' : $this->backdropBlur,
            ));

        return trim(implode(' ', [...$base, $placementClasses, $backdropClasses]));
    }

    private function buildPlacementClasses(string $placement): string
    {
        $row = 'center';
        $col = 'center';

        if (str_contains($placement, '-')) {
            [$row, $col] = explode('-', $placement, 2);
        } else {
            $row = 'center';
            $col = 'center';
        }

        $items = match ($row) {
            'top' => 'items-start pt-8',
            'bottom' => 'items-end pb-8',
            default => 'items-center',
        };

        $justify = match ($col) {
            'left' => 'justify-start',
            'right' => 'justify-end',
            default => 'justify-center',
        };

        return $items . ' ' . $justify;
    }

    private function buildDialogClasses(): string
    {
        $maxWidthClasses = match ($this->maxWidth) {
            'xs' => 'max-w-xs',
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            '2xl' => 'max-w-2xl',
            '3xl' => 'max-w-3xl',
            '4xl' => 'max-w-4xl',
            '5xl' => 'max-w-5xl',
            '6xl' => 'max-w-6xl',
            '7xl' => 'max-w-7xl',
            default => 'max-w-[80vw]',
        };

        $maxHeightClasses = match ($this->maxHeight) {
            'screen' => 'max-h-screen',
            default => 'max-h-[90vh]',
        };

        $colorClasses = match ($this->color) {
            'success' => 'border border-success',
            'info' => 'border border-info',
            'warning' => 'border border-warning',
            'danger', 'error' => 'border border-error',
            default => '',
        };

        $classes = [
            'w-full',
            'rounded-box',
            'text-base-content',
            'shadow-lg',
            $this->backgroundClass,
            $maxWidthClasses,
            $maxHeightClasses . ' overflow-y-auto',
            $colorClasses,
            $this->dialogClass,
        ];

        return trim(implode(' ', array_filter($classes)));
    }

    private function buildDialogAttributes(): ComponentAttributeBag
    {
        if ($this->transition === 'none') {
            return new ComponentAttributeBag;
        }

        [$durationClass, $delayClass] = $this->buildDurationAndDelayClasses($this->transitionDuration, $this->transitionDelay);

        $transitionEnter = trim(match ($this->transition) {
            'fade-in' => 'transition ease-out ' . $durationClass,
            default => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        });

        $transitionLeave = 'transition ease-in ' . $durationClass;

        $transitionEnterStart = match ($this->transition) {
            'fade-in' => 'opacity-0',
            'scale-up' => 'opacity-0 scale-95',
            'scale-down' => 'opacity-0 scale-110',
            'slide-up' => 'opacity-0 translate-y-8',
            'slide-down' => 'opacity-0 -translate-y-8',
            'unfold' => 'opacity-0 scale-y-0 origin-top',
            default => 'opacity-0 scale-50',
        };

        $transitionEnterEnd = match ($this->transition) {
            'fade-in' => 'opacity-100',
            'scale-up' => 'opacity-100 scale-100',
            'scale-down' => 'opacity-100 scale-100',
            'slide-up' => 'opacity-100 translate-y-0',
            'slide-down' => 'opacity-100 translate-y-0',
            'unfold' => 'opacity-100 scale-y-100',
            default => 'opacity-100 scale-100',
        };

        return new ComponentAttributeBag([
            'x-transition:enter' => trim($transitionEnter . ' motion-reduce:transition-opacity'),
            'x-transition:enter-start' => $transitionEnterStart,
            'x-transition:enter-end' => $transitionEnterEnd,
            'x-transition:leave' => trim($transitionLeave . ' motion-reduce:transition-opacity'),
            'x-transition:leave-start' => 'opacity-100 scale-100',
            'x-transition:leave-end' => 'opacity-0 scale-95',
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function buildDurationAndDelayClasses(int $durationMs, int $delayMs): array
    {
        $durationClass = match (true) {
            $durationMs <= 75 => 'duration-75',
            $durationMs <= 100 => 'duration-100',
            $durationMs <= 150 => 'duration-150',
            $durationMs <= 200 => 'duration-200',
            $durationMs <= 300 => 'duration-300',
            $durationMs <= 500 => 'duration-500',
            $durationMs <= 700 => 'duration-700',
            default => 'duration-1000',
        };

        $delayClass = match (true) {
            $delayMs <= 0 => '',
            $delayMs <= 75 => 'delay-75',
            $delayMs <= 100 => 'delay-100',
            $delayMs <= 150 => 'delay-150',
            $delayMs <= 200 => 'delay-200',
            $delayMs <= 300 => 'delay-300',
            default => 'delay-300',
        };

        return [$durationClass, $delayClass];
    }

    /**
     * @return array<string, string>
     */
    private function buildContainerAttributeDefaults(): array
    {
        $defaults = [];

        if (! $this->useParentState) {
            $xDataValue = Js::from($this->open || $this->autoOpen);
            $defaults['x-data'] = '{ ' . $this->openState . ': ' . $xDataValue . ' }';
        }

        $xInitParts = [];
        if ($this->autoOpen) {
            $xInitParts[] = '$nextTick(() => { ' . $this->openState . ' = true; })';
        }
        if ($this->onOpen) {
            $xInitParts[] = '$watch(\'' . $this->openState . '\', (value) => { if (value) { ' . $this->onOpen . ' } })';
        }
        if (! empty($xInitParts)) {
            $defaults['x-init'] = implode('; ', $xInitParts);
        }

        if ($this->trapFocus) {
            $defaults[$this->preventScroll ? 'x-trap.inert.noscroll' : 'x-trap.inert'] = $this->openState;
        }

        if ($this->closeAction !== '' && $this->closeOnEscape) {
            $defaults['x-on:keydown.esc.window'] = $this->closeAction;
        }

        if ($this->closeAction !== '' && $this->closeOnOutsideClick) {
            $defaults['x-on:click.self'] = $this->closeAction;
        }

        if ($this->backdropTransition) {
            $defaults['x-transition:enter'] = 'transition ease-out duration-200 motion-reduce:transition-opacity';
            $defaults['x-transition:enter-start'] = 'opacity-0';
            $defaults['x-transition:enter-end'] = 'opacity-100';
            $defaults['x-transition:leave'] = 'transition ease-in duration-200 motion-reduce:transition-opacity';
            $defaults['x-transition:leave-start'] = 'opacity-100';
            $defaults['x-transition:leave-end'] = 'opacity-0';
        }

        $defaults['role'] = $this->role;

        if ($this->ariaModal) {
            $defaults['aria-modal'] = 'true';
        }

        if ($this->title) {
            $defaults['aria-labelledby'] = $this->titleId;
        }

        if ($this->description) {
            $defaults['aria-describedby'] = $this->descriptionId;
        }

        return $defaults;
    }

    private function sanitizeAlpineIdentifier(string $value, string $fallback): string
    {
        $value = trim($value);

        if ($value === '') {
            return $fallback;
        }

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            return $fallback;
        }

        return $value;
    }

    private function sanitizePlacement(?string $placement): ?string
    {
        if ($placement === null) {
            return null;
        }

        $placement = trim($placement);
        if ($placement === '') {
            return null;
        }

        $allowed = [
            'top-left',
            'top-center',
            'top-right',
            'center-left',
            'center',
            'center-right',
            'bottom-left',
            'bottom-center',
            'bottom-right',
        ];

        return in_array($placement, $allowed, true) ? $placement : null;
    }

    private function sanitizeTransition(string $transition): string
    {
        $transition = trim($transition);

        $allowed = [
            'fade-in',
            'scale-up',
            'scale-down',
            'slide-up',
            'slide-down',
            'unfold',
            'none',
        ];

        return in_array($transition, $allowed, true) ? $transition : 'scale-up';
    }

    private function sanitizeVariant(string $variant): string
    {
        $variant = trim($variant);

        $allowed = [
            'default',
        ];

        return in_array($variant, $allowed, true) ? $variant : 'default';
    }

    private function sanitizeColor(?string $color): ?string
    {
        if ($color === null) {
            return null;
        }

        $color = trim($color);

        $allowed = [
            'success',
            'info',
            'warning',
            'danger',
            'error',
        ];

        return in_array($color, $allowed, true) ? $color : null;
    }

    private function sanitizeOpacity(int $opacity): int
    {
        if ($opacity < 0) {
            return 0;
        }

        if ($opacity > 100) {
            return 100;
        }

        return $opacity;
    }

    private function sanitizeBackdropBlur(string $backdropBlur): string
    {
        $backdropBlur = trim($backdropBlur);

        return match ($backdropBlur) {
            'none' => 'none',
            'sm' => 'backdrop-blur-sm',
            'md' => 'backdrop-blur-md',
            'lg' => 'backdrop-blur-lg',
            default => 'backdrop-blur-md',
        };
    }
}
