<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Users\UserService;
use App\Support\Users\UserData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.user';

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 4;

    /**
     * Form fields
     */
    public string $name = '';
    public ?string $username = null;
    public ?string $email = null;
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $timezone = null;
    public ?string $locale = null;

    /**
     * Create mode specific
     */
    public bool $sendActivation = false;

    /**
     * Edit mode specific
     */
    public bool $is_active = false;

    /**
     * Common (both modes)
     */
    public array $selectedRoles = [];
    public array $selectedTeams = [];
    public array $selectedDirectPermissions = [];

    /**
     * Initialize the component.
     */
    public function mount(?User $user = null): void
    {
        $this->authorizeAccess($user);
        $this->initializeUnifiedModel($user, fn ($u) => $this->loadExistingUser($u), fn () => $this->prepareNewUser());
        $this->updatePageHeader();
    }

    /**
     * Authorize access to the component.
     */
    protected function authorizeAccess(?User $user): void
    {
        $permission = $user ? Permissions::EDIT_USERS() : Permissions::CREATE_USERS();
        $this->authorize($permission);
    }

    /**
     * Load existing user data into form fields.
     */
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

    /**
     * Prepare a new user model with defaults.
     */
    protected function prepareNewUser(): void
    {
        $this->model = new User();
        $this->timezone = config('app.timezone');
        $this->locale = config('app.locale');
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
        return __($title, ['type' => __($this->modelTypeLabel)]);
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
     * Get available roles for selection.
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::orderBy('name', 'asc')->get();
    }

    /**
     * Get available teams for selection.
     */
    #[Computed]
    public function teams(): Collection
    {
        return Team::orderBy('name', 'asc')->get();
    }

    /**
     * Get available timezones.
     */
    #[Computed]
    public function timezones(): array
    {
        return collect(DateTimeZone::listIdentifiers())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray();
    }

    /**
     * Get available locales.
     */
    #[Computed]
    public function locales(): array
    {
        return collect(config('i18n.supported_locales'))->mapWithKeys(fn ($data, $locale) => [$locale => $data['native_name']])->toArray();
    }

    /**
     * Get available permissions for selection.
     */
    #[Computed]
    public function permissions(): Collection
    {
        return Permission::orderBy('name', 'asc')->get();
    }

    /**
     * Define validation rules.
     */
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
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique(User::class)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique(User::class)];

            if (!$this->sendActivation) {
                $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
            }

            if ($this->sendActivation) {
                $rules['email'] = ['required', 'email', 'max:255', Rule::unique(User::class)];
            }
        } else {
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->model->id)];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->model->id)];
            $rules['password'] = ['nullable', 'string', Password::defaults(), 'confirmed'];
            $rules['is_active'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Handle model creation.
     */
    public function create(UserService $userService): void
    {
        $this->validate();

        $user = $userService->createUser(
            UserData::forCreation(
                attributes: [
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
            ),
        );

        $this->sendSuccessNotification($user, 'pages.common.create.success');
        $this->redirect(route('users.index'), navigate: true);
    }

    /**
     * Handle model update.
     */
    public function save(UserService $userService): void
    {
        $this->validate();

        $user = $userService->updateUser(
            $this->model,
            UserData::forUpdate(
                attributes: [
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
            ),
        );

        $this->sendSuccessNotification($user, 'pages.common.edit.success');
        $this->redirect(route('users.show', $user->uuid), navigate: true);
    }

    /**
     * Handle changes to activation toggle.
     */
    public function updatedSendActivation(): void
    {
        if ($this->sendActivation) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    /**
     * Get the URL to redirect back to.
     */
    #[Computed]
    public function cancelUrl(): string
    {
        return $this->getCancelUrl('users.index', 'users.show', $this->model);
    }

    /**
     * Determine if password section should be visible.
     */
    #[Computed]
    public function showPasswordSection(): bool
    {
        return $this->isCreateMode ? !$this->sendActivation : Auth::user()?->hasRole(Roles::SUPER_ADMIN);
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="user-form"
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
                       id="user-form">
                {{-- Basic Information --}}
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.edit.basic_info') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <x-ui.input type="text"
                                    wire:model="name"
                                    name="name"
                                    :label="__('users.name')"
                                    required
                                    autofocus />

                        <x-ui.input type="text"
                                    wire:model="username"
                                    name="username"
                                    :label="__('users.username')"
                                    required />
                    </div>

                    <x-ui.input type="email"
                                wire:model="email"
                                name="email"
                                :label="__('users.email')"
                                :required="$isCreateMode && $sendActivation" />

                    @if (!$isCreateMode)
                        @if ($model->hasVerifiedEmail())
                            <p class="text-info flex items-center gap-2 text-sm">
                                <x-ui.icon name="information-circle"
                                           size="sm" />
                                {{ __('users.edit.email_change_note') }}
                            </p>
                        @endif

                        @if ($model->hasPendingEmailChange())
                            <div class="alert alert-warning">
                                <x-ui.icon name="clock"
                                           size="sm" />
                                <span>{{ __('users.edit.pending_email_note', ['email' => $model->pending_email]) }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Activation Options (Create Only) --}}
                @if ($isCreateMode)
                    <div class="divider"></div>
                    <div class="space-y-6">
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
                    <div class="space-y-6">
                        <x-ui.title level="3"
                                    class="text-base-content/70">
                            {{ $isCreateMode ? __('users.create.password') : __('users.edit.password') }}
                        </x-ui.title>

                        @if (!$isCreateMode)
                            <p class="text-base-content/60 text-sm">{{ __('users.edit.password_hint') }}</p>
                        @endif

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <x-ui.password wire:model="password"
                                           name="password"
                                           :label="__('users.password')"
                                           :required="$isCreateMode && !$sendActivation"
                                           with-strength-meter
                                           with-generation
                                           autocomplete="new-password" />

                            <x-ui.password wire:model="password_confirmation"
                                           name="password_confirmation"
                                           :label="__('users.password_confirmation')"
                                           :required="$isCreateMode && !$sendActivation"
                                           autocomplete="new-password" />
                        </div>
                    </div>
                @endif

                {{-- Status (Edit Only) --}}
                @if (!$isCreateMode)
                    <div class="divider"></div>
                    <div class="space-y-6">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.edit.status') }}</x-ui.title>

                        <x-ui.toggle wire:model="is_active"
                                     :label="__('users.edit.is_active')"
                                     color="success" />
                    </div>
                @endif

                {{-- Preferences --}}
                <div class="divider"></div>
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.edit.preferences') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                <div class="space-y-6">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.edit.roles_teams') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- Roles --}}
                        <div class="space-y-3">
                            <x-ui.label :text="__('users.roles')" />
                            <div class="border-base-300 max-h-60 space-y-1 overflow-y-auto rounded-lg border p-2">
                                @foreach ($this->roles as $role)
                                    <label wire:key="role-{{ $role->uuid }}"
                                           class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2 transition-colors">
                                        <x-ui.checkbox wire:model="selectedRoles"
                                                       value="{{ $role->uuid }}"
                                                       color="primary"
                                                       size="sm" />
                                        <span class="text-sm">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Teams --}}
                        <div class="space-y-3">
                            <x-ui.label :text="__('users.teams')" />
                            <div class="border-base-300 max-h-60 space-y-1 overflow-y-auto rounded-lg border p-2">
                                @foreach ($this->teams as $team)
                                    <label wire:key="team-{{ $team->uuid }}"
                                           class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2 transition-colors">
                                        <x-ui.checkbox wire:model="selectedTeams"
                                                       value="{{ $team->uuid }}"
                                                       color="secondary"
                                                       size="sm" />
                                        <span class="text-sm">{{ $team->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Direct Permissions --}}
                <div class="divider"></div>
                <div class="space-y-6">
                    <div class="flex flex-col gap-1">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('users.direct_permissions') }}</x-ui.title>
                        <p class="text-base-content/60 text-sm">{{ __('users.direct_permissions_help') }}</p>
                    </div>

                    <x-ui.permission-matrix :permissions="$this->permissions"
                                            :selectedPermissions="$selectedDirectPermissions"
                                            wireModel="selectedDirectPermissions" />
                </div>
            </x-ui.form>
        </x-ui.card>
    </div>
</x-layouts.page>
