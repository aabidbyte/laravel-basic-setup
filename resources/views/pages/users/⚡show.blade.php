<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;

new class extends BasePageComponent {
    public ?string $pageTitle = 'ui.pages.users.show';

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
        $this->pageTitle = $this->user->name;
        $this->pageSubtitle = __('ui.users.show.subtitle');
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

            NotificationBuilder::make()->title(__('ui.users.show.activation_link_generated'))->success()->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title(__('ui.users.show.activation_link_error'))->content($e->getMessage())->error()->send();
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
                ->title(__('ui.users.show.user_activated', ['name' => $this->user->name]))
                ->success()
                ->persist()
                ->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title(__('ui.users.show.activation_error'))->content($e->getMessage())->error()->send();
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
                ->title(__('ui.users.show.user_deactivated', ['name' => $this->user->name]))
                ->warning()
                ->persist()
                ->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title(__('ui.users.show.deactivation_error'))->content($e->getMessage())->error()->send();
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
                            <p class="text-base-content/60">{{ $user->email ?? __('ui.users.no_email') }}</p>
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
                                {{ __('ui.actions.edit') }}
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
                                    {{ __('ui.users.show.generate_link') }}
                                </x-ui.button>
                            @endcan

                            @can(Permissions::EDIT_USERS)
                                <x-ui.button
                                    wire:click="activateUser"
                                    wire:confirm="{{ __('ui.users.show.confirm_activate') }}"
                                    color="success"
                                    size="sm"
                                >
                                    <x-ui.icon
                                        name="check"
                                        size="sm"
                                    ></x-ui.icon>
                                    {{ __('ui.actions.activate') }}
                                </x-ui.button>
                            @endcan
                        @else
                            @can(Permissions::EDIT_USERS)
                                <x-ui.button
                                    wire:click="deactivateUser"
                                    wire:confirm="{{ __('ui.users.show.confirm_deactivate') }}"
                                    color="warning"
                                    size="sm"
                                >
                                    <x-ui.icon
                                        name="x-mark"
                                        size="sm"
                                    ></x-ui.icon>
                                    {{ __('ui.actions.deactivate') }}
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
                        {{ $user->is_active ? __('ui.users.active') : __('ui.users.inactive') }}
                    </x-ui.badge>
                </div>

                {{-- User details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Personal Information --}}
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('ui.users.personal_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-base-content/60">{{ __('ui.users.name') }}</span>
                                <p class="font-medium">{{ $user->name }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('ui.users.email') }}</span>
                                <p class="font-medium">{{ $user->email ?? '—' }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('ui.users.username') }}</span>
                                <p class="font-medium">{{ $user->username ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Account Information --}}
                    <div class="space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('ui.users.account_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-base-content/60">{{ __('ui.users.uuid') }}</span>
                                <p class="font-mono text-sm">{{ $user->uuid }}</p>
                            </div>

                            <div>
                                <span class="text-sm text-base-content/60">{{ __('ui.users.created_at') }}</span>
                                <p class="font-medium">
                                    {{ $user->created_at->diffForHumans() }}
                                    <span
                                        class="text-base-content/60 text-sm">({{ $user->created_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            @if ($user->last_login_at)
                                <div>
                                    <span
                                        class="text-sm text-base-content/60">{{ __('ui.users.last_login_at') }}</span>
                                    <p class="font-medium">
                                        {{ $user->last_login_at->diffForHumans() }}
                                        <span
                                            class="text-base-content/60 text-sm">({{ $user->last_login_at->format('Y-m-d H:i') }})</span>
                                    </p>
                                </div>
                            @endif

                            @if ($user->createdBy)
                                <div>
                                    <span class="text-sm text-base-content/60">{{ __('ui.users.created_by') }}</span>
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
                    >{{ __('ui.users.preferences') }}</x-ui.title>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-base-content/60">{{ __('ui.users.timezone') }}</span>
                            <p class="font-medium">{{ $user->timezone ?? __('ui.users.not_set') }}</p>
                        </div>

                        <div>
                            <span class="text-sm text-base-content/60">{{ __('ui.users.locale') }}</span>
                            <p class="font-medium">{{ $user->locale ?? __('ui.users.not_set') }}</p>
                        </div>

                        <div>
                            <span class="text-sm text-base-content/60">{{ __('ui.users.primary_team') }}</span>
                            <p class="font-medium">{{ $user->team?->name ?? __('ui.users.no_primary_team') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Roles --}}
                @if ($user->roles->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title
                            level="3"
                            class="text-base-content/70 border-b pb-2"
                        >{{ __('ui.users.roles') }}</x-ui.title>
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
                        >{{ __('ui.users.teams') }}</x-ui.title>
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
                        {{ __('ui.actions.back_to_list') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Activation Link Modal --}}
        @if ($showActivationModal && $activationLink)
            <x-ui.base-modal
                title="{{ __('ui.users.show.activation_link_title') }}"
                open
                @close="$wire.closeActivationModal()"
            >
                <div class="space-y-4">
                    <p class="text-base-content/70">{{ __('ui.users.show.activation_link_description') }}</p>

                    <div
                        x-data="{ copied: false }"
                        class="flex items-center gap-2"
                    >
                        <x-ui.input
                            type="text"
                            x-bind:value="'{{ $activationLink }}'"
                            readonly
                            class="font-mono text-sm"
                        ></x-ui.input>
                        <x-ui.button
                            @click="navigator.clipboard.writeText('{{ $activationLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            color="primary"
                            size="sm"
                        >
                            <x-ui.icon
                                x-show="!copied"
                                name="clipboard"
                                size="sm"
                            ></x-ui.icon>
                            <x-ui.icon
                                x-show="copied"
                                x-cloak
                                name="check"
                                size="sm"
                            ></x-ui.icon>
                            <span
                                x-text="copied ? '{{ __('ui.actions.copied') }}' : '{{ __('ui.actions.copy') }}'"></span>
                        </x-ui.button>
                    </div>

                    <div class="alert alert-warning">
                        <x-ui.icon
                            name="exclamation-triangle"
                            size="sm"
                        ></x-ui.icon>
                        <span>{{ __('ui.users.show.activation_link_warning', ['days' => 7]) }}</span>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button
                        wire:click="closeActivationModal"
                        style="ghost"
                    >{{ __('ui.actions.close') }}</x-ui.button>
                </x-slot:actions>
            </x-ui.base-modal>
        @endif
    @else
        <div class="alert alert-error">
            <x-ui.icon
                name="exclamation-triangle"
                size="sm"
            ></x-ui.icon>
            <span>{{ __('ui.users.user_not_found') }}</span>
        </div>
    @endif
</section>
