@auth
    @push('endBody')
        <div x-data="toastCenter()" x-init="init()" aria-live="assertive"
            class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-[9999]">
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <template x-for="(toast, index) in toasts" :key="toast.id">
                    <div class="pointer-events-auto w-full max-w-sm rounded-lg bg-base-100 shadow-lg outline-1 outline-base-content/5"
                        @click="handleClick(toast)">
                        <div class="p-4">
                            <div class="flex items-start">
                                <div class="shrink-0">
                                    <div :class="getIconClasses(toast.type)">
                                        <div x-html="toast.iconHtml || getIconSvg(toast.iconName)"></div>
                                    </div>
                                </div>
                                <div class="ml-3 w-0 flex-1 pt-0.5">
                                    <p class="text-sm font-medium text-base-content" x-text="toast.title"></p>
                                    <p x-show="toast.subtitle" class="mt-1 text-sm text-base-content/70"
                                        x-text="toast.subtitle"></p>
                                    <div x-show="toast.content" class="mt-1 text-sm text-base-content/70"
                                        x-html="toast.content"></div>
                                    <div x-show="toast.link" class="mt-3 flex gap-7">
                                        <a :href="toast.link"
                                            class="rounded-md text-sm font-medium text-primary hover:text-primary-focus focus:outline-2 focus:outline-offset-2 focus:outline-primary">
                                            <span x-text="'View'"></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="ml-4 flex shrink-0">
                                    <button @click.stop="removeToast(toast.id)" type="button"
                                        class="btn btn-sm btn-ghost btn-circle text-base-content/40 hover:text-base-content/60 focus:outline-2 focus:outline-offset-2 focus:outline-primary"
                                        aria-label="Close">
                                        <span class="sr-only">Close</span>
                                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    @endpush
@endauth
