<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;

new class extends BasePageComponent {
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
        $this->authorize(Permissions::GENERATE_ACTIVATION_USERS);

        $userService = app(UserService::class);
        $this->activationLink = $userService->generateActivationLink($this->user);
        $this->showActivationModal = true;

        NotificationBuilder::make()->title('users.show.activation_link_generated')->success()->send();
    }

    /**
     * Send password reset email to the user.
     */
    public function sendPasswordResetEmail(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $userService = app(UserService::class);
        $userService->sendPasswordResetEmail($this->user);

        NotificationBuilder::make()->title('users.show.password_reset_sent')->success()->send();
    }

    /**
     * Send activation email to the user.
     */
    public function sendActivationEmail(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $userService = app(UserService::class);
        $userService->sendActivationEmail($this->user);

        NotificationBuilder::make()->title('users.show.activation_email_sent')->success()->send();
    }

    /**
     * Cancel pending email change.
     */
    public function cancelPendingEmailChange(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $userService = app(UserService::class);
        $userService->cancelPendingEmailChange($this->user);
        $this->user = $this->user->fresh();

        NotificationBuilder::make()->title('users.show.pending_email_cancelled')->success()->send();
    }

    /**
     * Activate the user.
     */
    public function activateUser(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $this->user->activate();
        $this->user = $this->user->fresh();

        NotificationBuilder::make()
            ->title('pages.common.messages.activated', ['name' => $this->user->name])
            ->success()
            ->persist()
            ->send();
    }

    /**
     * Deactivate the user.
     */
    public function deactivateUser(): void
    {
        $this->authorize(Permissions::EDIT_USERS);

        $this->user->deactivate();
        $this->user = $this->user->fresh();

        NotificationBuilder::make()
            ->title('pages.common.messages.deactivated', ['name' => $this->user->name])
            ->warning()
            ->persist()
            ->send();
    }

    /**
     * Copy activation link to clipboard (dispatches event to Alpine).
     */
    public function closeActivationModal(): void
    {
        $this->showActivationModal = false;
        $this->activationLink = null;
    }

    /**
     * Delete the user.
     */
    public function deleteUser(): void
    {
        $this->authorize(Permissions::DELETE_USERS);

        $name = $this->user->name;
        $this->user->delete();

        NotificationBuilder::make()
            ->title('pages.common.messages.deleted', ['name' => $name])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('users.index'), navigate: true);
    }
}; ?>

