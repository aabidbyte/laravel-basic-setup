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
                :class="`pointer-events-auto relative rounded-lg border ${toast.typeClasses.border} bg-base-100 text-base-content overflow-hidden`"
                role="alert"
                @pause-auto-dismiss.window="clearTimeout(timeout); pauseProgress()"
                @resume-auto-dismiss.window="timeout = setTimeout(() => { isVisible = false; removeToast() }, displayDuration - elapsedTime); resumeProgress()"
                @click="handleClick()"
                x-init="$nextTick(() => {
                    isVisible = true;
                    startProgress();
                }), (timeout = setTimeout(() => {
                    isVisible = false;
                    removeToast();
                }, displayDuration))"
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
                        :class="`h-full ${toast.progressColor}`"
                        :style="`width: ${progress}%`"
                        style="transition: width 16ms linear;"
                    ></div>
                </div>

                <div
                    :class="`flex w-full items-center gap-2.5 rounded-lg ${toast.typeClasses.bgOverlay} p-4 transition-all duration-300`">
                    {{-- Icon --}}
                    <div
                        :class="`shrink-0 rounded-full ${toast.typeClasses.iconBg} ${toast.typeClasses.iconText}`"
                        aria-hidden="true"
                    >
                        <div
                            x-html="toast.iconHtml"
                            class="h-6 w-6"
                        ></div>
                    </div>

                    {{-- Title & Message --}}
                    <div class="flex flex-1 flex-col gap-2">
                        <h3
                            x-cloak
                            x-show="toast.title"
                            :class="`text-sm font-semibold ${toast.typeClasses.titleText}`"
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
                            x-html="toast.content"
                        ></div>
                        <div
                            x-cloak
                            x-show="toast.link"
                            class="mt-1"
                        >
                            <a
                                :href="toast.link"
                                :class="`text-sm font-medium ${toast.typeClasses.linkText} focus:outline-2 focus:outline-offset-2`"
                            >
                                <span>{{ __('ui.notifications.view') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Dismiss Button --}}
                    <button
                        @click.stop="isVisible = false; clearTimeout(timeout); if (progressInterval) clearInterval(progressInterval); removeToast()"
                        type="button"
                        class="ml-auto shrink-0 text-base-content/40 hover:text-base-content/60 focus:outline-2 focus:outline-offset-2 focus:outline-base-content"
                        aria-label="{{ __('ui.notifications.dismiss') }}"
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
