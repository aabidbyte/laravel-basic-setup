    <main class="hero min-h-screen bg-base-200">
        <div class="hero-content grid grid-cols-1 lg:grid-cols-2 place-items-center">
            <div class="text-center lg:text-left hidden lg:block">
                <a href="{{ route('dashboard') }}" class="flex items-center text-lg font-medium mb-8" wire:navigate>
                    <x-app-logo-icon class="me-2 h-7 fill-current text-base-content" />
                    {{ config('app.name', 'Laravel') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <blockquote class="space-y-2">
                    <p class="text-2xl font-bold text-base-content">&ldquo;{{ trim($message) }}&rdquo;</p>
                    <footer>
                        <p class="text-lg font-semibold text-base-content/70">{{ trim($author) }}</p>
                    </footer>
                </blockquote>
            </div>
            <div class="card shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
                <div class="flex items-center justify-end gap-2 p-2">
                    <x-preferences.theme-switcher />
                    <x-preferences.locale-switcher />
                </div>
                <div class="card-body">
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col items-center gap-2 font-medium lg:hidden mb-4" wire:navigate>
                        <x-app-logo-icon class="size-9 fill-current text-base-content" />
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </main>
