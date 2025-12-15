@props([
    'id' => uniqid('modal-'),
    'title' => null,
    'closeOnOutsideClick' => true, // Show modal-backdrop to close on outside click
    'showCloseButton' => true, // Show close button at top-right corner
    'closeBtn' => true, // Show cancel/close button in actions area
    'closeBtnLabel' => 'Cancel', // Label for the cancel/close button
    'maxWidth' => null, // Custom width (e.g., 'md', 'lg', 'xl', '5xl', '11/12')
    'placement' => 'middle', // Placement: 'top', 'middle', 'bottom', 'start', 'end'
    'class' => '', // Additional classes for modal-box
    'autoOpen' => false, // Automatically open the modal when rendered
])

@php
    // Generate modal classes
    $modalClasses = 'modal modal-bottom ';
    if ($placement) {
        $modalClasses .= ' sm:modal-' . $placement;
    }

    // Generate modal-box classes
    $modalBoxClasses = 'modal-box';
    if ($maxWidth) {
        // Support both DaisyUI max-width classes and custom Tailwind classes
        if (in_array($maxWidth, ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'])) {
            $modalBoxClasses .= ' max-w-' . $maxWidth;
        } elseif (str_contains($maxWidth, '/')) {
            // Support fraction classes like '11/12'
            $modalBoxClasses .= ' w-' . $maxWidth;
        } else {
            // Support custom width classes
            $modalBoxClasses .= ' ' . $maxWidth;
        }
    }
    if ($class) {
        $modalBoxClasses .= ' ' . $class;
    }
@endphp

<dialog id="{{ $id }}" class="{{ $modalClasses }}" x-data
    @if ($autoOpen) data-auto-open="true" @endif x-init="if ($el.dataset.autoOpen === 'true') {
        $nextTick(() => { $el.showModal(); });
    }">
    <div class="{{ $modalBoxClasses }}">
        @if ($showCloseButton)
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
        @endif

        @if ($title)
            <h3 class="text-lg font-bold">{{ $title }}</h3>
        @endif

        {{ $slot }}

        @if ($closeBtn || isset($actions))
            <div class="modal-action">
                @if ($closeBtn)
                    <form method="dialog">
                        <x-ui.button variant="ghost">
                            {{ __($closeBtnLabel) }}
                        </x-ui.button>
                    </form>
                @endif
                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        @endif
    </div>

    @if ($closeOnOutsideClick)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @endif
</dialog>
