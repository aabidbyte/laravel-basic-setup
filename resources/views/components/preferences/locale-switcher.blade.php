<div class="dropdown dropdown-end" x-data="{ open: false }">
    <div tabindex="0" role="button" class="btn btn-ghost btn-sm" @click="open = !open">
        <x-ui.icon name="{{ $localeMetadata['icon']['name'] ?? 'globe-alt' }}"
            pack="{{ $localeMetadata['icon']['pack'] ?? 'heroicons' }}" class="h-5 w-5" />
        <span class="sr-only">{{ __('ui.preferences.locale') }}</span>
    </div>
    <ul tabindex="0"
        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-48 p-2 shadow-lg border border-base-300"
        x-show="open" @click.away="open = false">
        @foreach ($supportedLocales as $localeCode => $localeData)
            <li>
                <form method="POST" action="{{ route('preferences.locale') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $localeCode }}">
                    <button type="submit"
                        class="btn btn-ghost btn-sm justify-start w-full {{ $currentLocale === $localeCode ? 'btn-active' : '' }}">
                        <x-ui.icon name="{{ $localeData['icon']['name'] ?? 'globe-alt' }}"
                            pack="{{ $localeData['icon']['pack'] ?? 'heroicons' }}" class="h-4 w-4" />
                        <span>{{ $localeData['native_name'] ?? $localeCode }}</span>
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
