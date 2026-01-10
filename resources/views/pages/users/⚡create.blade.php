<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\ActivationService;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Database\Eloquent\Collection;
use DateTimeZone;
use Exception;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 4;

    // Form fields
    public string $name = '';
    public ?string $username = null;
    public ?string $email = null;
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $timezone = null;
    public ?string $locale = null;
    public bool $sendActivation = false;

    /** @var array<int> */
    public array $selectedRoles = [];

    /** @var array<int> */
    public array $selectedTeams = [];

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::CREATE_USERS);
        $this->timezone = config('app.timezone');
        $this->locale = config('app.locale');
        $this->pageSubtitle = __('pages.common.create.description', ['type' => __('types.user')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.create.title', ['type' => __('types.user')]);
    }

    /**
     * Get available roles for selection.
     *
     * @return Collection<int, Role>
     */
    public function getRolesProperty()
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Get available teams for selection.
     *
     * @return Collection<int, Team>
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
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)],
            'timezone' => ['required', 'string', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['exists:roles,id'],
            'selectedTeams' => ['array'],
            'selectedTeams.*' => ['exists:teams,id'],
        ];

        // If not sending activation, password is required
        if (!$this->sendActivation) {
            $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
        }

        // If sending activation, email is required
        if ($this->sendActivation) {
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique(User::class)];
        }

        return $rules;
    }

    /**
     * Create the user.
     */
    public function createUser(): void
    {
        $validated = $this->validate();

        try {
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
                roleIds: $this->selectedRoles,
                teamIds: $this->selectedTeams,
            );

            NotificationBuilder::make()
                ->title('pages.common.create.success', ['name' => $user->name])
                ->success()
                ->persist()
                ->send();

            $this->redirect(route('users.index'), navigate: true);
        } catch (Exception $e) {
            NotificationBuilder::make()
                ->title('pages.common.create.error', ['type' => __('types.user')])
                ->content($e->getMessage())
                ->error()
                ->send();
        }
    }

    /**
     * Handle activation toggle change.
     */
    public function updatedSendActivation(): void
    {
        // If activation is enabled, clear password fields
        if ($this->sendActivation) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }
}; ?>

<section class="mx-auto w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title level="2"
                        class="mb-6">{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form wire:submit="createUser"
                       class="space-y-6">
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.create.basic_info') }}</x-ui.title>

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
                                :required="$sendActivation"></x-ui.input>
                </div>

                {{-- Activation Options --}}
                <div class="divider"></div>
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.create.activation') }}</x-ui.title>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model.live="sendActivation"
                                   class="toggle toggle-primary">
                            <span class="label-text">{{ __('users.create.send_activation_email') }}</span>
                        </label>
                        <span class="text-base-content/60 ml-14 text-sm">
                            {{ __('users.create.activation_hint') }}
                        </span>
                    </div>

                    @if (!$sendActivation)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.input type="password"
                                        wire:model="password"
                                        name="password"
                                        :label="__('users.password')"
                                        required
                                        autocomplete="new-password"></x-ui.input>

                            <x-ui.input type="password"
                                        wire:model="password_confirmation"
                                        name="password_confirmation"
                                        :label="__('users.password_confirmation')"
                                        required
                                        autocomplete="new-password"></x-ui.input>
                        </div>
                    @endif
                </div>

                {{-- Preferences --}}
                <div class="divider"></div>
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.create.preferences') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">{{ __('users.timezone') }}</span>
                            </label>
                            <select wire:model="timezone"
                                    class="select select-bordered w-full">
                                @foreach ($this->timezones as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">{{ __('users.locale') }}</span>
                            </label>
                            <select wire:model="locale"
                                    class="select select-bordered w-full">
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
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('users.create.roles_teams') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {{-- Roles --}}
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text font-medium">{{ __('users.roles') }}</span>
                            </label>
                            <div class="border-base-300 max-h-48 space-y-2 overflow-y-auto rounded-lg border p-2">
                                @foreach ($this->roles as $role)
                                    <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2">
                                        <input type="checkbox"
                                               wire:model="selectedRoles"
                                               value="{{ $role->id }}"
                                               class="checkbox checkbox-sm checkbox-primary">
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
                            <div class="border-base-300 max-h-48 space-y-2 overflow-y-auto rounded-lg border p-2">
                                @foreach ($this->teams as $team)
                                    <label class="hover:bg-base-200 flex cursor-pointer items-center gap-3 rounded p-2">
                                        <input type="checkbox"
                                               wire:model="selectedTeams"
                                               value="{{ $team->id }}"
                                               class="checkbox checkbox-sm checkbox-secondary">
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
                    <x-ui.button href="{{ route('users.index') }}"
                                 style="ghost"
                                 wire:navigate>{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button type="submit"
                                 variant="primary">
                        <x-ui.loading wire:loading
                                      wire:target="createUser"
                                      size="sm"></x-ui.loading>
                        {{ __('pages.common.create.submit', ['type' => __('types.user')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
