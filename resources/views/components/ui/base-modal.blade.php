{{--
    Base Modal Component Props:
    - Modal identification: id, modalId
    - State management: open, openState
    - Content: title, titleId, description, descriptionId
    - Visual appearance: variant ('default', 'success', 'info', 'warning', 'danger'), maxWidth ('xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', or custom), placement ('top', 'middle', 'bottom', 'start', 'end'), class, dialogClass, headerClass, bodyClass, footerClass
    - Behavior: closeOnOutsideClick, closeOnEscape, trapFocus, preventScroll, autoOpen
    - Transitions: transition ('fade-in', 'scale-up', 'scale-down', 'slide-up', 'slide-down', 'unfold', 'none'), transitionDuration, transitionDelay, backdropTransition
    - Close button: showCloseButton, closeButtonLabel, closeButtonClass
    - Footer actions: showFooter
    - Accessibility: role, ariaModal
    - Advanced: onOpen, onClose, persistent
--}}
@props([
    'id' => null,
    'modalId' => null,
    'open' => false,
    'openState' => 'modalIsOpen',
    'useParentState' => false,
    'title' => null,
    'titleId' => null,
    'description' => null,
    'descriptionId' => null,
    'variant' => 'default',
    'maxWidth' => 'md',
    'placement' => 'middle',
    'class' => '',
    'dialogClass' => '',
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'closeOnOutsideClick' => true,
    'closeOnEscape' => true,
    'trapFocus' => true,
    'preventScroll' => true,
    'autoOpen' => false,
    'transition' => 'scale-up',
    'transitionDuration' => 200,
    'transitionDelay' => 100,
    'backdropTransition' => true,
    'showCloseButton' => true,
    'closeButtonLabel' => 'Close modal',
    'closeButtonClass' => '',
    'showFooter' => true,
    'role' => 'dialog',
    'ariaModal' => true,
    'onOpen' => null,
    'onClose' => null,
    'persistent' => false,
])

@php
    // Generate unique modal ID
    $modalId = $id ?? ($modalId ?? uniqid('modal-'));

    // Generate ARIA IDs
    $titleId = $titleId ?? $modalId . '-title';
    $descriptionId = $descriptionId ?? $modalId . '-description';

    // Build container classes
    $containerClasses = 'fixed inset-0 z-50 flex p-4';

    // Build placement classes
    $placementClasses = match ($placement) {
        'top' => 'items-start pt-8',
        'middle' => 'items-center',
        'bottom' => 'items-end pb-8',
        'start' => 'justify-start pl-8',
        'end' => 'justify-end pr-8',
        default => 'items-center justify-center',
    };

    // Build max-width classes
    $maxWidthClasses = match ($maxWidth) {
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
        default => $maxWidth,
    };

    // Build variant classes (border colors)
    $variantClasses = match ($variant) {
        'success' => 'border border-success',
        'info' => 'border border-info',
        'warning' => 'border border-warning',
        'danger' => 'border border-error',
        default => '',
    };

    // Build transition enter classes with standard Tailwind durations
    // Map custom durations to closest Tailwind classes, or use inline styles for exact values
    $durationClass = match (true) {
        $transitionDuration <= 75 => 'duration-75',
        $transitionDuration <= 100 => 'duration-100',
        $transitionDuration <= 150 => 'duration-150',
        $transitionDuration <= 200 => 'duration-200',
        $transitionDuration <= 300 => 'duration-300',
        $transitionDuration <= 500 => 'duration-500',
        $transitionDuration <= 700 => 'duration-700',
        default => 'duration-1000',
    };

    $delayClass = match (true) {
        $transitionDelay <= 75 => 'delay-75',
        $transitionDelay <= 100 => 'delay-100',
        $transitionDelay <= 150 => 'delay-150',
        $transitionDelay <= 200 => 'delay-200',
        $transitionDelay <= 300 => 'delay-300',
        default => '',
    };

    $transitionEnter = match ($transition) {
        'fade-in' => 'transition ease-out ' . $durationClass,
        'scale-up' => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        'scale-down' => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        'slide-up' => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        'slide-down' => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        'unfold' => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
        'none' => '',
        default => 'transition ease-out ' . $durationClass . ' ' . $delayClass,
    };

    $transitionLeave = match ($transition) {
        'none' => '',
        default => 'transition ease-in ' . $durationClass,
    };

    $transitionEnterStart = match ($transition) {
        'fade-in' => 'opacity-0',
        'scale-up' => 'opacity-0 scale-50',
        'scale-down' => 'opacity-100 scale-100',
        'slide-up' => 'opacity-0 translate-y-8',
        'slide-down' => 'opacity-0 -translate-y-8',
        'unfold' => 'opacity-0 scale-y-0 origin-top',
        'none' => '',
        default => 'opacity-0 scale-50',
    };

    $transitionEnterEnd = match ($transition) {
        'fade-in' => 'opacity-100',
        'scale-up' => 'opacity-100 scale-100',
        'scale-down' => 'opacity-0 scale-50',
        'slide-up' => 'opacity-100 translate-y-0',
        'slide-down' => 'opacity-100 translate-y-0',
        'unfold' => 'opacity-100 scale-y-100',
        'none' => '',
        default => 'opacity-100 scale-100',
    };

    // Build dialog classes
    // NOTE: We intentionally do NOT use DaisyUI's `modal-box` utility class here.
