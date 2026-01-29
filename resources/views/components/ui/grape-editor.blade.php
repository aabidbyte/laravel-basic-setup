@props(['label' => null, 'required' => false, 'lang' => 'en', 'dir' => 'ltr'])

@pushOnce('endHead')
    <link rel="stylesheet"
          href="https://unpkg.com/grapesjs/dist/css/grapes.min.css"
          @cspNonce>
    <link rel="stylesheet"
          href="https://unpkg.com/grapesjs-preset-newsletter/dist/grapesjs-preset-newsletter.css"
          @cspNonce>
    <style @cspNonce>
        /* Customize Right Panel Width */
        .gjs-pn-views-container {
            width: max(18%, 200px) !important;
            min-width: 200px !important;
        }

        .gjs-cv-canvas {
            width: calc(100% - max(18%, 200px)) !important;
        }

        .gjs-pn-panel.gjs-pn-options {
            z-index: 99 !important;
        }
    </style>
@endPushOnce

@pushOnce('endBody')
    <script src="https://unpkg.com/grapesjs"
            @cspNonce></script>
    <script src="https://unpkg.com/grapesjs-preset-newsletter"
            @cspNonce></script>
@endPushOnce

<div class="w-full">
    @if ($label)
        <label class="label">
            <span class="label-text {{ $required ? 'font-semibold' : '' }}">
                {{ $label }}
                @if ($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif

    <div x-data="grapeEditor($wire.entangle('{{ $attributes->wire('model')->value() }}'), '{{ Vite::asset(config('assets.css.app')) }}', '{{ $currentTheme ?? 'light' }}', '{{ $lang }}', '{{ $dir }}')"
         wire:ignore
         {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'border-base-300 overflow-hidden rounded-lg border shadow-sm']) }}>
        <div x-ref="editor"
             class="min-h-[600px] w-full bg-white"></div>
    </div>
</div>
