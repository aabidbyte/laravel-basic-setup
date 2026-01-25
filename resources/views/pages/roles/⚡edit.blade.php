<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Notifications\NotificationBuilder;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    #[Locked]
    public ?string $roleUuid = null;

    // Form fields
    public string $name = '';

    public ?string $display_name = null;

    public ?string $description = null;

    /** @var array<string> */
    public array $selectedPermissions = [];

    /**
     * Mount the component and authorize access.
     */
    public function mount(?Role $role = null): void
    {
        $this->authorizeAccess($role);
        $this->checkSuperAdminProtection($role);
        $this->initializeUnifiedModel($role, $this->loadExistingRole(...), $this->prepareNewRole(...));
        $this->updatePageHeader();
    }

    protected function authorizeAccess(?Role $role): void
    {
        $permission = $role ? Permissions::EDIT_ROLES() : Permissions::CREATE_ROLES();

        $this->authorize($permission);
    }

    protected function checkSuperAdminProtection(?Role $role): void
    {
        // Only super_admin can edit the super_admin role
        if ($role && $role->name === Roles::SUPER_ADMIN && !auth()->user()?->hasRole(Roles::SUPER_ADMIN)) {
            abort(403);
        }
    }

    protected function loadExistingRole(Role $role): void
    {
        $this->roleUuid = $role->uuid;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->selectedPermissions = $role->permissions->pluck('uuid')->toArray();
    }

    protected function prepareNewRole(): void
    {
        // Nothing special needed for new roles
    }

    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => __('types.role')]);
            $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.role')]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => __('types.role'), 'name' => $this->name]);
            $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.role')]);
        }
    }

    /**
     * Get the role being edited.
     */
    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    protected function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,uuid'],
        ];
    }

    /**
     * Generate a unique slug name from display_name.
     */
    protected function generateUniqueName(string $displayName, ?string $currentName = null): string
    {
        $slug = Str::slug($displayName, '_');

        // If editing and generated slug matches current name, keep it
        if ($currentName && $slug === $currentName) {
            return $currentName;
        }

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while (Role::where('name', $slug)->when($currentName, fn($q) => $q->where('name', '!=', $currentName))->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function create(): void
    {
        $this->validate();

        // Auto-generate name from display_name
        $generatedName = $this->generateUniqueName($this->display_name);

        $role = Role::create([
            'name' => $generatedName,
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        $this->syncPermissions($role);
        $this->sendSuccessNotification($role, 'pages.common.create.success');
        $this->redirect(route('roles.index'), navigate: true);
    }

    public function save(): void
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

        // Update name if display_name changed (keep it in sync)
        $newName = $this->generateUniqueName($this->display_name, $role->name);

        $role->update([
            'name' => $newName,
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        $this->syncPermissions($role);
        $this->sendSuccessNotification($role, 'pages.common.edit.success');
        $this->redirect(route('roles.show', $role->uuid), navigate: true);
    }

    protected function syncPermissions(Role $role): void
    {
        if (!empty($this->selectedPermissions)) {
            $permissionIds = Permission::whereIn('uuid', $this->selectedPermissions)->pluck('id')->toArray();
            $role->syncPermissions($permissionIds);
        } else {
            $role->syncPermissions([]);
        }
    }

    protected function getRole(): ?Role
    {
        return Role::where('uuid', $this->roleUuid)->first();
    }

    public function getCancelUrlProperty(): string
    {
        return $this->isCreateMode ? route('roles.index') : route('roles.show', $this->roleUuid);
    }

    public function getIsSuperAdminProperty(): bool
    {
        return $this->name === Roles::SUPER_ADMIN;
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="role-form"
                     color="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm"></x-ui.loading>
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-4xl">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.form wire:submit="{{ $this->submitAction }}"
                           id="role-form"
                           class="space-y-6">
                    {{-- Basic Information --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('roles.edit.basic_info') }}</x-ui.title>

                        <x-ui.input type="text"
                                    wire:model="display_name"
                                    name="display_name"
                                    :label="__('roles.display_name')"
                                    :hint="__('roles.display_name_hint')"
                                    required
                                    autofocus></x-ui.input>

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

                        @if (!$this->isSuperAdmin)
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
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>
