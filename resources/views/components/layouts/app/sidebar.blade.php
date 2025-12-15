<div class="drawer lg:drawer-open">
    <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col">
        <div class="navbar bg-base-200 lg:hidden">
            <div class="flex-none">
                <label for="sidebar-drawer" class="btn btn-square btn-ghost drawer-button">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
            </div>
            <div class="flex-1">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl" wire:navigate>
                    <x-app-logo />
                </a>
            </div>
            <div class="flex-none">
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full bg-base-300 text-base-content">
                            <span class="text-xs">{{ auth()->user()->initials() }}</span>
                        </div>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">
                            <span>{{ auth()->user()->name }}</span>
                        </li>
                        <li><a href="{{ route('profile.edit') }}" wire:navigate>{{ __('Settings') }}</a></li>
                        <li>
                            <x-ui.form method="POST" action="{{ route('logout') }}">
                                <x-ui.button type="submit" data-test="logout-button">{{ __('Log Out') }}</x-ui.button>
                            </x-ui.form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    <div class="drawer-side">
        <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
        <aside class="min-h-full w-64 bg-base-200">
            <div class="p-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
                    <x-app-logo />
                </a>
            </div>
            <ul class="menu p-4 w-full">
                <li class="menu-title">
                    <span>{{ __('Platform') }}</span>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ __('Dashboard') }}
                    </a>
                </li>
                <li class="menu-title">
                    <span>{{ __('Resources') }}</span>
                </li>
                <li>
                    <a href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        {{ __('Repository') }}
                    </a>
                </li>
                <li>
                    <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ __('Documentation') }}
                    </a>
                </li>
            </ul>
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-300">
                <div class="dropdown dropdown-top">
                    <div tabindex="0" role="button" class="btn btn-ghost w-full justify-start"
                        data-test="sidebar-menu-button">
                        <div class="avatar placeholder">
                            <div class="w-10 rounded-full bg-base-300 text-base-content">
                                <span class="text-xs">{{ auth()->user()->initials() }}</span>
                            </div>
                        </div>
                        <div class="flex-1 text-left">
                            <div class="font-bold">{{ auth()->user()->name }}</div>
                            <div class="text-xs opacity-50">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content mb-2 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">
                            <span>{{ auth()->user()->name }}</span>
                        </li>
                        <li><a href="{{ route('profile.edit') }}" wire:navigate>{{ __('Settings') }}</a></li>
                        <li>
                            <x-ui.form method="POST" action="{{ route('logout') }}">
                                <x-ui.button type="submit" data-test="logout-button">{{ __('Log Out') }}</x-ui.button>
                            </x-ui.form>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</div>
