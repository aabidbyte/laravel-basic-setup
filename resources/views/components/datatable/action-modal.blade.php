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

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('actionModal', () => ({
                    modalIsOpen: false,
                    isLoading: false,
                    _loadingListener: null,
                    _morphHookRemove: null,
                    _wireId: null,

                    closeModal() {
                        this.modalIsOpen = false;
                        this.$dispatch('datatable-close-modal');
                    },

                    confirmAction() {
                        this.$dispatch('datatable-confirm');
                    },

                    init() {
                        // Store wire ID on init for later comparison
                        this._wireId = this.$wire?.id;

                        // Entangle with Livewire
                        this.$watch('$wire.isOpen', (value) => {
                            this.modalIsOpen = value;
                        });
                        this.modalIsOpen = this.$wire?.isOpen ?? false;

                        // Store listener reference for cleanup
                        this._loadingListener = () => {
                            this.isLoading = true;
                            this.modalIsOpen = true;
                        };

                        // Listen for loading trigger
                        window.addEventListener(
                            'datatable-modal-loading',
                            this._loadingListener,
                        );

                        // Use Livewire hook to detect when component has finished updating
                        // Store the cleanup function returned by hook()
                        this._morphHookRemove = window.Livewire.hook(
                            'morph.updated',
                            ({
                                component
                            }) => {
                                // Check if wire instance still exists (not destroyed by navigation)
                                if (!this.$wire || !this.$wire.__instance) {
                                    return;
                                }
                                if (
                                    component.id === this._wireId &&
                                    this.modalIsOpen &&
                                    this.$wire.modalView
                                ) {
                                    this.isLoading = false;
                                }
                            },
                        );
                    },

                    destroy() {
                        // Clean up event listener on component destroy
                        if (this._loadingListener) {
                            window.removeEventListener(
                                'datatable-modal-loading',
                                this._loadingListener,
                            );
                        }
                        // Clean up Livewire hook
                        if (
                            this._morphHookRemove &&
                            typeof this._morphHookRemove === 'function'
                        ) {
                            this._morphHookRemove();
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
