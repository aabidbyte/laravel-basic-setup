@props(['user'])

@if ($user)
    @can(App\Constants\Auth\Permissions::EDIT_USERS())
        <x-ui.button href="{{ route('users.edit', $user->uuid) }}"
                     color="primary"
                     size="sm"
                     icon="pencil"
                     wire:navigate>
            {{ __('actions.edit') }}
        </x-ui.button>
    @endcan

    @if (!$user->is_active)
        {{-- Inactive user: show activation options --}}
        @if (!$user->email)
            {{-- No email: generate activation link --}}
            @can(App\Constants\Auth\Permissions::GENERATE_ACTIVATION_USERS())
                <x-ui.button wire:click="generateActivationLink"
                             color="info"
                             size="sm"
                             icon="link">
                    {{ __('users.show.generate_link') }}
                </x-ui.button>
            @endcan
        @else
            {{-- Has email: send activation email --}}
            @can(App\Constants\Auth\Permissions::EDIT_USERS())
                <x-ui.button x-on:click="confirmModal({
                             title: @js(__('users.show.send_activation_email')),
                             message: @js(__('users.show.confirm_send_activation')),
                             callback: 'confirm-send-activation-email'
                         })"
                             color="primary"
                             size="sm"
                             icon="envelope">
                    {{ __('users.show.send_activation_email') }}
                </x-ui.button>
            @endcan
        @endif

        @can(App\Constants\Auth\Permissions::EDIT_USERS())
            <x-ui.button x-on:click="confirmModal({
                         title: @js(__('actions.activate')),
                         message: @js(__('users.show.confirm_activate')),
                         callback: 'confirm-activate-user'
                     })"
                         color="success"
                         size="sm"
                         icon="check">
                {{ __('actions.activate') }}
            </x-ui.button>
        @endcan
    @else
        {{-- Active user: password reset and deactivate options --}}
        @if ($user->hasVerifiedEmail())
            @can(App\Constants\Auth\Permissions::EDIT_USERS())
                <x-ui.button x-on:click="confirmModal({
                             title: @js(__('users.show.send_password_reset')),
                             message: @js(__('users.show.confirm_send_reset')),
                             callback: 'confirm-send-password-reset'
                         })"
                             color="info"
                             size="sm"
                             icon="key">
                    {{ __('users.show.send_password_reset') }}
                </x-ui.button>
            @endcan
        @elseif ($user->email)
            {{-- Has email but not verified --}}
            @can(App\Constants\Auth\Permissions::EDIT_USERS())
                <x-ui.button x-on:click="confirmModal({
                             title: @js(__('users.show.send_activation_email')),
                             message: @js(__('users.show.confirm_send_activation')),
                             callback: 'confirm-send-activation-email'
                         })"
                             color="primary"
                             size="sm"
                             icon="envelope">
                    {{ __('users.show.send_activation_email') }}
                </x-ui.button>
            @endcan
        @else
            {{-- No email: generate activation link --}}
            @can(App\Constants\Auth\Permissions::GENERATE_ACTIVATION_USERS())
                <x-ui.button wire:click="generateActivationLink"
                             color="info"
                             size="sm"
                             icon="link">
                    {{ __('users.show.generate_link') }}
                </x-ui.button>
            @endcan
        @endif

        @can(App\Constants\Auth\Permissions::EDIT_USERS())
            <x-ui.button x-on:click="confirmModal({
                         title: @js(__('actions.deactivate')),
                         message: @js(__('users.show.confirm_deactivate')),
                         callback: 'confirm-deactivate-user'
                     })"
                         color="warning"
                         size="sm"
                         icon="x-mark">
                {{ __('actions.deactivate') }}
            </x-ui.button>
        @endcan
    @endif

    @can(App\Constants\Auth\Permissions::DELETE_USERS())
        <x-ui.button x-on:click="confirmModal({
                     title: @js(__('actions.delete')),
                     message: @js(__('actions.confirm_delete')),
                     callback: 'confirm-delete-user'
                 })"
                     color="error"
                     size="sm"
                     icon="trash">
            {{ __('actions.delete') }}
        </x-ui.button>
    @endcan
@endif
