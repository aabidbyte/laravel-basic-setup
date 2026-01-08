{{--
    Settings Layout Component with consolidated tab navigation.
    Tabs: Account, Security, Preferences, Notifications, Mail (permission-gated)
--}}
<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <ul class="menu bg-base-200 rounded-box p-2">
            {{-- Account Tab --}}
            <li>
                <a
                    href="{{ route('settings.account') }}"
                    wire:navigate
                    @class(['active' => request()->routeIs('settings.account', 'profile.edit')])
                >
                    <x-ui.icon name="user" class="h-4 w-4"></x-ui.icon>
                    {{ __('settings.tabs.account') }}
                </a>
            </li>

            {{-- Security Tab --}}
            <li>
                <a
                    href="{{ route('settings.security') }}"
                    wire:navigate
                    @class(['active' => request()->routeIs('settings.security', 'two-factor.show', 'user-password.edit')])
                >
                    <x-ui.icon name="shield-check" class="h-4 w-4"></x-ui.icon>
                    {{ __('settings.tabs.security') }}
                </a>
            </li>

            {{-- Preferences Tab --}}
            <li>
                <a
                    href="{{ route('settings.preferences') }}"
                    wire:navigate
                    @class(['active' => request()->routeIs('settings.preferences')])
                >
                    <x-ui.icon name="cog-6-tooth" class="h-4 w-4"></x-ui.icon>
                    {{ __('settings.tabs.preferences') }}
                </a>
            </li>

            {{-- Notifications Tab --}}
            <li>
                <a
                    href="{{ route('settings.notifications') }}"
                    wire:navigate
                    @class(['active' => request()->routeIs('settings.notifications')])
                >
                    <x-ui.icon name="bell" class="h-4 w-4"></x-ui.icon>
                    {{ __('settings.tabs.notifications') }}
                </a>
            </li>

            {{-- Mail Tab (Permission-gated) --}}
            @can(\App\Constants\Auth\Permissions::CONFIGURE_MAIL_SETTINGS)
                <li>
                    <a
                        href="{{ route('settings.mail') }}"
                        wire:navigate
                        @class(['active' => request()->routeIs('settings.mail')])
                    >
                        <x-ui.icon name="envelope" class="h-4 w-4"></x-ui.icon>
                        {{ __('settings.tabs.mail') }}
                    </a>
                </li>
            @endcan
        </ul>
    </div>

    <div class="divider md:hidden"></div>

    <div class="w-full max-w-lg ">
        {{ $slot }}
    </div>
</div>
