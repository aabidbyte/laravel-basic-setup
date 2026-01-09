<?php

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Role;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'card';

    #[Locked]
    public string $roleUuid = '';

    public ?Role $role = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Role $role): void
    {
        $this->authorize(Permissions::VIEW_ROLES);
        
        $this->roleUuid = $role->uuid;
        $this->role = $role->load(['permissions']);
        
        $this->pageSubtitle = __('pages.common.show.description', ['type' => __('types.role')]);
    }

    public function getPageTitle(): string
    {
        return $this->role?->label() ?? __('types.role');
    }
}; ?>

<section class="w-full max-w-6xl mx-auto space-y-6">
    {{-- Role Details Card --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-start">
                <x-ui.title
                    level="2"
                    class="mb-6"
                >{{ $this->getPageTitle() }}</x-ui.title>

                @can(Permissions::EDIT_ROLES)
                    <x-ui.button
                        href="{{ route('roles.edit', $roleUuid) }}"
                        wire:navigate
                        variant="ghost"
                        class="gap-2"
                    >
                        <x-ui.icon
                            name="pencil"
                            size="sm"
                        ></x-ui.icon>
                        {{ __('actions.edit') }}
                    </x-ui.button>
                @endcan
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Basic Info --}}
                <div class="space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70"
                    >{{ __('roles.show.basic_info') }}</x-ui.title>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-base-content/60">{{ __('roles.name') }}</dt>
                            <dd class="text-base-content font-semibold">{{ $role->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-base-content/60">{{ __('roles.display_name') }}</dt>
                            <dd class="text-base-content">{{ $role->display_name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-base-content/60">{{ __('roles.description') }}</dt>
                            <dd class="text-base-content">{{ $role->description ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Permissions --}}
                <div class="space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70"
                    >{{ __('roles.permissions') }} ({{ $role->permissions->count() }})</x-ui.title>

                    @if ($role->name === \App\Constants\Auth\Roles::SUPER_ADMIN)
                        <div class="alert alert-info">
                            <x-ui.icon name="shield-check" class="w-6 h-6"></x-ui.icon>
                            <span>{{ __('roles.super_admin_all_permissions') }}</span>
                        </div>
                    @else
                        <x-ui.permission-matrix
                            :permissions="\App\Models\Permission::all()"
                            :selectedPermissions="$role->permissions->pluck('id')->toArray()"
                            :readonly="true"
                        ></x-ui.permission-matrix>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Users with this Role --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title
                level="3"
                class="mb-4"
            >{{ __('roles.users_with_role') }}</x-ui.title>

            <livewire:tables.role-user-table
                :role-uuid="$roleUuid"
                lazy
            ></livewire:tables.role-user-table>
        </div>
    </div>

    {{-- Back Button --}}
    <div class="flex justify-start">
        <x-ui.button
            href="{{ route('roles.index') }}"
            wire:navigate
            variant="ghost"
            class="gap-2"
        >
            <x-ui.icon
                name="arrow-left"
                size="sm"
            ></x-ui.icon>
            {{ __('actions.back_to_list') }}
        </x-ui.button>
    </div>
</section>
