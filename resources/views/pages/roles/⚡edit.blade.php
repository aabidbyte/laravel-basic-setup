<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    #[Locked]
    public string $roleUuid = '';

    // Form fields
    public string $name = '';
    public ?string $display_name = null;
    public ?string $description = null;

    /** @var array<string> */
    public array $selectedPermissions = [];

    /**
     * Mount the component and authorize access.
     */
    public function mount(Role $role): void
    {
        $this->authorize(Permissions::EDIT_ROLES);

        // Only super_admin can edit the super_admin role
        if ($role->name === Roles::SUPER_ADMIN && !auth()->user()?->hasRole(Roles::SUPER_ADMIN)) {
            abort(403);
        }

        $this->roleUuid = $role->uuid;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->selectedPermissions = $role->permissions->pluck('uuid')->toArray();

        $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.role')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.edit.title', ['type' => __('types.role'), 'name' => $this->name]);
    }

    /**
     * Get the role being edited.
     */
    protected function getRole(): ?Role
    {
        return Role::where('uuid', $this->roleUuid)->first();
    }

    /**
     * Get available permissions for selection.
     *
     * @return Collection<int, Permission>
     */
    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $role = $this->getRole();

        return [
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,uuid'],
        ];
    }

    /**
     * Update the role.
     */
    public function updateRole(): void
    {
        $this->validate();

        $role = $this->getRole();

        if (!$role) {
            NotificationBuilder::make()
                ->title('pages.common.not_found', ['type' => __('types.role')])
                ->error()
                ->send();

            return;
        }

        $role->update([
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        // Retrieve permission IDs from UUIDs
        $permissionIds = Permission::whereIn('uuid', $this->selectedPermissions)->pluck('id')->toArray();
        $role->syncPermissions($permissionIds);

        NotificationBuilder::make()
            ->title('pages.common.edit.success', ['name' => $role->label()])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('roles.show', $role->uuid), navigate: true);
    }
}; ?>

<section class="mx-auto w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title level="2"
                        class="mb-6">{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form wire:submit="updateRole"
                       class="space-y-6">
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('roles.edit.basic_info') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-ui.input type="text"
                                    wire:model="display_name"
                                    name="display_name"
                                    :label="__('roles.display_name')"
                                    required
                                    autoFocus></x-ui.input>
                        <x-ui.input type="text"
                                    wire:model="name"
                                    name="name"
                                    :label="__('roles.name')"
                                    required
                                    disabled></x-ui.input>
                    </div>

                    <x-ui.input type="textarea"
                                wire:model="description"
                                name="description"
                                :label="__('roles.description')"
                                rows="3"></x-ui.input>
                </div>

                {{-- Permissions --}}
                <div class="divider"></div>
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('roles.permissions') }}</x-ui.title>

                    @if ($name !== Roles::SUPER_ADMIN)
                        <x-ui.permission-matrix :permissions="$this->permissions"
                                                :selectedPermissions="$selectedPermissions"
                                                wireModel="selectedPermissions"></x-ui.permission-matrix>
                    @else
                        <div class="alert alert-info">
                            <x-ui.icon name="shield-check"
                                       class="h-6 w-6"></x-ui.icon>
                            <span>{{ __('roles.super_admin_all_permissions') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Submit --}}
                <div class="divider"></div>
                <div class="flex justify-end gap-4">
                    <x-ui.button href="{{ route('roles.show', $roleUuid) }}"
                                 variant="ghost"
                                 wire:navigate>{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button type="submit"
                                 variant="primary">
                        <x-ui.loading wire:loading
                                      wire:target="updateRole"
                                      size="sm"></x-ui.loading>
                        {{ __('pages.common.edit.submit', ['type' => __('types.role')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
