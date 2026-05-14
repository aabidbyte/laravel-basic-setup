<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.role';

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 3;

    #[Locked]
    public ?string $roleUuid = null;

    /**
     * Form fields
     */
    public string $name = '';

    public ?string $display_name = null;

    public ?string $description = null;

    /** @var array<string> */
    public array $selectedPermissions = [];

    /**
     * Initialize the component.
     */
    public function mount(?Role $role = null): void
    {
        $this->authorizeAccess($role);
        $this->checkSuperAdminProtection($role);
        $this->initializeUnifiedModel($role, fn ($r) => $this->loadExistingRole($r), fn () => $this->prepareNewRole());
        $this->updatePageHeader();
    }

    /**
     * Authorize access to the component.
     */
    protected function authorizeAccess(?Role $role): void
    {
        $permission = $role ? Permissions::EDIT_ROLES() : Permissions::CREATE_ROLES();
        $this->authorize($permission);
    }

    /**
     * Protect super_admin role from unauthorized modification.
     */
    protected function checkSuperAdminProtection(?Role $role): void
    {
        if ($role && $role->name === Roles::SUPER_ADMIN && !Auth::user()?->hasRole(Roles::SUPER_ADMIN)) {
            abort(403);
        }
    }

    /**
     * Load existing role data into form fields.
     */
    protected function loadExistingRole(Role $role): void
    {
        $this->model = $role;
        $this->roleUuid = $role->uuid;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->selectedPermissions = $role->permissions->pluck('uuid')->toArray();
    }

    /**
     * Prepare a new role model with defaults.
     */
    protected function prepareNewRole(): void
    {
        $this->model = new Role();
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = 'pages.common.create.title';
            $this->pageSubtitle = 'pages.common.create.description';
        } else {
            $this->pageTitle = 'pages.common.edit.title';
            $this->pageSubtitle = 'pages.common.edit.description';
        }
    }

    /**
     * Override getPageTitle to provide type parameter.
     */
    public function getPageTitle(): string
    {
        $title = parent::getPageTitle();
        $params = ['type' => __($this->modelTypeLabel)];

        if (!$this->isCreateMode && $this->display_name) {
            $params['name'] = $this->display_name;
        }

        return __($title, $params);
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
     * Get available permissions for selection.
     */
    #[Computed]
    public function permissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => [
                'exists:permissions,uuid',
                function ($attribute, $value, $fail) {
                    $permission = Permission::where('uuid', $value)->first();
                    if ($permission && !in_array($permission->name, Permissions::all())) {
                        $fail("The selected permission {$permission->name} is invalid.");
                    }
                }
            ],
        ];
    }

    /**
     * Generate a unique slug name from display_name.
     */
    protected function generateUniqueName(string $displayName, ?string $currentName = null): string
    {
        $slug = Str::slug($displayName, '_');

        if ($currentName && $slug === $currentName) {
            return $currentName;
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Role::where('name', $slug)->when($currentName, fn ($q) => $q->where('name', '!=', $currentName))->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle model creation.
     */
    public function create(): void
    {
        $this->validate();

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

    /**
     * Handle model update.
     */
    public function save(): void
    {
        $this->validate();

        if (!$this->model) {
            NotificationBuilder::make()
                ->title('pages.common.not_found', ['type' => __($this->modelTypeLabel)])
                ->error()
                ->send();

            return;
        }

        $newName = $this->generateUniqueName($this->display_name, $this->model->name);

        $this->model->update([
            'name' => $newName,
            'display_name' => $this->display_name,
            'description' => $this->description,
        ]);

        $this->syncPermissions($this->model);
        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect(route('roles.show', $this->model->uuid), navigate: true);
    }

    /**
     * Sync permissions for the role.
     */
    protected function syncPermissions(Role $role): void
    {
        if (!empty($this->selectedPermissions)) {
            $permissionIds = Permission::whereIn('uuid', $this->selectedPermissions)->pluck('id')->toArray();
            $role->syncPermissions($permissionIds);
        } else {
            $role->syncPermissions([]);
        }
    }

    /**
     * Get the URL to redirect back to.
     */
    #[Computed]
    public function cancelUrl(): string
    {
        return $this->getCancelUrl('roles.index', 'roles.show', $this->model);
    }

    /**
     * Determine if current role is Super Admin.
     */
    #[Computed]
    public function isSuperAdmin(): bool
    {
        return $this->name === Roles::SUPER_ADMIN;
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="role-form"
                     variant="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm" />
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <div class="mx-auto w-full max-w-4xl">
        <x-ui.card>
            <x-ui.form wire:submit="{{ $this->submitAction }}"
                       id="role-form">
                {{-- Basic Information --}}
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('roles.edit.basic_info') }}</x-ui.title>

                    <x-ui.input type="text"
                                wire:model="display_name"
                                name="display_name"
                                :label="__('roles.display_name')"
                                :hint="__('roles.display_name_hint')"
                                required
                                autofocus />

                    <x-ui.input type="textarea"
                                wire:model="description"
                                name="description"
                                :label="__('roles.description')"
                                rows="3" />
                </div>

                {{-- Permissions --}}
                <div class="divider"></div>
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('roles.permissions') }}</x-ui.title>

                    @if (!$this->isSuperAdmin)
                        <x-ui.permission-matrix :permissions="$this->permissions"
                                                :selectedPermissions="$selectedPermissions"
                                                wireModel="selectedPermissions" />
                    @else
                        <div class="alert alert-info">
                            <x-ui.icon name="shield-check"
                                       class="h-6 w-6" />
                            <span>{{ __('roles.super_admin_all_permissions') }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
