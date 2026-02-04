<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Database\Eloquent\Collection;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 4;

    public ?User $model = null;

    // Form fields
    public string $name = '';
    public ?string $username = null;
    public ?string $email = null;
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $timezone = null;
    public ?string $locale = null;

    // Create mode specific
    public bool $sendActivation = false;

    // Edit mode specific
    public bool $is_active = false;

    // Common (both modes)
    public array $selectedRoles = [];
    public array $selectedTeams = [];
    public array $selectedDirectPermissions = [];

    public function mount(?User $user = null): void
    {
        $this->authorizeAccess($user);
        $this->initializeUnifiedModel($user, fn($u) => $this->loadExistingUser($u), fn() => $this->prepareNewUser());

        $this->modelTypeLabel = __('types.user');

        $this->updatePageHeader();
    }

    protected function authorizeAccess(?User $user): void
    {
        $permission = $user ? Permissions::EDIT_USERS() : Permissions::CREATE_USERS();

        $this->authorize($permission);
    }

    protected function loadExistingUser(User $user): void
    {
        $this->model = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->timezone = $user->timezone ?? config('app.timezone');
        $this->locale = $user->locale ?? config('app.locale');
        $this->is_active = $user->is_active;
        $this->selectedRoles = $user->roles->pluck('uuid')->toArray();
        $this->selectedTeams = $user->teams->pluck('uuid')->toArray();
        $this->selectedDirectPermissions = $user->permissions->pluck('uuid')->toArray();
    }

    protected function prepareNewUser(): void
    {
        $this->model = new User();
        $this->timezone = config('app.timezone');
        $this->locale = config('app.locale');
    }

    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => __('types.user')]);
            $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.user')]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => __('types.user')]);
            $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('types.user')]);
        }
    }

    /**
     * Get available roles for selection.
     *
     * @return Collection<int, Role>
     */
    public function getRolesProperty()
    {
        return Role::orderBy('name', 'asc')->get();
    }

    /**
     * Get available teams for selection.
     *
     * @return Collection<int, Team>
     */
    public function getTeamsProperty()
    {
        return Team::orderBy('name', 'asc')->get();
    }

    /**
     * Get available timezones.
     *
     * @return array<string, string>
     */
    public function getTimezonesProperty(): array
    {
        return collect(DateTimeZone::listIdentifiers())->mapWithKeys(fn($tz) => [$tz => $tz])->toArray();
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
     * Get available permissions for selection.
     *
     * @return Collection<int, \App\Models\Permission>
     */
    public function getPermissionsProperty()
    {
        return \App\Models\Permission::orderBy('name', 'asc')->get();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['exists:roles,uuid'],
            'selectedTeams' => ['array'],
            'selectedTeams.*' => ['exists:teams,uuid'],
            'selectedDirectPermissions' => ['array'],
            'selectedDirectPermissions.*' => ['exists:permissions,uuid'],
        ];

        if ($this->isCreateMode) {
            // Create mode validation
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique(User::class)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique(User::class)];

            // If not sending activation, password is required
            if (!$this->sendActivation) {
                $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
            }

            // If sending activation, email is required
            if ($this->sendActivation) {
                $rules['email'] = ['required', 'email', 'max:255', Rule::unique(User::class)];
            }
        } else {
            // Edit mode validation
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->model->id)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->model->id)];
            $rules['password'] = ['nullable', 'string', Password::defaults(), 'confirmed'];
            $rules['is_active'] = ['boolean'];
        }

        return $rules;
    }

    public function create(): void
    {
        $validated = $this->validate();

        $userService = app(UserService::class);
        $user = $userService->createUser(
            data: [
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password ?: null,
                'timezone' => $this->timezone,
                'locale' => $this->locale,
            ],
            sendActivation: $this->sendActivation,
            roleUuids: $this->selectedRoles,
            teamUuids: $this->selectedTeams,
            permissionUuids: $this->selectedDirectPermissions,
        );

        $this->sendSuccessNotification($user, 'pages.common.create.success');
        $this->redirect(route('users.index'), navigate: true);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $userService = app(UserService::class);
        $user = $userService->updateUser(
            user: $this->model,
            data: [
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password ?: null,
                'timezone' => $this->timezone,
                'locale' => $this->locale,
                'is_active' => $this->is_active,
            ],
            roleUuids: $this->selectedRoles,
            teamUuids: $this->selectedTeams,
            permissionUuids: $this->selectedDirectPermissions,
        );

        $this->sendSuccessNotification($user, 'pages.common.edit.success');
        $this->redirect(route('users.show', $user->uuid), navigate: true);
    }

    // Lifecycle hook for create mode
    public function updatedSendActivation(): void
    {
        if ($this->sendActivation) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    // Computed properties
    public function getCancelUrlProperty(): string
    {
        return $this->isCreateMode ? route('users.index') : route('users.show', $this->model->uuid);
    }

    public function getShowPasswordSectionProperty(): bool
    {
        // Create: show if not sending activation
        // Edit: show only for Super Admin
        return $this->isCreateMode ? !$this->sendActivation : Auth::user()?->hasRole(Roles::SUPER_ADMIN);
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="user-form"
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
                           id="user-form"
                           class="space-y-6">
                    {{-- Basic Information --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.edit.basic_info') }}</x-ui.title>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.input type="text"
                                        wire:model="name"
                                        name="name"
                                        :label="__('users.name')"
                                        required
                                        autofocus></x-ui.input>

                            <x-ui.input type="text"
                                        wire:model="username"
                                        name="username"
                                        :label="__('users.username')"
                                        required></x-ui.input>
                        </div>

                        <x-ui.input type="email"
                                    wire:model="email"
                                    name="email"
                                    :label="__('users.email')"
                                    :required="$isCreateMode && $sendActivation"></x-ui.input>

                        @if (!$isCreateMode)
                            @if ($model->hasVerifiedEmail())
                                <p class="text-info mt-1 text-sm">
                                    <x-ui.icon name="information-circle"
                                               size="sm"
                                               class="inline"></x-ui.icon>
                                    {{ __('users.edit.email_change_note') }}
                                </p>
                            @endif

                            @if ($model->hasPendingEmailChange())
                                <div class="alert alert-warning mt-2">
                                    <x-ui.icon name="clock"
                                               size="sm"></x-ui.icon>
                                    <span>{{ __('users.edit.pending_email_note', ['email' => $model->pending_email]) }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Activation Options (Create Only) --}}
                    @if ($isCreateMode)
                        <div class="divider"></div>
                        <div class="space-y-4">
                            <x-ui.title level="3"
                                        class="text-base-content/70">{{ __('users.create.activation') }}</x-ui.title>

                            <x-ui.toggle wire:model.live="sendActivation"
                                         :label="__('users.create.send_activation_email')"
                                         :description="__('users.create.activation_hint')"
                                         color="primary" />
                        </div>
                    @endif

                    {{-- Password --}}
                    @if ($this->showPasswordSection)
                        <div class="divider"></div>
                        <div class="space-y-4">
                            <x-ui.title level="3"
                                        class="text-base-content/70">
                                {{ $isCreateMode ? __('users.create.password') : __('users.edit.password') }}
                            </x-ui.title>

                            @if (!$isCreateMode)
                                <p class="text-base-content/60 text-sm">{{ __('users.edit.password_hint') }}</p>
                            @endif

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <x-ui.password wire:model="password"
                                               name="password"
                                               :label="__('users.password')"
                                               :required="$isCreateMode && !$sendActivation"
                                               with-strength-meter
                                               with-generation
                                               autocomplete="new-password"></x-ui.password>

                                <x-ui.password wire:model="password_confirmation"
                                               name="password_confirmation"
                                               :label="__('users.password_confirmation')"
                                               :required="$isCreateMode && !$sendActivation"
                                               autocomplete="new-password"></x-ui.password>
                            </div>
                        </div>
                    @endif

                    {{-- Status (Edit Only) --}}
                    @if (!$isCreateMode)
                        <div class="divider"></div>
                        <div class="space-y-4">
                            <x-ui.title level="3"
                                        class="text-base-content/70">{{ __('users.edit.status') }}</x-ui.title>

                            <x-ui.toggle wire:model="is_active"
                                         :label="__('users.edit.is_active')"
                                         color="success" />
                        </div>
                    @endif

                    {{-- Preferences --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.edit.preferences') }}</x-ui.title>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.select label="{{ __('users.timezone') }}"
                                         name="timezone"
                                         wire:model="timezone"
                                         :options="$this->timezones"
                                         :prepend-empty="false" />

                            <x-ui.select label="{{ __('users.locale') }}"
                                         name="locale"
                                         wire:model="locale"
                                         :options="$this->locales"
                                         :prepend-empty="false" />
                        </div>

                    </div>

                    {{-- Roles & Teams --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.edit.roles_teams') }}</x-ui.title>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Roles --}}
                            <div class="space-y-2">
                                <x-ui.label :text="__('users.roles')"></x-ui.label>
                                <div class="border-base-300 max-h-48 space-y-2 overflow-y-auto rounded-lg border p-2">
                                    @foreach ($this->roles as $role)
                                        <x-ui.label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2"
                                                    variant="plain">
                                            <input type="checkbox"
                                                   wire:model="selectedRoles"
                                                   value="{{ $role->uuid }}"
                                                   class="checkbox checkbox-sm checkbox-primary">
                                            <span class="label-text">{{ $role->name }}</span>
                                        </x-ui.label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Teams --}}
                            <div class="space-y-2">
                                <x-ui.label :text="__('users.teams')"></x-ui.label>
                                <div class="border-base-300 max-h-48 space-y-2 overflow-y-auto rounded-lg border p-2">
                                    @foreach ($this->teams as $team)
                                        <x-ui.label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2"
                                                    variant="plain">
                                            <input type="checkbox"
                                                   wire:model="selectedTeams"
                                                   value="{{ $team->uuid }}"
                                                   class="checkbox checkbox-sm checkbox-secondary">
                                            <span class="label-text">{{ $team->name }}</span>
                                        </x-ui.label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Direct Permissions --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.direct_permissions') }}</x-ui.title>
                        <p class="text-base-content/60 text-sm">{{ __('users.direct_permissions_help') }}</p>

                        <x-ui.permission-matrix :permissions="$this->permissions"
                                                :selectedPermissions="$selectedDirectPermissions"
                                                wireModel="selectedDirectPermissions"></x-ui.permission-matrix>
                    </div>
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>
