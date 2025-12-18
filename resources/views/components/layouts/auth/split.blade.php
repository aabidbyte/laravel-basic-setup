        <div class=" min-h-screen  grid grid-cols-1 lg:grid-cols-2  w-full h-full">
            <div class="text-center lg:text-left hidden lg:grid bg-base-content grid-cols-1 place-items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center text-lg font-medium mb-8" wire:navigate>
                    <x-app-logo-icon class="me-2 h-7 fill-current text-base-300" />
                    <span class="text-base-300">{{ config('app.name', 'Laravel') }}</span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <blockquote class="space-y-2 max-w-xl mx-auto">
                    <p class="text-2xl font-bold text-base-300">&ldquo;{{ trim($message) }}&rdquo;</p>
                    <footer>
                        <p class="text-lg font-semibold text-base-300/70">{{ trim($author) }}</p>
                    </footer>
                </blockquote>
            </div>
            <div class="grid grid-cols-1 place-items-center">
                <div class="card shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
                    <div class="flex items-center justify-end gap-2 p-2">
                        <x-preferences.theme-switcher />
                        <x-preferences.locale-switcher />
                    </div>
                    <div class="card-body">
                        <a href="{{ route('dashboard') }}"
                            class="flex flex-col items-center gap-2 font-medium lg:hidden mb-4" wire:navigate>
                            <x-app-logo-icon class="size-9 fill-current text-base-content" />
                            <span class="text-base-content">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
