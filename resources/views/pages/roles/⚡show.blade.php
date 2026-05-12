<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.role';

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    #[Locked]
    public ?Role $role = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Role $role): void
    {
        $this->authorize(Permissions::VIEW_ROLES());

        // Only super_admin can view the super_admin role
        if ($role->name === Roles::SUPER_ADMIN && !Auth::user()?->hasRole(Roles::SUPER_ADMIN)) {
            abort(403);
        }

        $this->role = $role->load(['permissions']);
        $this->updatePageHeader();
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = 'pages.common.show.title';
        $this->pageSubtitle = 'pages.common.show.subtitle';
    }

    /**
     * Override getPageTitle to provide dynamic parameters.
     */
    public function getPageTitle(): string
    {
        $title = parent::getPageTitle();
        return __($title, [
            'name' => $this->role->display_name ?? $this->role->name,
            'type' => __($this->modelTypeLabel),
        ]);
    }

    /**
     * Override getPageSubtitle to provide type parameter.
     */
    public function getPageSubtitle(): ?string
    {
        $subtitle = parent::getPageSubtitle();
        return $subtitle ? __($subtitle, ['type' => __($this->modelTypeLabel)]) : null;
    }

    /**
     * Get available permissions.
     */
    #[Computed]
    public function permissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }
}; ?>

<x-layouts.page backHref="{{ route('roles.index') }}">
    <x-slot:topActions>
        @can(Permissions::EDIT_ROLES())
            <x-ui.button href="{{ route('roles.edit', $role->uuid) }}"
                         wire:navigate
                         variant="primary"
                         size="sm"
                         class="gap-2">
                <x-ui.icon name="pencil"
                           size="sm" />
                {{ __('actions.edit') }}
            </x-ui.button>
        @endcan
    </x-slot:topActions>

    <div class="max-col-6xl mx-auto w-full space-y-8">
        {{-- Role Details Card --}}
        <x-ui.card title="{{ __('roles.show.basic_info') }}">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('roles.display_name') }}</span>
                    <p class="font-medium">{{ $role->display_name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('roles.name') }}</span>
                    <p class="font-mono text-sm">{{ $role->name }}</p>
                </div>
                <div class="md:col-span-2">
                    <span class="text-base-content/60 text-sm">{{ __('roles.description') }}</span>
                    <p class="text-base-content">{{ $role->description ?? '-' }}</p>
                </div>
            </div>
        </x-ui.card>

        {{-- Permissions --}}
        <x-ui.card>
            <x-slot:title>
                <div class="flex items-center justify-between">
                    <span>{{ __('roles.permissions') }}</span>
                    <x-ui.badge variant="info">{{ $role->permissions->count() }}</x-ui.badge>
                </div>
            </x-slot:title>

            @if ($role->name === Roles::SUPER_ADMIN)
                <div class="alert alert-info">
                    <x-ui.icon name="shield-check"
                               class="h-6 w-6" />
                    <span>{{ __('roles.super_admin_all_permissions') }}</span>
                </div>
            @else
                <x-ui.permission-matrix :permissions="$this->permissions"
                                        :selectedPermissions="$role->permissions->pluck('uuid')->toArray()"
                                        readonly />
            @endif
        </x-ui.card>

        {{-- Users with this Role --}}
        <x-ui.card title="{{ __('roles.users_with_role') }}">
            <livewire:tables.role-user-table :role-uuid="$role->uuid"
                                             lazy />
        </x-ui.card>
    </div>
</x-layouts.page>
