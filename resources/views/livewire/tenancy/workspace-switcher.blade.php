<div x-data="workspaceSwitcher()"
     @prompt-workspace-selection.window="impersonationModalOpen = false">
    {{-- Trigger: The current workspace indicator at the top of the sidebar --}}
    <div @click="switcherOpen = true"
         class="border-base-content/10 bg-base-100 hover:bg-base-200 group flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3 transition-all">
        <div class="flex items-center gap-3">
            <x-ui.avatar initials="{{ strtoupper(substr(tenant('name') ?: __('tenancy.platform_initials'), 0, 1)) }}"
                         size="sm"
                         shape="square"
                         class="shadow-sm"></x-ui.avatar>
            <div class="flex flex-col text-left">
                <span class="text-base-content text-sm font-semibold leading-tight">
                    {{ tenant('name') ?: __('tenancy.central_platform') }}
                </span>
                <span class="text-base-content/60 text-xs">
                    {{ tenant('plan') ?: __('tenancy.system_admin') }}
                </span>
            </div>
        </div>
        <x-ui.icon name="chevron-up-down"
                   class="text-base-content/40 group-hover:text-base-content/70 h-4 w-4"></x-ui.icon>
    </div>

    {{-- The Switcher Modal --}}
    <x-ui.base-modal id="workspace-switcher-modal"
                     open-state="switcherOpen"
                     :use-parent-state="true"
                     :title="__('tenancy.switch_workspace')"
                     :description="__('tenancy.switch_workspace_description')"
                     size="lg">
        <div class="flex flex-col gap-4">
            <livewire:tables.workspace-table />

            <div class="border-base-content/5 flex justify-end gap-2 border-t pt-4">
                @can(\App\Constants\Auth\Permissions::IMPERSONATE_USERS())
                    <x-ui.button variant="secondary"
                                 size="sm"
                                 icon="user-secret"
                                 @click="impersonationModalOpen = true; switcherOpen = false">
                        {{ __('tenancy.impersonate_user') }}
                    </x-ui.button>
                @endcan

                <x-ui.button variant="primary"
                             size="sm"
                             icon="plus"
                             href="{{ route('tenants.create') }}"
                             wire:navigate>
                    {{ __('tenancy.create_workspace') }}
                </x-ui.button>
            </div>
        </div>
    </x-ui.base-modal>

    {{-- The Impersonation Modal --}}
    <x-ui.base-modal id="impersonate-user-modal"
                     open-state="impersonationModalOpen"
                     :use-parent-state="true"
                     :title="__('tenancy.impersonate_user')"
                     :description="__('tenancy.impersonate_user_description')"
                     size="xl">
        <div class="flex flex-col gap-4">
            <livewire:tables.impersonate-user-table />
        </div>
    </x-ui.base-modal>

    {{-- Option A: Workspace Selection Prompt --}}
    <div x-show="$wire.showSelectionModal"
         x-cloak
         class="bg-base-300/60 fixed inset-0 z-[10000] flex items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-base-100 rounded-box border-base-content/5 w-full max-w-md overflow-hidden border shadow-lg"
             @click.away="$wire.set('showSelectionModal', false)">
            <div class="border-base-content/5 border-b p-6">
                <h3 class="text-lg font-bold">{{ __('tenancy.select_target_workspace') }}</h3>
                <p class="text-base-content/60 mt-1 text-sm">{{ __('tenancy.select_target_workspace_description') }}</p>
            </div>
            <div class="max-h-[60vh] overflow-y-auto p-2">
                <div class="grid grid-cols-1 gap-1">
                    @foreach ($workspaces as $workspace)
                        <button wire:click="selectWorkspace('{{ $workspace['id'] }}')"
                                class="hover:bg-base-200 group flex items-center justify-between rounded-xl p-4 text-left transition-colors">
                            <div class="flex flex-col">
                                <span class="text-base-content font-semibold">{{ $workspace['name'] }}</span>
                                <span class="text-base-content/60 text-xs">{{ $workspace['id'] }}</span>
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
