        <div class="grid h-full min-h-screen w-full grid-cols-1 lg:grid-cols-2">
            <div class="bg-base-content hidden grid-cols-1 place-items-center text-center lg:grid lg:text-left">
                <a href="{{ route('dashboard') }}"
                   class="mb-8 flex items-center text-lg font-medium"
                   wire:navigate>
                    <x-app-logo-icon class="text-base-300 me-2 h-7 fill-current"></x-app-logo-icon>
                    <span class="text-base-300">{{ config('app.name', 'Laravel') }}</span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <blockquote class="mx-auto max-w-xl space-y-2">
                    <p class="text-base-300 text-2xl font-bold">&ldquo;{{ trim($message) }}&rdquo;</p>
                    <footer>
                        <p class="text-base-300/70 text-lg font-semibold">{{ trim($author) }}</p>
                    </footer>
                </blockquote>
            </div>
            <div class="grid grid-cols-1 place-items-center">
                <div class="card bg-base-100 w-full max-w-sm shrink-0 shadow-2xl">
                    <div class="flex items-center justify-end gap-2 p-2">
                        <x-preferences.theme-switcher></x-preferences.theme-switcher>
                        <x-preferences.locale-switcher></x-preferences.locale-switcher>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('dashboard') }}"
                           class="mb-4 flex flex-col items-center gap-2 font-medium lg:hidden"
                           wire:navigate>
                            <x-app-logo-icon class="text-base-content size-9 fill-current"></x-app-logo-icon>
                            <span class="text-base-content">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
