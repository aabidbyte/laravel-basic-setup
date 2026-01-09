<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Validation\Rule;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    // Form fields
    public string $name = '';
    public ?string $display_name = null;
    public ?string $description = null;

    /** @var array<int> */
    public array $selectedPermissions = [];

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::CREATE_ROLES);
        $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.role')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.create.title', ['type' => __('types.role')]);
    }

    /**
     * Get available permissions for selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Permission>
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
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,id'],
        ];
    }

    /**
     * Create the role.
     */
    public function createRole(): void
    {
        $this->validate();

        try {
            $role = Role::create([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
            ]);

            if (! empty($this->selectedPermissions)) {
                $role->syncPermissions($this->selectedPermissions);
            }

            NotificationBuilder::make()
                ->title('pages.common.create.success', ['name' => $role->label()])
                ->success()
                ->persist()
                ->send();

            $this->redirect(route('roles.index'), navigate: true);
        } catch (\Exception $e) {
            NotificationBuilder::make()
                ->title('pages.common.create.error', ['type' => __('types.role')])
                ->content($e->getMessage())
                ->error()
                ->send();
        }
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title
                level="2"
                class="mb-6"
            >{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form
                wire:submit="createRole"
                class="space-y-6"
            >
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70"
                    >{{ __('roles.create.basic_info') }}</x-ui.title>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input
                            type="text"
                            wire:model="name"
                            name="name"
                            :label="__('roles.name')"
                            required
                            autofocus
                        ></x-ui.input>

                        <x-ui.input
                            type="text"
                            wire:model="display_name"
                            name="display_name"
                            :label="__('roles.display_name')"
                        ></x-ui.input>
                    </div>

                    <x-ui.input
                        type="textarea"
                        wire:model="description"
                        name="description"
                        :label="__('roles.description')"
                        rows="3"
                    ></x-ui.input>
                </div>

                {{-- Permissions --}}
                <div class="divider"></div>
                <div class="space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70"
                    >{{ __('roles.permissions') }}</x-ui.title>

                    <x-ui.permission-matrix
                        :permissions="$this->permissions"
                        :selectedPermissions="$selectedPermissions"
                        wireModel="selectedPermissions"
                    ></x-ui.permission-matrix>
                </div>

                {{-- Submit --}}
                <div class="divider"></div>
                <div class="flex justify-end gap-4">
                    <x-ui.button
                        href="{{ route('roles.index') }}"
                        style="ghost"
                        wire:navigate
                    >{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        <x-ui.loading
                            wire:loading
                            wire:target="createRole"
                            size="sm"
                        ></x-ui.loading>
                        {{ __('pages.common.create.submit', ['type' => __('types.role')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