// `modal-box` is designed to be used inside DaisyUI's `.modal` wrapper, and can have base styles
    // (including opacity) that are toggled by `.modal-open`. Our base modal is a custom Alpine modal,
    // so we use equivalent theme-aware classes instead.
    $dialogClasses = collect([
        'w-full',
        'rounded-box',
        'bg-base-100',
        'text-base-content',
        'p-6',
        'shadow-lg',
        $maxWidthClasses,
        $variantClasses,
        $dialogClass,
    ])
        ->filter()
        ->implode(' ');

    // Build close action
    $closeAction = $persistent ? '' : $openState . ' = false';
    if ($onClose) {
        $closeAction = $closeAction ? $closeAction . ', ' . $onClose : $onClose;
    }

    // Build focus trap
    $focusTrap = $trapFocus ? 'x-trap.inert.noscroll="' . $openState . '"' : '';

    // Build x-data attribute (only when not using parent state)
    // Use Js::from to properly encode the value for JavaScript
    $xDataValue = \Illuminate\Support\Js::from($open || $autoOpen);
    $xDataAttr = !$useParentState ? 'x-data="{ ' . $openState . ': ' . $xDataValue . ' }"' : '';

    // Build x-init attribute
    $xInitParts = [];
    if ($autoOpen) {
        $xInitParts[] = '$nextTick(() => { ' . $openState . ' = true; })';
    }
    if ($onOpen) {
        $xInitParts[] = '$watch(\'' . $openState . '\', (value) => { if (value) { ' . $onOpen . ' } })';
    }
    $xInitAttr = !empty($xInitParts) ? 'x-init="' . implode(';
    ', $xInitParts) . '"' : '';

    // Build conditional event handlers
    $escapeHandler = $closeOnEscape ? 'x-on:keydown.esc.window="' . $closeAction . '"' : '';
    $outsideClickHandler = $closeOnOutsideClick ? 'x-on:click.self="' . $closeAction . '"' : '';

    // Build ARIA attributes
    $ariaModalAttr = $ariaModal ? 'aria-modal="true"' : '';
    $ariaLabelledByAttr = $title ? 'aria-labelledby="' . $titleId . '"' : '';
    $ariaDescribedByAttr = $description ? 'aria-describedby="' . $descriptionId . '"' : '';

    // Build transition attributes (only when transition is not 'none')
    $hasTransition = $transition !== 'none';
    $transitionEnterAttr = $hasTransition ? 'x-transition:enter="' . $transitionEnter . '"' : '';
    $transitionEnterStartAttr = $hasTransition ? 'x-transition:enter-start="' . $transitionEnterStart . '"' : '';
    $transitionEnterEndAttr = $hasTransition ? 'x-transition:enter-end="' . $transitionEnterEnd . '"' : '';
    $transitionLeaveAttr = $hasTransition ? 'x-transition:leave="' . $transitionLeave . '"' : '';
    $transitionLeaveStartAttr = $hasTransition ? 'x-transition:leave-start="opacity-100 scale-100"' : '';
    $transitionLeaveEndAttr = $hasTransition ? 'x-transition:leave-end="opacity-0 scale-95"' : '';

    // Build container (backdrop) transition attributes
    $containerTransitionAttrs = $backdropTransition
        ? 'x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"'
        : '';
@endphp

{{-- Modal Container: Creates new x-data scope when useParentState is false, uses parent state when true --}}
<div {!! $xDataAttr !!} {!! $xInitAttr !!} x-cloak x-show="{{ $openState }}" {!! $containerTransitionAttrs !!}
    {!! $escapeHandler !!} {!! $outsideClickHandler !!} {!! $focusTrap !!}
    class="{{ $containerClasses }} {{ $placementClasses }} {{ $class }}" role="{{ $role }}"
    {!! $ariaModalAttr !!} {!! $ariaLabelledByAttr !!} {!! $ariaDescribedByAttr !!}>
    {{-- Modal Dialog --}}
    <div x-show="{{ $openState }}" {!! $transitionEnterAttr !!} {!! $transitionEnterStartAttr !!} {!! $transitionEnterEndAttr !!}
        {!! $transitionLeaveAttr !!} {!! $transitionLeaveStartAttr !!} {!! $transitionLeaveEndAttr !!} class="{{ $dialogClasses }}">
        {{-- Dialog Header --}}
        @if ($title || $showCloseButton)
            <div class="flex items-center justify-between gap-4 {{ $headerClass }}">
                @if ($title)
                    <h3 id="{{ $titleId }}" class="text-lg font-bold">
                        {{ $title }}
                    </h3>
                @else
                    <div></div>
                @endif

                @if ($showCloseButton)
                    <button type="button" x-on:click="{{ $closeAction }}"
                        class="btn btn-sm btn-circle btn-ghost {{ $closeButtonClass }}"
                        aria-label="{{ $closeButtonLabel }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"
                            stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        {{-- Dialog Description --}}
        @if ($description)
            <p id="{{ $descriptionId }}" class="text-sm text-base-content/70 mt-2">
                {{ $description }}
            </p>
        @endif

        {{-- Dialog Body --}}
        <div class="{{ $bodyClass }}">
            {{ $slot }}
        </div>

        {{-- Dialog Footer --}}
        @if ($showFooter && (isset($footerActions) || isset($actions)))
            <div class="modal-action {{ $footerClass }}">
                @isset($footerActions)
                    {{ $footerActions }}
                @endisset

                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        @endif
    </div>
</div>
