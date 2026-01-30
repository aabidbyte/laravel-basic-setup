@props(['label' => null, 'required' => false, 'lang' => 'en', 'dir' => 'ltr'])

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

@assets
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
    <script src="https://unpkg.com/grapesjs"
            @cspNonce></script>
    <script src="https://unpkg.com/grapesjs-preset-newsletter"
            @cspNonce></script>
    <script>
        (function() {
            const register = () => {
                Alpine.data('grapeEditor', (
                    content,
                    cssUrl = '',
                    theme = 'light',
                    lang = 'en',
                    dir = 'ltr',
                ) => ({
                    editor: null,
                    internalContent: content,
                    cssUrl: cssUrl,
                    theme: theme,
                    lang: lang,
                    dir: dir,
                    lastFocusedInput: null,

                    init() {
                        if (!this.$refs.editor) {
                            console.error('GrapeEditor: No editor reference found');
                            return;
                        }

                        // Track last focused input for Traits insertion
                        this.$el.addEventListener('focusin', (e) => {
                            if (
                                e.target &&
                                (e.target.tagName === 'INPUT' ||
                                    e.target.tagName === 'TEXTAREA')
                            ) {
                                this.lastFocusedInput = e.target;
                            }
                        });

                        // Wait for GrapeJS to be loaded (in case of async load via CDN)
                        this.loadInterval = setInterval(async () => {
                            if (window.grapesjs) {
                                clearInterval(this.loadInterval);
                                this.loadInterval = null;

                                // Fetch CSS content if URL is provided
                                let cssText = '';
                                if (this.cssUrl) {
                                    try {
                                        const response = await fetch(this.cssUrl);
                                        if (response.ok) {
                                            cssText = await response.text();
                                        }
                                    } catch (e) {
                                        console.warn(
                                            'GrapeEditor: Failed to fetch CSS for inlining',
                                            e,
                                        );
                                    }
                                }

                                // Ensure the element still exists before initializing
                                if (this.$refs.editor) {
                                    this.initEditor(cssText);
                                }
                            }
                        }, 50);

                        this.$watch('internalContent', (value) => {
                            if (!this.editor) return;
                            // Only update if external content changed significantly or is first load
                            const currentContent = this.getEditorContent();
                            if (value !== currentContent) {
                                this.editor.setComponents(value || '');
                            }
                        });

                        this.$el.addEventListener('insert-text', (e) => {
                            try {
                                const text = e.detail.text;

                                // 1. Try inserting into last focused Trait input (if valid and active context)
                                if (
                                    this.lastFocusedInput &&
                                    document.contains(this.lastFocusedInput)
                                ) {
                                    const input = this.lastFocusedInput;
                                    const start = input.selectionStart;
                                    const end = input.selectionEnd;
                                    const val = input.value;

                                    input.value =
                                        val.substring(0, start) + text + val.substring(end);
                                    input.selectionStart = input.selectionEnd =
                                        start + text.length;

                                    // Trigger Input/Change for GrapesJS binding
                                    input.dispatchEvent(new Event('input', {
                                        bubbles: true
                                    }));
                                    input.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));

                                    // Refocus to allow continuous typing/editing
                                    input.focus();
                                    return;
                                }

                                if (!this.editor) return;

                                // Check if we are currently editing a text component (RTE is active)
                                const editingComponent = this.editor.getEditing();

                                if (editingComponent && this.editor.RichTextEditor) {
                                    // We are in edit mode, try to insert at cursor
                                    this.editor.RichTextEditor.run('insertHTML', text);
                                } else {
                                    // Not in edit mode, fallback to appending to the selected component
                                    const selected = this.editor.getSelected();

                                    if (selected) {
                                        // Check if the component can accept text content
                                        const isText =
                                            selected.is('text') ||
                                            selected.get('type') === 'text';
                                        const currentContent = selected.get('content') || '';

                                        // Fallback logic: Append to 'content' if it has value, otherwise append as new component
                                        if (currentContent.length > 0) {
                                            selected.set('content', currentContent + text);
                                        } else {
                                            selected.components().add(text);
                                        }

                                        // Attempt to activate the RTE (Rich Text Editor) on the component
                                        // The reliable way to do this in GrapeJS is to simulate the user interaction
                                        const view = selected.getView();
                                        if (view && view.el) {
                                            this.editor.select(selected);
                                            // Trigger double click to open RTE
                                            const dblClickEvent = new MouseEvent('dblclick', {
                                                bubbles: true,
                                                cancelable: true,
                                                view: window,
                                            });
                                            view.el.dispatchEvent(dblClickEvent);
                                        }
                                    }
                                }
                            } catch (error) {
                                console.error('GrapeEditor: Error inserting text', error);
                            }
                        });
                    },

                    initEditor(cssText = '') {
                        // Use the global plugin string identifier.
                        // The CDN version registers 'grapesjs-preset-newsletter' globally.
                        const plugins = ['grapesjs-preset-newsletter'];

                        this.editor = window.grapesjs.init({
                            container: this.$refs.editor,
                            height: '600px',
                            width: '100%',
                            noticeOnUnload: false,
                            telemetry: false,
                            fromElement: true,
                            fullPage: true,
                            plugins: plugins,
                            pluginsOpts: {
                                'grapesjs-preset-newsletter': {
                                    inlineCss: true,
                                    fromElement: true,
                                    fullPage: true,
                                    juiceOpts: {
                                        applyAttributesTableElements: false,
                                        extraCss: cssText,
                                    },
                                    // Block definition override
                                    block: (id) => {
                                        const common = {
                                            category: 'Basic'
                                        };
                                        if (id === 'button') {
                                            return {
                                                ...common,
                                                label: 'Button',
                                                content: '<a href="#" class="btn btn-primary">Button</a>',
                                            };
                                        }
                                        if (id === 'text') {
                                            return {
                                                ...common,
                                                label: 'Text',
                                                content: '<div class="font-sans text-base leading-relaxed text-gray-700 p-2">Insert your text here</div>',
                                            };
                                        }
                                        return {};
                                    },
                                },
                            },
                            storageManager: false,
                            assetManager: {
                                embedAsBase64: true,
                            },
                            canvas: {
                                styles: this.cssUrl ? [this.cssUrl] : [],
                            },
                        });

                        // Apply Theme, Lang, and Dir to Canvas HTML
                        this.editor.on('load', () => {
                            const frame = this.editor.Canvas.getDocument();
                            if (frame && frame.documentElement) {
                                frame.documentElement.setAttribute('data-theme', this.theme);
                                frame.documentElement.setAttribute('lang', this.lang);
                                frame.documentElement.setAttribute('dir', this.dir);
                            }
                        });

                        if (this.internalContent) {
                            this.editor.setComponents(this.internalContent);
                        }

                        this.editor.on('update', () => {
                            this.internalContent = this.getEditorContent();
                        });

                        // Add Merge Tag Button to RTE
                        if (this.editor.RichTextEditor) {
                            this.editor.RichTextEditor.add('merge-tag', {
                                icon: '<svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor"><path d="M18.5 2h-13C3.6 2 2 3.6 2 5.5v13C2 20.4 3.6 22 5.5 22h13c1.9 0 3.5-1.6 3.5-3.5v-13C22 3.6 20.4 2 18.5 2zM5.5 4h13c.8 0 1.5.7 1.5 1.5v13c0 .8-.7 1.5-1.5 1.5h-13c-.8 0-1.5-.7-1.5-1.5v-13c0-.8.7-1.5 1.5-1.5zM8 11h2v2H8zm6 0h2v2h-2z" /></svg>',
                                attributes: {
                                    title: 'Insert Merge Tag'
                                },
                                result: () => {
                                    window.dispatchEvent(
                                        new CustomEvent('open-merge-tag-modal'),
                                    );
                                },
                            });
                        }

                        // Remove unwanted buttons
                        const panels = this.editor.Panels;
                        panels.removeButton('options', 'sw-visibility');
                        panels.removeButton('options', 'preview');
                        panels.removeButton('options', 'gjs-open-import-template');
                        panels.removeButton('options', 'gjs-toggle-images');
                        panels.removeButton('views', 'open-layers');
                    },

                    getEditorContent() {
                        if (!this.editor) return '';
                        const html = this.editor.getHtml();
                        const css = this.editor.getCss();
                        return `<style>${css}</style>${html}`;
                    },

                    destroy() {
                        if (this.loadInterval) {
                            clearInterval(this.loadInterval);
                        }
                        if (this.editor) {
                            this.editor.destroy();
                        }
                    },
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
