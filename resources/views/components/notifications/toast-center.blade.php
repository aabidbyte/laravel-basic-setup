@auth
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
        $userUuid = $user->uuid ?? null;
        $teamUuid = $user->teams()->first()?->uuid ?? null;
    @endphp

    <div x-data="toastCenter(@js($userUuid), @js($teamUuid))" x-init="init()" class="pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="getEnterStart(toast.position)" x-transition:enter-end="getEnterEnd(toast.position)"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="getLeaveStart(toast.position)"
                x-transition:leave-end="getLeaveEnd(toast.position)" :class="getToastClasses(toast)"
                :style="getToastPosition(toast, index)"
                class="transform z-[9999] alert min-w-[300px] max-w-md shadow-lg pointer-events-auto mb-2"
                @click="handleClick(toast)">
                <div class="flex items-start gap-3">
                    <div :class="getIconClasses(toast.type)">
                        <x-ui.icon x-bind:name="toast.iconName" class="h-5 w-5" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold" x-text="toast.title"></h3>
                        <p x-show="toast.subtitle" class="text-sm opacity-80" x-text="toast.subtitle"></p>
                        <div x-show="toast.content" class="text-sm mt-1" x-html="toast.content"></div>
                        <a x-show="toast.link" :href="toast.link" class="text-sm underline mt-1 block"
                            x-text="'View'"></a>
                    </div>
                    <button @click.stop="removeToast(toast.id)" class="btn btn-sm btn-ghost btn-circle" aria-label="Close">
                        <x-ui.icon name="x-mark" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </template>
    </div>

    <script>
        // Global toast manager - singleton pattern to prevent duplicate Echo subscriptions
        // This ensures subscriptions are created only once, even if component re-initializes
        if (!window.__toastManager) {
            window.__toastManager = {
                subscriptions: {},
                callbacks: new Set(),

                subscribe(channelKey, channelName, callback) {
                    // Register callback first
                    this.callbacks.add(callback);

                    // Only create Echo subscription once per channel
                    if (!this.subscriptions[channelKey]) {
                        if (typeof window.Echo === 'undefined') {
                            console.warn('Echo is not available. Toast notifications will not work.');
                            return;
                        }

                        this.subscriptions[channelKey] = window.Echo.private(channelName)
                            .listen('.toast.received', (e) => {
                                // Notify all registered callbacks
                                this.callbacks.forEach(cb => {
                                    try {
                                        cb(e);
                                    } catch (error) {
                                        console.error('Error in toast callback:', error);
                                    }
                                });
                            });
                    }
                },

                unsubscribe(callback) {
                    this.callbacks.delete(callback);
                }
            };
        }

        function toastCenter(userUuid, teamUuid) {
            return {
                toasts: [],
                userUuid: userUuid,
                teamUuid: teamUuid,
                callback: null,

                init() {
                    if (typeof window.Echo === 'undefined') {
                        console.warn('Echo is not available. Toast notifications will not work.');
                        return;
                    }

                    // Create callback bound to this component instance
                    const component = this;
                    this.callback = (e) => component.addToast(e);

                    // Subscribe to user channel (subscriptions are managed globally to prevent duplicates)
                    if (this.userUuid) {
                        window.__toastManager.subscribe(
                            `user-${this.userUuid}`,
                            `private-notifications.user.${this.userUuid}`,
                            this.callback
                        );
                    }

                    // Subscribe to team channel
                    if (this.teamUuid) {
                        window.__toastManager.subscribe(
                            `team-${this.teamUuid}`,
                            `private-notifications.team.${this.teamUuid}`,
                            this.callback
                        );
                    }

                    // Subscribe to global channel
                    window.__toastManager.subscribe(
                        'global',
                        'private-notifications.global',
                        this.callback
                    );
                },

                destroy() {
                    // Unregister callback when component is destroyed
                    if (this.callback) {
                        window.__toastManager.unsubscribe(this.callback);
                    }
                },

                addToast(data) {
                    const type = data.type || 'success';
                    const now = Date.now();
                    const toast = {
                        id: Date.now() + Math.random(),
                        timestamp: now,
                        title: data.title || '',
                        subtitle: data.subtitle || null,
                        content: data.content || null,
                        type: type,
                        iconName: this.getIconName(type),
                        position: data.position || 'top-right',
                        animation: data.animation || 'slide',
                        link: data.link || null,
                    };

                    this.toasts.push(toast);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        this.removeToast(toast.id);
                    }, 5000);
                },

                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index > -1) {
                        // Remove directly from array - Alpine.js will handle DOM removal with x-transition
                        this.toasts.splice(index, 1);
                    }
                },

                handleClick(toast) {
                    if (toast.link) {
                        window.location.href = toast.link;
                    }
                },

                getIconName(type) {
                    const icons = {
                        'success': 'check-circle',
                        'info': 'information-circle',
                        'warning': 'exclamation-triangle',
                        'error': 'x-circle',
                        'classic': 'bell',
                    };
                    return icons[type] || 'bell';
                },

                getIconClasses(type) {
                    const classes = {
                        'success': 'text-success',
                        'info': 'text-info',
                        'warning': 'text-warning',
                        'error': 'text-error',
                        'classic': 'text-base-content',
                    };
                    return classes[type] || 'text-base-content';
                },

                getToastClasses(toast) {
                    const typeClasses = {
                        'success': 'alert-success',
                        'info': 'alert-info',
                        'warning': 'alert-warning',
                        'error': 'alert-error',
                        'classic': 'alert',
                    };
                    return typeClasses[toast.type] || 'alert';
                },

                getToastPosition(toast, index) {
                    const baseOffset = 1; // 1rem
                    const toastHeight = 5; // Approximate height in rem
                    const spacing = 0.5; // Spacing between toasts in rem
                    const offsetY = index * (toastHeight + spacing);
                    const position = toast.position || 'top-right';

                    const positions = {
                        'top-right': `position: fixed; top: ${baseOffset + offsetY}rem; right: 1rem; left: auto; bottom: auto;`,
                        'top-left': `position: fixed; top: ${baseOffset + offsetY}rem; left: 1rem; right: auto; bottom: auto;`,
                        'top-center': `position: fixed; top: ${baseOffset + offsetY}rem; left: 50%; right: auto; bottom: auto;`,
                        'bottom-right': `position: fixed; bottom: ${baseOffset + offsetY}rem; right: 1rem; left: auto; top: auto;`,
                        'bottom-left': `position: fixed; bottom: ${baseOffset + offsetY}rem; left: 1rem; right: auto; top: auto;`,
                        'bottom-center': `position: fixed; bottom: ${baseOffset + offsetY}rem; left: 50%; right: auto; top: auto; --tw-translate-x: -50%;`,
                        'center': `position: fixed; top: 50%; left: 50%; right: auto; bottom: auto;`,
                    };

                    return positions[position] || positions['top-right'];
                },


                getEnterStart(position) {
                    const starts = {
                        'top-right': 'opacity-0 transform translate-x-full',
                        'top-left': 'opacity-0 transform -translate-x-full',
                        'top-center': 'opacity-0 transform -translate-y-full',
                        'bottom-right': 'opacity-0 transform translate-x-full',
                        'bottom-left': 'opacity-0 transform -translate-x-full',
                        'bottom-center': 'opacity-0 transform translate-y-full',
                        'center': 'opacity-0 transform scale-95',
                    };
                    return starts[position] || starts['top-right'];
                },

                getEnterEnd(position) {
                    return 'opacity-100 transform translate-x-0 translate-y-0 scale-100';
                },

                getLeaveStart(position) {
                    return 'opacity-100 transform translate-x-0 translate-y-0 scale-100';
                },

                getLeaveEnd(position) {
                    const ends = {
                        'top-right': 'opacity-0 transform translate-x-full',
                        'top-left': 'opacity-0 transform -translate-x-full',
                        'top-center': 'opacity-0 transform -translate-y-full',
                        'bottom-right': 'opacity-0 transform translate-x-full',
                        'bottom-left': 'opacity-0 transform -translate-x-full',
                        'bottom-center': 'opacity-0 transform translate-y-full',
                        'center': 'opacity-0 transform scale-95',
                    };
                    return ends[position] || ends['top-right'];
                },

            };
        }
    </script>
@endauth
