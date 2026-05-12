<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\UserService;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'types.user';

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    protected int $placeholderRows = 4;

    #[Locked]
    public ?User $user = null;

    public ?string $activationLink = null;

    public bool $showActivationModal = false;

    /**
     * Mount the component and authorize access.
     */
    public function mount(User $user): void
    {
        $this->authorize(Permissions::VIEW_USERS());

        $this->user = $user;
        $this->updatePageHeader();
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = 'pages.common.show.title';
        $this->pageSubtitle = 'pages.common.show.subtitle';
    }

    /**
     * Override getPageTitle to provide dynamic parameters.
     */
    public function getPageTitle(): string
    {
        $title = parent::getPageTitle();
        return __($title, [
            'name' => $this->user->name,
            'type' => __($this->modelTypeLabel),
        ]);
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
     * Generate activation link for the user.
     */
    public function generateActivationLink(UserService $userService): void
    {
        $this->authorize(Permissions::GENERATE_ACTIVATION_USERS());

        $this->activationLink = $userService->generateActivationLink($this->user);
        $this->showActivationModal = true;

        NotificationBuilder::make()->title('users.show.activation_link_generated')->success()->send();
    }

    /**
     * Send password reset email to the user.
     */
    public function sendPasswordResetEmail(UserService $userService): void
    {
        $this->authorize(Permissions::EDIT_USERS());

        $userService->sendPasswordResetEmail($this->user);

        NotificationBuilder::make()->title('users.show.password_reset_sent')->success()->send();
    }

    /**
     * Send activation email to the user.
     */
    public function sendActivationEmail(UserService $userService): void
    {
        $this->authorize(Permissions::EDIT_USERS());

        $userService->sendActivationEmail($this->user);

        NotificationBuilder::make()->title('users.show.activation_email_sent')->success()->send();
    }

    /**
     * Cancel pending email change.
     */
    public function cancelPendingEmailChange(UserService $userService): void
    {
        $this->authorize(Permissions::EDIT_USERS());

        $userService->cancelPendingEmailChange($this->user);
        $this->user = $this->user->fresh();

        NotificationBuilder::make()->title('users.show.pending_email_cancelled')->success()->send();
    }

    /**
     * Activate the user.
     */
    public function activateUser(): void
    {
        $this->authorize(Permissions::EDIT_USERS());

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
        $this->authorize(Permissions::EDIT_USERS());

        $this->user->deactivate();
        $this->user = $this->user->fresh();

        NotificationBuilder::make()
            ->title('pages.common.messages.deactivated', ['name' => $this->user->name])
            ->warning()
            ->persist()
            ->send();
    }

    /**
     * Close activation modal and clear link.
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
        $this->authorize(Permissions::DELETE_USERS());

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

<x-layouts.page backHref="{{ route('users.index') }}">
    <x-slot:topActions>
        @if ($user)
            @can(Permissions::EDIT_USERS())
                <x-ui.button href="{{ route('users.edit', $user->uuid) }}"
                             variant="primary"
                             size="sm"
                             wire:navigate>
                    <x-ui.icon name="pencil"
                               size="sm" />
                    {{ __('actions.edit') }}
                </x-ui.button>
            @endcan

            @if (!$user->is_active)
                {{-- Inactive user: show activation options --}}
                @if (!$user->email)
                    {{-- No email: generate activation link --}}
                    @can(Permissions::GENERATE_ACTIVATION_USERS())
                        <x-ui.button wire:click="generateActivationLink"
                                     variant="secondary"
                                     size="sm">
                            <x-ui.icon name="link"
                                       size="sm" />
                            {{ __('users.show.generate_link') }}
                        </x-ui.button>
                    @endcan
                @else
                    {{-- Has email: send activation email --}}
                    @can(Permissions::EDIT_USERS())
                        <x-ui.button @click="$dispatch('confirm-modal', {
                                     title: '{{ __('users.show.send_activation_email') }}',
                                     message: '{{ __('users.show.confirm_send_activation') }}',
                                     confirmEvent: 'confirm-send-activation-email'
                                 })"
                                     variant="secondary"
                                     size="sm">
                            <x-ui.icon name="envelope"
                                       size="sm" />
                            {{ __('users.show.send_activation_email') }}
                        </x-ui.button>
                    @endcan
                @endif

                @can(Permissions::EDIT_USERS())
                    <x-ui.button @click="$dispatch('confirm-modal', {
                                 title: '{{ __('actions.activate') }}',
                                 message: '{{ __('users.show.confirm_activate') }}',
                                 confirmEvent: 'confirm-activate-user'
                             })"
                                 variant="success"
                                 size="sm">
                        <x-ui.icon name="check"
                                   size="sm" />
                        {{ __('actions.activate') }}
                    </x-ui.button>
                @endcan
            @else
                {{-- Active user: password reset and deactivate options --}}
                @if ($user->hasVerifiedEmail())
                    @can(Permissions::EDIT_USERS())
                        <x-ui.button @click="$dispatch('confirm-modal', {
                                     title: '{{ __('users.show.send_password_reset') }}',
                                     message: '{{ __('users.show.confirm_send_reset') }}',
                                     confirmEvent: 'confirm-send-password-reset'
                                 })"
                                     variant="info"
                                     size="sm">
                            <x-ui.icon name="key"
                                       size="sm" />
                            {{ __('users.show.send_password_reset') }}
                        </x-ui.button>
                    @endcan
                @elseif ($user->email)
                    {{-- Has email but not verified --}}
                    @can(Permissions::EDIT_USERS())
                        <x-ui.button @click="$dispatch('confirm-modal', {
                                     title: '{{ __('users.show.send_activation_email') }}',
                                     message: '{{ __('users.show.confirm_send_activation') }}',
                                     confirmEvent: 'confirm-send-activation-email'
                                 })"
                                     variant="secondary"
                                     size="sm">
                            <x-ui.icon name="envelope"
                                       size="sm" />
                            {{ __('users.show.send_activation_email') }}
                        </x-ui.button>
                    @endcan
                @else
                    {{-- No email: generate activation link --}}
                    @can(Permissions::GENERATE_ACTIVATION_USERS())
                        <x-ui.button wire:click="generateActivationLink"
                                     variant="secondary"
                                     size="sm">
                            <x-ui.icon name="link"
                                       size="sm" />
                            {{ __('users.show.generate_link') }}
                        </x-ui.button>
                    @endcan
                @endif

                @can(Permissions::EDIT_USERS())
                    <x-ui.button @click="$dispatch('confirm-modal', {
                                 title: '{{ __('actions.deactivate') }}',
                                 message: '{{ __('users.show.confirm_deactivate') }}',
                                 confirmColor: 'warning',
                                 confirmEvent: 'confirm-deactivate-user'
                             })"
                                 variant="warning"
                                 size="sm">
                        <x-ui.icon name="x-mark"
                                   size="sm" />
                        {{ __('actions.deactivate') }}
                    </x-ui.button>
                @endcan
            @endif

            @can(Permissions::DELETE_USERS())
                <x-ui.button @click="$dispatch('confirm-modal', {
                                 title: '{{ __('actions.delete') }}',
                                 message: '{{ __('actions.confirm_delete') }}',
                                 confirmColor: 'error',
                                 confirmEvent: 'confirm-delete-user'
                             })"
                             variant="error"
                             size="sm">
                    <x-ui.icon name="trash"
                               size="sm" />
                    {{ __('actions.delete') }}
                </x-ui.button>
            @endcan
        @endif
    </x-slot:topActions>

    <div class="mx-auto w-full max-w-4xl space-y-8"
         @confirm-send-activation-email.window="$wire.sendActivationEmail()"
         @confirm-activate-user.window="$wire.activateUser()"
         @confirm-send-password-reset.window="$wire.sendPasswordResetEmail()"
         @confirm-deactivate-user.window="$wire.deactivateUser()"
         @confirm-delete-user.window="$wire.deleteUser()"
         @confirm-cancel-pending-email.window="$wire.cancelPendingEmailChange()">

        <div class="flex items-center gap-4">
            <x-ui.avatar :user="$user"
                         size="lg" />
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                <x-ui.badge :color="$user->is_active ? 'success' : 'error'"
                            size="md">
                    {{ $user->is_active ? __('users.active') : __('users.inactive') }}
                </x-ui.badge>
            </div>
        </div>

        {{-- User details --}}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            {{-- Personal Information --}}
            <x-ui.card title="{{ __('users.personal_info') }}">
                <div class="space-y-4">
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
                                           size="sm" />
                                <span
                                      class="text-sm">{{ __('users.show.pending_email', ['email' => $user->pending_email]) }}</span>
                                @can(Permissions::EDIT_USERS())
                                    <x-ui.button @click="$dispatch('confirm-modal', {
                                                             title: '{{ __('actions.cancel') }}',
                                                             message: '{{ __('users.show.confirm_cancel_pending') }}',
                                                             confirmEvent: 'confirm-cancel-pending-email'
                                                         })"
                                                 variant="ghost"
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
            </x-ui.card>

            {{-- Account Information --}}
            <x-ui.card title="{{ __('users.account_info') }}">
                <div class="space-y-4">
                    <div>
                        <span class="text-base-content/60 text-sm">{{ __('users.uuid') }}</span>
                        <p class="font-mono text-sm">{{ $user->uuid }}</p>
                    </div>

                    <div>
                        <span class="text-base-content/60 text-sm">{{ __('users.created_at') }}</span>
                        <p class="font-medium">
                            {{ $user->created_at->diffForHumans() }}
                            <span
                                  class="text-base-content/60 text-xs">({{ $user->created_at->format('Y-m-d H:i') }})</span>
                        </p>
                    </div>

                    <div>
                        <span class="text-base-content/60 text-sm">{{ __('users.email_verified_at') }}</span>
                        <p class="font-medium">
                            @if ($user->email_verified_at)
                                {{ $user->email_verified_at->diffForHumans() }}
                                <span
                                      class="text-base-content/60 text-xs">({{ $user->email_verified_at->format('Y-m-d H:i') }})</span>
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
                                      class="text-base-content/60 text-xs">({{ $user->last_login_at->format('Y-m-d H:i') }})</span>
                            </p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Roles, Teams & Permissions --}}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            {{-- Roles --}}
            <x-ui.card title="{{ __('users.roles') }}"
                       class="h-full">
                @if ($user->roles->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($user->roles as $role)
                            <x-ui.badge variant="primary"
                                        size="md">{{ $role->display_name }}</x-ui.badge>
                        @endforeach
                    </div>
                @else
                    <p class="text-base-content/60 italic">{{ __('common.none') }}</p>
                @endif
            </x-ui.card>

            {{-- Teams --}}
            <x-ui.card title="{{ __('users.teams') }}"
                       class="h-full">
                @if ($user->teams->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($user->teams as $team)
                            <x-ui.badge variant="secondary"
                                        size="md">{{ $team->name }}</x-ui.badge>
                        @endforeach
                    </div>
                @else
                    <p class="text-base-content/60 italic">{{ __('common.none') }}</p>
                @endif
            </x-ui.card>

            {{-- Direct Permissions --}}
            <x-ui.card title="{{ __('users.direct_permissions') }}"
                       class="h-full">
                @if ($user->permissions->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($user->permissions as $permission)
                            <x-ui.badge variant="info"
                                        size="md">{{ $permission->display_name ?? $permission->name }}</x-ui.badge>
                        @endforeach
                    </div>
                @else
                    <p class="text-base-content/60 italic">{{ __('common.none') }}</p>
                @endif
            </x-ui.card>
        </div>

        {{-- Preferences --}}
        <x-ui.card title="{{ __('users.preferences') }}">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <span class="text-base-content/60 text-sm">{{ __('users.timezone') }}</span>
                    <p class="font-medium">{{ $user->timezone ?? __('users.not_set') }}
                    </p>
                </div>

                <div>
                    <span class="text-base-content/60 text-sm">{{ __('users.locale') }}</span>
                    <p class="font-medium">{{ $user->locale ?? __('users.not_set') }}</p>
                </div>
            </div>
        </x-ui.card>

        {{-- Activation Link Modal --}}
        @if ($showActivationModal && $activationLink)
            <x-ui.base-modal title="{{ __('users.show.activation_link_title') }}"
                             open
                             @close="$wire.closeActivationModal()">
                <div class="space-y-4">
                    <p class="text-base-content/70">
                        {{ __('users.show.activation_link_description') }}</p>

                    <div class="flex items-center gap-2">
                        <x-ui.input type="text"
                                    value="{{ $activationLink }}"
                                    readonly
                                    class="font-mono text-sm" />
                        <x-ui.copy-button :text="$activationLink"
                                          size="sm"
                                          variant="primary"
                                          show-text />
                    </div>

                    <div class="alert alert-warning">
                        <x-ui.icon name="exclamation-triangle"
                                   size="sm" />
                        <span>{{ __('users.show.activation_link_warning', ['days' => 7]) }}</span>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button wire:click="closeActivationModal"
                                 variant="ghost">{{ __('actions.close') }}</x-ui.button>
                </x-slot:actions>
            </x-ui.base-modal>
        @endif
    </div>
</x-layouts.page>
