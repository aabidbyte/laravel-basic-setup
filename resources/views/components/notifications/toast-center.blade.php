@push('endBody')
    <div
        x-data="toastCenter"
        x-init="init()"
        aria-live="assertive"
        class="pointer-events-none fixed z-[9999] flex max-w-sm flex-col gap-2 p-6"
        :class="getContainerPosition()"
        @mouseenter="$dispatch('pause-auto-dismiss')"
        @mouseleave="$dispatch('resume-auto-dismiss')"
    >
        <template
            x-for="(toast, index) in toasts"
            :key="toast.id"
        >
            <div
                x-data="toastItem(toast, toasts, displayDuration)"
                x-cloak
                x-show="isVisible"
                :class="getWrapperClass()"
                role="alert"
                @pause-auto-dismiss.window="handlePauseDismiss()"
                @resume-auto-dismiss.window="handleResumeDismiss()"
                @click="handleClick()"
                x-transition:enter="transition duration-300 ease-out"
                x-transition:enter-start="translate-y-8 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition duration-300 ease-in"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-24 opacity-0 md:translate-x-24"
            >
                {{-- Progress Bar --}}
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-base-200">
                    <div
                        :class="getProgressBarClass()"
                        :style="getProgressBarStyle()"
                        style="transition: width 16ms linear;"
                    ></div>
                </div>

                <div :class="getContentClass()">
                    {{-- Icon --}}
                    <div
                        :class="getIconClass()"
                        aria-hidden="true"
                    >
                        {{-- Success Icon --}}
                        <template x-if="toast.type === 'success'">
                            <x-ui.icon
                                name="check-circle"
                                class="h-6 w-6"
                            />
                        </template>

                        {{-- Info Icon --}}
                        <template x-if="toast.type === 'info'">
                            <x-ui.icon
                                name="information-circle"
                                class="h-6 w-6"
                            />
                        </template>

                        {{-- Warning Icon --}}
                        <template x-if="toast.type === 'warning'">
                            <x-ui.icon
                                name="exclamation-triangle"
                                class="h-6 w-6"
                            />
                        </template>

                        {{-- Error Icon --}}
                        <template x-if="toast.type === 'error'">
                            <x-ui.icon
                                name="x-circle"
                                class="h-6 w-6"
                            />
                        </template>

                        {{-- Classic/Default Icon (Bell) --}}
                        <template x-if="!['success', 'info', 'warning', 'error'].includes(toast.type)">
                            <x-ui.icon
                                name="bell"
                                class="h-6 w-6"
                            />
                        </template>
                    </div>

                    {{-- Title & Message --}}
                    <div class="flex flex-1 flex-col gap-2">
                        <h3
                            x-cloak
                            x-show="toast.title"
                            :class="getTitleClass()"
                            x-text="toast.title"
                        ></h3>
                        <p
                            x-cloak
                            x-show="toast.subtitle"
                            class="text-pretty text-sm text-base-content/70"
                            x-text="toast.subtitle"
                        ></p>
                        <div
                            x-cloak
                            x-show="toast.content"
                            class="text-pretty text-sm text-base-content/70"
                            x-text="toast.content"
                        ></div>
                        <div
                            x-cloak
                            x-show="toast.link"
                            class="mt-1"
                        >
                            <a
                                :href="toast.link"
                                :class="getLinkClass()"
                            >
                                <span>{{ __('notifications.view') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Dismiss Button --}}
                    <button
                        @click.stop="dismiss()"
                        type="button"
                        class="ml-auto shrink-0 text-base-content/40 hover:text-base-content/60 focus:outline-2 focus:outline-offset-2 focus:outline-base-content"
                        aria-label="{{ __('notifications.dismiss') }}"
                    >
                        <x-ui.icon
                            name="x-mark"
                            class="h-5 w-5"
                        ></x-ui.icon>
                    </button>
                </div>
            </div>
        </template>
    </div>
@endpush
