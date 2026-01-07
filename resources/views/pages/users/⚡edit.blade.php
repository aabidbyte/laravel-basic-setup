<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 4;

    public ?User $editUser = null;

    // Form fields
    public string $name = '';
    public ?string $username = null;
    public ?string $email = null;
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $timezone = null;
    public ?string $locale = null;
    public bool $is_active = false;

    /** @var array<int> */
    public array $selectedRoles = [];

    /** @var array<int> */
    public array $selectedTeams = [];

    /**
     * Mount the component and authorize access.
     */
    public function mount(string $user): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $this->editUser = User::where('uuid', $user)->firstOrFail();
        $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.user')]);

        // Populate form fields
        $this->name = $this->editUser->name;
        $this->username = $this->editUser->username;
        $this->email = $this->editUser->email;
        $this->timezone = $this->editUser->timezone ?? config('app.timezone');
        $this->locale = $this->editUser->locale ?? config('app.locale');
        $this->is_active = $this->editUser->is_active;
        $this->selectedRoles = $this->editUser->roles->pluck('id')->toArray();
        $this->selectedTeams = $this->editUser->teams->pluck('id')->toArray();
    }

    /**
     * Get available roles for selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function getRolesProperty()
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Get available teams for selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Team>
     */
    public function getTeamsProperty()
    {
        return Team::orderBy('name')->get();
    }

    /**
     * Get available timezones.
     *
     * @return array<string, string>
     */
    public function getTimezonesProperty(): array
    {
        return collect(\DateTimeZone::listIdentifiers())->mapWithKeys(fn($tz) => [$tz => $tz])->toArray();
    }

    /**
     * Get available locales.
     *
     * @return array<string, string>
     */
    public function getLocalesProperty(): array
    {
        return collect(config('i18n.supported_locales'))->mapWithKeys(fn($data, $locale) => [$locale => $data['native_name']])->toArray();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->editUser->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->editUser->id)],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'timezone' => ['required', 'string', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['exists:roles,id'],
            'selectedTeams' => ['array'],
            'selectedTeams.*' => ['exists:teams,id'],
        ];
    }

    /**
     * Update the user.
     */
    public function updateUser(): void
    {
        $validated = $this->validate();

        try {
            $userService = app(UserService::class);

            $user = $userService->updateUser(
                user: $this->editUser,
                data: [
                    'name' => $this->name,
                    'username' => $this->username,
                    'email' => $this->email,
                    'password' => $this->password ?: null,
                    'timezone' => $this->timezone,
                    'locale' => $this->locale,
                    'is_active' => $this->is_active,
                ],
                roleIds: $this->selectedRoles,
                teamIds: $this->selectedTeams,
            );

            NotificationBuilder::make()
                ->title('pages.common.edit.success', ['name' => $user->name])
                ->success()
                ->persist()
                ->send();

            $this->redirect(route('users.show', $user->uuid), navigate: true);
        } catch (\Exception $e) {
            NotificationBuilder::make()
                ->title('pages.common.edit.error', ['type' => __('types.user')])
                ->content($e->getMessage())
                ->error()
                ->send();
        }
    }

    public function getPageTitle(): string
    {
        return __('pages.common.edit.title', ['type' => __('types.user')]);
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    @if ($editUser)
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.title
                    level="2"
                    class="mb-6"
                >{{ $this->getPageTitle() }}</x-ui.title>

                <x-ui.form
                    wire:submit="updateUser"
                    class="space-y-6"
                >
                    {{-- Basic Information --}}
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70"
                        >{{ __('users.edit.basic_info') }}</x-ui.title>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                type="text"
                                wire:model="name"
                                name="name"
                                :label="__('users.name')"
                                required
                                autofocus
                            ></x-ui.input>

                            <x-ui.input
                                type="text"
                                wire:model="username"
                                name="username"
                                :label="__('users.username')"
                                required
                            ></x-ui.input>
                        </div>

                        <x-ui.input
                            type="email"
                            wire:model="email"
                            name="email"
                            :label="__('users.email')"
                        ></x-ui.input>
                    </div>

                    {{-- Password (optional on edit) --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70"
                        >{{ __('users.edit.password') }}</x-ui.title>
                        <p class="text-sm text-base-content/60">{{ __('users.edit.password_hint') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                type="password"
                                wire:model="password"
                                name="password"
                                :label="__('users.password')"
                                autocomplete="new-password"
                            ></x-ui.input>

                            <x-ui.input
                                type="password"
                                wire:model="password_confirmation"
                                name="password_confirmation"
                                :label="__('users.password_confirmation')"
                                autocomplete="new-password"
                            ></x-ui.input>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70"
                        >{{ __('users.edit.status') }}</x-ui.title>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input
                                    type="checkbox"
                                    wire:model="is_active"
                                    class="toggle toggle-success"
                                >
                                <span class="label-text">{{ __('users.edit.is_active') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Preferences --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70"
                        >{{ __('users.edit.preferences') }}</x-ui.title>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text">{{ __('users.timezone') }}</span>
                                </label>
                                <select
                                    wire:model="timezone"
                                    class="select select-bordered w-full"
                                >
                                    @foreach ($this->timezones as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text">{{ __('users.locale') }}</span>
                                </label>
                                <select
                                    wire:model="locale"
                                    class="select select-bordered w-full"
                                >
                                    @foreach ($this->locales as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- Roles & Teams --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70"
                        >{{ __('users.edit.roles_teams') }}</x-ui.title>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Roles --}}
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text font-medium">{{ __('users.roles') }}</span>
                                </label>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-2 border border-base-300 rounded-lg">
                                    @foreach ($this->roles as $role)
                                        <label
                                            class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded"
                                        >
                                            <input
                                                type="checkbox"
                                                wire:model="selectedRoles"
                                                value="{{ $role->id }}"
                                                class="checkbox checkbox-sm checkbox-primary"
                                            >
                                            <span class="label-text">{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Teams --}}
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text font-medium">{{ __('users.teams') }}</span>
                                </label>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-2 border border-base-300 rounded-lg">
                                    @foreach ($this->teams as $team)
                                        <label
                                            class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded"
                                        >
                                            <input
                                                type="checkbox"
                                                wire:model="selectedTeams"
                                                value="{{ $team->id }}"
                                                class="checkbox checkbox-sm checkbox-secondary"
                                            >
                                            <span class="label-text">{{ $team->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="divider"></div>
                    <div class="flex justify-end gap-4">
                        <x-ui.button
                            href="{{ route('users.show', $editUser->uuid) }}"
                            style="ghost"
                            wire:navigate
                        >{{ __('actions.cancel') }}</x-ui.button>
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            <x-ui.loading
                                wire:loading
                                wire:target="updateUser"
                                size="sm"
                            ></x-ui.loading>
                            {{ __('pages.common.edit.submit') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            </div>
        </div>
    @else
        <div class="alert alert-error">
            <x-ui.icon
                name="exclamation-triangle"
                size="sm"
            ></x-ui.icon>
            <span>{{ __('users.user_not_found') }}</span>
        </div>
    @endif
</section>
