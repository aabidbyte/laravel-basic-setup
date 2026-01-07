<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <ul class="menu bg-base-200 rounded-box p-2">
            <li>
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                >
                    {{ __('settings.profile.title') }}
                </a>
            </li>
            <li>
                <a
                    href="{{ route('user-password.edit') }}"
                    wire:navigate
                    class="{{ request()->routeIs('user-password.edit') ? 'active' : '' }}"
                >
                    {{ __('settings.password.title') }}
                </a>
            </li>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <li>
                    <a
                        href="{{ route('two-factor.show') }}"
                        wire:navigate
                        class="{{ request()->routeIs('two-factor.show') ? 'active' : '' }}"
                    >
                        {{ __('settings.two_factor.title') }}
                    </a>
                </li>
            @endif
        </ul>
    </div>

    <div class="divider md:hidden"></div>

    <div class="w-full max-w-lg ">
        {{ $slot }}
    </div>
</div>