<section class="mx-auto w-full max-w-4xl"
         @confirm-send-activation-email.window="$wire.sendActivationEmail()"
         @confirm-activate-user.window="$wire.activateUser()"
         @confirm-send-password-reset.window="$wire.sendPasswordResetEmail()"
         @confirm-deactivate-user.window="$wire.deactivateUser()"
         @confirm-delete-user.window="$wire.deleteUser()"
         @confirm-cancel-pending-email.window="$wire.cancelPendingEmailChange()">
    @if ($user)
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                {{-- Header with actions --}}
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :user="$user"
                                     size="lg"></x-ui.avatar>
                        <div>
                            <x-ui.title level="2">{{ $user->name }}</x-ui.title>
                            <p class="text-base-content/60">{{ $user->email ?? __('users.no_email') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @can(Permissions::EDIT_USERS)
                            <x-ui.button href="{{ route('users.edit', $user->uuid) }}"
                                         color="primary"
                                         size="sm"
                                         wire:navigate>
                                <x-ui.icon name="pencil"
                                           size="sm"></x-ui.icon>
                                {{ __('actions.edit') }}
                            </x-ui.button>
                        @endcan

                        @if (!$user->is_active)
                            {{-- Inactive user: show activation options --}}
                            @if (!$user->email)
                                {{-- No email: generate activation link --}}
                                @can(Permissions::GENERATE_ACTIVATION_USERS)
                                    <x-ui.button wire:click="generateActivationLink"
                                                 color="secondary"
                                                 size="sm">
                                        <x-ui.icon name="link"
                                                   size="sm"></x-ui.icon>
                                        {{ __('users.show.generate_link') }}
                                    </x-ui.button>
                                @endcan
                            @else
                                {{-- Has email: send activation email --}}
                                @can(Permissions::EDIT_USERS)
                                    <x-ui.button @click="$dispatch('confirm-modal', {
                                                     title: '{{ __('users.show.send_activation_email') }}',
                                                     message: '{{ __('users.show.confirm_send_activation') }}',
                                                     confirmEvent: 'confirm-send-activation-email'
                                                 })"
                                                 color="secondary"
                                                 size="sm">
                                        <x-ui.icon name="envelope"
                                                   size="sm"></x-ui.icon>
                                        {{ __('users.show.send_activation_email') }}
                                    </x-ui.button>
                                @endcan
                            @endif

                            @can(Permissions::EDIT_USERS)
                                <x-ui.button @click="$dispatch('confirm-modal', {
                                                 title: '{{ __('actions.activate') }}',
                                                 message: '{{ __('users.show.confirm_activate') }}',
                                                 confirmEvent: 'confirm-activate-user'
                                             })"
                                             color="success"
                                             size="sm">
                                    <x-ui.icon name="check"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.activate') }}
                                </x-ui.button>
                            @endcan
                        @else
                            {{-- Active user: password reset and deactivate options --}}
                            @if ($user->hasVerifiedEmail())
                                @can(Permissions::EDIT_USERS)
                                    <x-ui.button @click="$dispatch('confirm-modal', {
                                                     title: '{{ __('users.show.send_password_reset') }}',
                                                     message: '{{ __('users.show.confirm_send_reset') }}',
                                                     confirmEvent: 'confirm-send-password-reset'
                                                 })"
                                                 color="info"
                                                 size="sm">
                                        <x-ui.icon name="key"
                                                   size="sm"></x-ui.icon>
                                        {{ __('users.show.send_password_reset') }}
                                    </x-ui.button>
                                @endcan
                            @elseif ($user->email)
                                {{-- Has email but not verified --}}
                                @can(Permissions::EDIT_USERS)
                                    <x-ui.button @click="$dispatch('confirm-modal', {
                                                     title: '{{ __('users.show.send_activation_email') }}',
                                                     message: '{{ __('users.show.confirm_send_activation') }}',
                                                     confirmEvent: 'confirm-send-activation-email'
                                                 })"
                                                 color="secondary"
                                                 size="sm">
                                        <x-ui.icon name="envelope"
                                                   size="sm"></x-ui.icon>
                                        {{ __('users.show.send_activation_email') }}
                                    </x-ui.button>
                                @endcan
                            @else
                                {{-- No email: generate activation link --}}
                                @can(Permissions::GENERATE_ACTIVATION_USERS)
                                    <x-ui.button wire:click="generateActivationLink"
                                                 color="secondary"
                                                 size="sm">
                                        <x-ui.icon name="link"
                                                   size="sm"></x-ui.icon>
                                        {{ __('users.show.generate_link') }}
                                    </x-ui.button>
                                @endcan
                            @endif

                            @can(Permissions::EDIT_USERS)
                                <x-ui.button @click="$dispatch('confirm-modal', {
                                                 title: '{{ __('actions.deactivate') }}',
                                                 message: '{{ __('users.show.confirm_deactivate') }}',
                                                 confirmColor: 'warning',
                                                 confirmEvent: 'confirm-deactivate-user'
                                             })"
                                             color="warning"
                                             size="sm">
                                    <x-ui.icon name="x-mark"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.deactivate') }}
                                </x-ui.button>
                            @endcan
                        @endif

                        @can(Permissions::DELETE_USERS)
                            <x-ui.button @click="$dispatch('confirm-modal', {
                                                 title: '{{ __('actions.delete') }}',
                                                 message: '{{ __('actions.confirm_delete') }}',
                                                 confirmColor: 'error',
                                                 confirmEvent: 'confirm-delete-user'
                                             })"
                                         color="error"
                                         size="sm">
                                <x-ui.icon name="trash"
                                           size="sm"></x-ui.icon>
                                {{ __('actions.delete') }}
                            </x-ui.button>
                        @endcan
                    </div>
                </div>

                {{-- Status badge --}}
                <div class="mb-6">
                    <x-ui.badge :color="$user->is_active ? 'success' : 'error'"
                                size="lg">
                        {{ $user->is_active ? __('users.active') : __('users.inactive') }}
                    </x-ui.badge>
                </div>

                {{-- User details --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    {{-- Personal Information --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('users.personal_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.name') }}</span>
                                <p class="font-medium">{{ $user->name }}</p>
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.email') }}</span>
                                <p class="font-medium">{{ $user->email ?? '—' }}</p>
                                @if ($user->hasPendingEmailChange())
                                    <div class="alert alert-warning mt-2 p-2">
                                        <x-ui.icon name="clock"
                                                   size="sm"></x-ui.icon>
                                        <span
                                              class="text-sm">{{ __('users.show.pending_email', ['email' => $user->pending_email]) }}</span>
                                        @can(Permissions::EDIT_USERS)
                                            <x-ui.button @click="$dispatch('confirm-modal', {
                                                             title: '{{ __('actions.cancel') }}',
                                                             message: '{{ __('users.show.confirm_cancel_pending') }}',
                                                             confirmEvent: 'confirm-cancel-pending-email'
                                                         })"
                                                         color="ghost"
                                                         size="xs">
                                                {{ __('actions.cancel') }}
                                            </x-ui.button>
                                        @endcan
                                    </div>
                                @endif
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.username') }}</span>
                                <p class="font-medium">{{ $user->username ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Account Information --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('users.account_info') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.uuid') }}</span>
                                <p class="font-mono text-sm">{{ $user->uuid }}</p>
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.created_at') }}</span>
                                <p class="font-medium">
                                    {{ $user->created_at->diffForHumans() }}
                                    <span
                                          class="text-base-content/60 text-sm">({{ $user->created_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.updated_at') }}</span>
                                <p class="font-medium">
                                    {{ $user->updated_at->diffForHumans() }}
                                    <span
                                          class="text-base-content/60 text-sm">({{ $user->updated_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('users.email_verified_at') }}</span>
                                <p class="font-medium">
                                    @if ($user->email_verified_at)
                                        {{ $user->email_verified_at->diffForHumans() }}
                                        <span
                                              class="text-base-content/60 text-sm">({{ $user->email_verified_at->format('Y-m-d H:i') }})</span>
                                    @else
                                        <span class="text-error italic">{{ __('users.not_verified') }}</span>
                                    @endif
                                </p>
                            </div>

                            @if ($user->last_login_at)
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('users.last_login_at') }}</span>
                                    <p class="font-medium">
                                        {{ $user->last_login_at->diffForHumans() }}
                                        <span
                                              class="text-base-content/60 text-sm">({{ $user->last_login_at->format('Y-m-d H:i') }})</span>
                                    </p>
                                </div>
                            @endif

                            @if ($user->createdBy)
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('users.created_by') }}</span>
                                    <p class="font-medium">{{ $user->createdBy->name }}</p>
                                </div>
                            @endif

                            @if ($user->createdUsers()->exists())
                                <div>
                                    <span
                                          class="text-base-content/60 text-sm">{{ __('users.created_users_count') }}</span>
                                    <p class="font-medium">{{ $user->createdUsers()->count() }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Notification Preferences --}}
                <div class="mt-8 space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70 border-b pb-2">{{ __('users.notification_preferences') }}</x-ui.title>

                    @if (!empty($user->notification_preferences))
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($user->notification_preferences as $channel => $enabled)
                                <div class="bg-base-200 flex items-center justify-between rounded-lg p-3">
                                    <span class="font-medium">{{ Str::headline($channel) }}</span>
                                    <x-ui.badge :color="$enabled ? 'success' : null"
                                                :variant="$enabled ? null : 'ghost'"
                                                size="sm">
                                        {{ $enabled ? __('users.active') : __('users.inactive') }}
                                    </x-ui.badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-base-content/60 italic">{{ __('users.not_set') }}</p>
                    @endif
                </div>

                {{-- Preferences --}}
                <div class="mt-8 space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70 border-b pb-2">{{ __('users.preferences') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('users.timezone') }}</span>
                            <p class="font-medium">{{ $user->timezone ?? __('users.not_set') }}</p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('users.locale') }}</span>
                            <p class="font-medium">{{ $user->locale ?? __('users.not_set') }}</p>
                        </div>

                    </div>
                </div>

                {{-- Roles --}}
                @if ($user->roles->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('users.roles') }}</x-ui.title>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <x-ui.badge color="primary"
                                            size="md">{{ $role->display_name }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Teams --}}
                @if ($user->teams->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('users.teams') }}</x-ui.title>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->teams as $team)
                                <x-ui.badge color="secondary"
                                            size="md">{{ $team->name }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Direct Permissions --}}
                @if ($user->permissions->count() > 0)
                    <div class="mt-8 space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('users.direct_permissions') }}</x-ui.title>
                        <p class="text-base-content/60 text-sm">{{ __('users.direct_permissions_description') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->permissions as $permission)
                                <x-ui.badge color="info"
                                            size="md">{{ $permission->display_name ?? $permission->name }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Back button --}}
                <div class="mt-8 border-t pt-4">
                    <x-ui.button href="{{ route('users.index') }}"
                                 variant="ghost"
                                 wire:navigate>
                        <x-ui.icon name="arrow-left"
                                   size="sm"></x-ui.icon>
                        {{ __('actions.back_to_list') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Activation Link Modal --}}
        @if ($showActivationModal && $activationLink)
            <x-ui.base-modal title="{{ __('users.show.activation_link_title') }}"
                             open
                             @close="$wire.closeActivationModal()">
                <div class="space-y-4">
                    <p class="text-base-content/70">{{ __('users.show.activation_link_description') }}</p>

                    <div class="flex items-center gap-2">
                        <x-ui.input type="text"
                                    value="{{ $activationLink }}"
                                    readonly
                                    class="font-mono text-sm"></x-ui.input>
                        <x-ui.copy-button :text="$activationLink"
                                          size="sm"
                                          color="primary"
                                          showText></x-ui.copy-button>
                    </div>

                    <div class="alert alert-warning">
                        <x-ui.icon name="exclamation-triangle"
                                   size="sm"></x-ui.icon>
                        <span>{{ __('users.show.activation_link_warning', ['days' => 7]) }}</span>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button wire:click="closeActivationModal"
                                 variant="ghost">{{ __('actions.close') }}</x-ui.button>
                </x-slot:actions>
            </x-ui.base-modal>
        @endif
    @else
        <div class="alert alert-error">
            <x-ui.icon name="exclamation-triangle"
                       size="sm"></x-ui.icon>
            <span>{{ __('users.user_not_found') }}</span>
        </div>
    @endif
</section>
