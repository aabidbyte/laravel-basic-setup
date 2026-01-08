<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;

new class extends BasePageComponent
{
    public ?string $pageTitle = null;

    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'card';

    protected int $placeholderRows = 4;

    public ?User $user = null;

    public ?string $activationLink = null;

    public bool $showActivationModal = false;

    /**
     * Mount the component and authorize access.
     */
    public function mount(User $user): void
    {
        $this->authorize(Permissions::VIEW_USERS);

        $this->user = $user;
        $this->pageSubtitle = __('pages.common.show.subtitle', ['type' => __('types.user')]);
    }

    public function getPageTitle(): string
    {
        return __('pages.common.show.title', ['name' => $this->user->name, 'type' => __('types.user')]);
    }

    /**
     * Generate activation link for the user.
     */
    public function generateActivationLink(): void
    {
        $this->authorize(Permissions::GENERATE_ACTIVATION_LINKS);

        try {
            $userService = app(UserService::class);
            $this->activationLink = $userService->generateActivationLink($this->user);
            $this->showActivationModal = true;

            NotificationBuilder::make()->title('users.show.activation_link_generated')->success()->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title('users.show.activation_link_error')->content($e->getMessage())->error()->send();
        }
    }

    /**
     * Activate the user.
     */
    public function activateUser(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        try {
            $this->user->activate();
            $this->user = $this->user->fresh();

            NotificationBuilder::make()
                ->title('pages.common.messages.activated', ['name' => $this->user->name])
                ->success()
                ->persist()
                ->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title('users.show.activation_error')->content($e->getMessage())->error()->send();
        }
    }

    /**
     * Deactivate the user.
     */
    public function deactivateUser(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        try {
            $this->user->deactivate();
            $this->user = $this->user->fresh();

            NotificationBuilder::make()
                ->title('pages.common.messages.deactivated', ['name' => $this->user->name])
                ->warning()
                ->persist()
                ->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title('users.show.deactivation_error')->content($e->getMessage())->error()->send();
        }
    }

    /**
     * Copy activation link to clipboard (dispatches event to Alpine).
     */
    public function closeActivationModal(): void
    {
        $this->showActivationModal = false;
        $this->activationLink = null;
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    @if ($user)
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                {{-- Header with actions --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <x-ui.avatar
                            :user="$user"
                            size="lg"
                        ></x-ui.avatar>
                        <div>
                            <x-ui.title level="2">{{ $user->name }}</x-ui.title>
                            <p class="text-base-content/60">{{ $user->email ?? __('users.no_email') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @can(Permissions::EDIT_USERS)
                            <x-ui.button
                                href="{{ route('users.edit', $user->uuid) }}"
                                color="primary"
                                size="sm"
                                wire:navigate
                            >
                                <x-ui.icon
                                    name="pencil"
                                    size="sm"
                                ></x-ui.icon>
                                {{ __('actions.edit') }}
                            </x-ui.button>
                        @endcan

                        @if (!$user->is_active)
                            @can(Permissions::GENERATE_ACTIVATION_LINKS)
                                <x-ui.button
                                    wire:click="generateActivationLink"
                                    color="secondary"
                                    size="sm"
                                >
                                    <x-ui.icon
                                        name="link"
                                        size="sm"
                                    ></x-ui.icon>
                                    {{ __('users.show.generate_link') }}
                                </x-ui.button>
                            @endcan

                            @can(Permissions::EDIT_USERS)
                                <x-ui.button
                                    wire:click="activateUser"
                                    wire:confirm="{{ __('users.show.confirm_activate') }}"
                                    color="success"
                                    size="sm"
                                >
                                    <x-ui.icon
                                        name="check"
                                        size="sm"
                                    ></x-ui.icon>
                                    {{ __('actions.activate') }}
                                </x-ui.button>
                            @endcan
                        @else
                            @can(Permissions::EDIT_USERS)
                                <x-ui.button
                                    wire:click="deactivateUser"
                                    wire:confirm="{{ __('users.show.confirm_deactivate') }}"
                                    color="warning"
                                    size="sm"
                                >
                                    <x-ui.icon
                                        name="x-mark"
                                        size="sm"
                                    ></x-ui.icon>
                                    {{ __('actions.deactivate') }}
                                </x-ui.button>
                            @endcan
                        @endif
                    </div>
                </div>

                {{-- Status badge --}}
                <div class="mb-6">
                    <x-ui.badge
                        :variant="$user->is_active ? 'success' : 'error'"
                        size="lg"
                    >
                        {{ $user->is_active ? __('users.active') : __('users.inactive') }}
                    </x-ui.badge>
                </div>

                {{-- User details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Personal Information --}}
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('users.personal_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-base-content/60">{{ __('users.name') }}</span>
                                <p class="font-medium">{{ $user->name }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('users.email') }}</span>
                                <p class="font-medium">{{ $user->email ?? '—' }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('users.username') }}</span>
                                <p class="font-medium">{{ $user->username ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Account Information --}}
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('users.account_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-base-content/60">{{ __('users.uuid') }}</span>
                                <p class="font-mono text-sm">{{ $user->uuid }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('users.created_at') }}</span>
                                <p class="font-medium">
                                    {{ $user->created_at->diffForHumans() }}
                                    <span
                                        class="text-base-content/60 text-sm">({{ $user->created_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            @if ($user->last_login_at)
                                <div>
                                    <span class="text-sm text-base-content/60">{{ __('users.last_login_at') }}</span>
                                    <p class="font-medium">
                                        {{ $user->last_login_at->diffForHumans() }}
                                        <span
                                            class="text-base-content/60 text-sm">({{ $user->last_login_at->format('Y-m-d H:i') }})</span>
                                    </p>
                                </div>
                            @endif

                            @if ($user->createdBy)
                                <div>
                                    <span class="text-sm text-base-content/60">{{ __('users.created_by') }}</span>
                                    <p class="font-medium">{{ $user->createdBy->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Preferences --}}
                <div class="mt-8 space-y-4">
                    <x-ui.title
                        level="3"
                        class="text-base-content/70 border-b pb-2"
                    >{{ __('users.preferences') }}</x-ui.title>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-base-content/60">{{ __('users.timezone') }}</span>
                            <p class="font-medium">{{ $user->timezone ?? __('users.not_set') }}</p>
                        </div>

                        <div>
                            <span class="text-sm text-base-content/60">{{ __('users.locale') }}</span>
                            <p class="font-medium">{{ $user->locale ?? __('users.not_set') }}</p>
                        </div>


                    </div>
                </div>

                {{-- Roles --}}
                @if ($user->roles->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('users.roles') }}</x-ui.title>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <x-ui.badge
                                    variant="primary"
                                    size="md"
                                >{{ $role->name }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Teams --}}
                @if ($user->teams->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('users.teams') }}</x-ui.title>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->teams as $team)
                                <x-ui.badge
                                    variant="secondary"
                                    size="md"
                                >{{ $team->name }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Back button --}}
                <div class="mt-8 pt-4 border-t">
                    <x-ui.button
                        href="{{ route('users.index') }}"
                        style="ghost"
                        wire:navigate
                    >
                        <x-ui.icon
                            name="arrow-left"
                            size="sm"
                        ></x-ui.icon>
                        {{ __('actions.back_to_list') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Activation Link Modal --}}
        @if ($showActivationModal && $activationLink)
            <x-ui.base-modal
                title="{{ __('users.show.activation_link_title') }}"
                open
                @close="$wire.closeActivationModal()"
            >
                <div class="space-y-4">
                    <p class="text-base-content/70">{{ __('users.show.activation_link_description') }}</p>

                    <div class="flex items-center gap-2">
                        <x-ui.input
                            type="text"
                            value="{{ $activationLink }}"
                            readonly
                            class="font-mono text-sm"
                        ></x-ui.input>
                        <x-ui.copy-button
                            :text="$activationLink"
                            size="sm"
                            variant="primary"
                            showText
                        ></x-ui.copy-button>
                    </div>

                    <div class="alert alert-warning">
                        <x-ui.icon
                            name="exclamation-triangle"
                            size="sm"
                        ></x-ui.icon>
                        <span>{{ __('users.show.activation_link_warning', ['days' => 7]) }}</span>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button
                        wire:click="closeActivationModal"
                        style="ghost"
                    >{{ __('actions.close') }}</x-ui.button>
                </x-slot:actions>
            </x-ui.base-modal>
        @endif
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
