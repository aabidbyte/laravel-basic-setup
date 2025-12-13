<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-base-100">
    <header class="sticky top-0 z-40 flex h-16 items-center gap-4 border-b border-base-300 bg-base-200 px-4">
        <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
            wire:navigate>
            <x-app-logo />
        </a>

        <nav class="hidden lg:flex lg:items-center lg:gap-1">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-base-300 text-base-content' : 'text-base-content/70 hover:bg-base-300 hover:text-base-content' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                {{ __('Dashboard') }}
            </a>
        </nav>

        <div class="flex-1"></div>

        <nav class="hidden lg:flex lg:items-center lg:gap-1">
            <a href="https://github.com/laravel/livewire-starter-kit" target="_blank"
                class="flex h-10 items-center justify-center rounded-lg px-3 text-base-content/70 hover:bg-base-300 hover:text-base-content"
                title="{{ __('Repository') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </a>
            <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank"
                class="flex h-10 items-center justify-center rounded-lg px-3 text-base-content/70 hover:bg-base-300 hover:text-base-content"
                title="{{ __('Documentation') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </a>
        </nav>

        <!-- Desktop User Menu -->
        <div class="relative hidden lg:block" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-base-300 text-base-content">
                    {{ auth()->user()->initials() }}
                </span>
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                class="absolute end-0 top-full mt-2 w-64 rounded-lg border border-base-300 bg-base-100 shadow-lg"
                style="display: none;">
                <div class="p-2">
                    <div class="flex items-center gap-2 rounded-lg px-3 py-2">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-base-300 text-base-content">
                            {{ auth()->user()->initials() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-base-content">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-base-content/70">{{ auth()->user()->email }}
                            </p>
                        </div>
                    </div>

                    <div class="divider my-2"></div>

                    <a href="{{ route('profile.edit') }}" wire:navigate
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-base-content hover:bg-base-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('Settings') }}
                    </a>

                    <div class="divider my-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-base-content hover:bg-base-200"
                            data-test="logout-button">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{ $slot }}
</body>

</html>
