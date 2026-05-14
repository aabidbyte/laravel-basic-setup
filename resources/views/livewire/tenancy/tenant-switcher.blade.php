@php
    use Illuminate\Support\Str;

    $currentTenant = $this->currentTenant;
    $currentTenantName = $currentTenant?->name ?: __('tenancy.central_platform');
    $currentTenantPlan = $currentTenant
        ? ($currentTenant->planModel?->name
            ?? ($currentTenant->plan ? __("tenancy.plans.{$currentTenant->plan}") : __('tenancy.free_plan')))
        : __('tenancy.system_admin');
    $currentTenantDomain = $currentTenant?->domains->first()?->domain;
    $currentTenantInitials = Str::of($currentTenantName)->trim()->substr(0, 1)->upper()->toString();
@endphp

<div x-data="tenantSwitcher"
     @prompt-tenant-selection.window="closeImpersonationModal">
    {{-- Trigger: The current tenant indicator at the top of the sidebar --}}
    <button type="button"
            @click="openSwitcher"
            class="border-base-content/10 bg-base-100 hover:border-primary/30 hover:bg-base-200/70 focus-visible:ring-primary/35 group flex min-h-14 w-full items-center justify-between gap-3 rounded-lg border px-3 py-2.5 text-left shadow-sm transition focus:outline-none focus-visible:ring-2">
        <div class="flex min-w-0 items-center gap-3">
            <x-ui.avatar initials="{{ $currentTenantInitials ?: __('tenancy.platform_initials') }}"
                         size="sm"
                         shape="square"
                         class="ring-base-content/10 shrink-0 shadow-sm ring-1"></x-ui.avatar>
            <div class="min-w-0 flex-1 overflow-hidden">
                <span class="text-base-content block truncate text-sm font-semibold leading-tight">
                    {{ $currentTenantName }}
                </span>
                @if($currentTenant)
                    <span class="border-primary/20 bg-primary/10 text-primary mt-1 inline-flex max-w-full items-center rounded-md border px-1.5 py-0.5 text-[10px] font-semibold leading-none">
                        <span class="truncate">
                            {{ $currentTenantPlan ?: __('tenancy.free_plan') }}
                        </span>
                    </span>
                @else
                    <span class="text-base-content/60 mt-1 block truncate text-[10px] font-semibold uppercase tracking-wide">
                        {{ __('tenancy.system_admin') }}
                    </span>
                @endif
            </div>
        </div>
        <x-ui.icon name="chevron-up-down"
                   class="text-base-content/40 group-hover:text-base-content/70 h-4 w-4 shrink-0 transition-colors"></x-ui.icon>
    </button>

    {{-- The Switcher Modal --}}
    <x-ui.base-modal id="tenant-switcher-modal"
                     open-state="$store.tenantSwitcher.switcherOpen"
                     :use-parent-state="true"
                     :title="__('tenancy.switch_tenant')"
                     :description="__('tenancy.switch_tenant_description')"
                     :custom-close="true"
                     on-close="closeSwitcher()"
                     size="lg">
        <div class="flex flex-col gap-4">
            <div class="border-base-content/10 bg-base-200/50 rounded-lg border p-4">
                <div class="flex items-start gap-3">
                    <x-ui.avatar initials="{{ $currentTenantInitials ?: __('tenancy.platform_initials') }}"
                                 size="md"
                                 shape="square"
                                 class="shadow-sm"></x-ui.avatar>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base-content font-semibold">{{ $currentTenantName }}</span>
                            <x-ui.badge color="{{ $currentTenant ? 'primary' : 'neutral' }}"
                                        size="sm">
                                {{ $currentTenantPlan ?: __('tenancy.free_plan') }}
                            </x-ui.badge>
                        </div>
                        <div class="text-base-content/60 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                            @if($currentTenantDomain)
                                <span>{{ $currentTenantDomain }}</span>
                                <span class="text-base-content/30">•</span>
                            @endif
                            <span>{{ $currentTenant ? $currentTenant->id : __('tenancy.system_admin') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <livewire:tables.tenant-table :is-switcher="true" />

            <div class="border-base-content/5 flex justify-end gap-2 border-t pt-4">
                @can(\App\Constants\Auth\Permissions::IMPERSONATE_USERS())
                    <x-ui.button variant="soft"
                                 size="sm"
                                 @click="openImpersonation()">
                        <x-ui.icon name="user-secret" pack="fontawesome" size="xs" class="mr-2" />
                        {{ __('tenancy.impersonate_user') }}
                    </x-ui.button>
                @endcan

                <x-ui.button variant="primary"
                             size="sm"
                             href="{{ route('tenants.create') }}"
                             wire:navigate>
                    <x-ui.icon name="plus" size="xs" class="mr-2" />
                    {{ __('tenancy.create_new_tenant') }}
                </x-ui.button>
            </div>
        </div>
    </x-ui.base-modal>

    {{-- The Impersonation Modal --}}
    <x-ui.base-modal id="impersonate-user-modal"
                     open-state="$store.tenantSwitcher.impersonationModalOpen"
                     :use-parent-state="true"
                     :title="__('tenancy.impersonate_user')"
                     :description="__('tenancy.impersonate_user_description')"
                     :custom-close="true"
                     on-close="closeImpersonationModal()"
                     size="xl">
        <div class="flex flex-col gap-4">
            <livewire:tables.impersonate-user-table />
        </div>
    </x-ui.base-modal>

    {{-- Option A: Tenant Selection Prompt --}}
    <div x-show="$wire.showSelectionModal"
         x-cloak
         class="bg-base-300/60 fixed inset-0 z-[10000] flex items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-base-100 rounded-box border-base-content/5 w-full max-w-md overflow-hidden border shadow-lg"
             @click.away="$wire.set('showSelectionModal', false)">
            <div class="border-base-content/5 border-b p-6">
                <h3 class="text-lg font-bold">{{ __('tenancy.select_target_tenant') }}</h3>
                <p class="text-base-content/60 mt-1 text-sm">{{ __('tenancy.select_target_tenant_description') }}</p>
            </div>
            <div class="max-h-[60vh] overflow-y-auto p-2">
                <div class="grid grid-cols-1 gap-1">
                    @foreach ($tenants as $tenant)
                        <button wire:click="selectTenant('{{ $tenant['id'] }}')"
                                class="hover:bg-base-200 group flex items-center justify-between rounded-xl p-4 text-left transition-colors">
                            <div class="flex flex-col">
                                <span class="text-base-content font-semibold">{{ $tenant['name'] }}</span>
                                <span class="text-base-content/60 text-xs">{{ $tenant['id'] }}</span>
                            </div>
                            <x-ui.icon name="chevron-right"
                                       class="text-base-content/30 group-hover:text-base-content/70 h-4 w-4"></x-ui.icon>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="bg-base-200/50 flex justify-end p-4">
                <x-ui.button variant="ghost"
                             size="sm"
                             @click="$wire.set('showSelectionModal', false)">
                    {{ __('actions.cancel') }}
                </x-ui.button>
            </div>
        </div>
    </div>
</div>

@assets
    <script @cspNonce>
        (function() {
            const ensureStore = () => {
                if (!window.Alpine) {
                    return;
                }

                if (window.Alpine.store('tenantSwitcher')) {
                    return;
                }

                window.Alpine.store('tenantSwitcher', {
                    switcherOpen: false,
                    impersonationModalOpen: false,
                });
            };

            const registerTenantSwitcher = () => {
                if (!window.Alpine) {
                    return;
                }

                ensureStore();

                window.Alpine.data('tenantSwitcher', () => ({
                    openSwitcher() {
                        this.$store.tenantSwitcher.switcherOpen = true;
                    },

                    closeSwitcher() {
                        this.$store.tenantSwitcher.switcherOpen = false;
                    },

                    openImpersonation() {
                        this.$store.tenantSwitcher.impersonationModalOpen = true;
                        this.$store.tenantSwitcher.switcherOpen = false;
                    },

                    closeImpersonationModal() {
                        this.$store.tenantSwitcher.impersonationModalOpen = false;
                    },

                    init() {
                        ensureStore();
                    },
                }));
            };

            if (window.Alpine) {
                registerTenantSwitcher();
            } else {
                document.addEventListener('alpine:init', registerTenantSwitcher);
            }
        })();
    </script>
@endassets
