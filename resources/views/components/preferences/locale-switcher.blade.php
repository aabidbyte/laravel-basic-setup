<x-ui.dropdown
    placement="end"
    menu
    menuSize="sm"
>
    <x-slot:trigger>
        <div class="btn btn-ghost btn-sm">
            <x-ui.icon
                name="{{ $localeMetadata['icon']['name'] ?? 'globe-alt' }}"
                pack="{{ $localeMetadata['icon']['pack'] ?? 'heroicons' }}"
                class="h-5 w-5"
            ></x-ui.icon>
            <span class="sr-only">{{ __('ui.preferences.locale') }}</span>
        </div>
    </x-slot:trigger>

    @foreach ($supportedLocales as $localeCode => $localeData)
        <div>
            <form
                method="POST"
                action="{{ route('preferences.locale') }}"
            >
                @csrf
                <input
                    type="hidden"
                    name="locale"
                    value="{{ $localeCode }}"
                >
                <button
                    type="submit"
                    class="btn btn-ghost btn-sm justify-start w-full {{ $currentLocale === $localeCode ? 'btn-active' : '' }}"
                >
                    <x-ui.icon
                        name="{{ $localeData['icon']['name'] ?? 'globe-alt' }}"
                        pack="{{ $localeData['icon']['pack'] ?? 'heroicons' }}"
                        class="h-4 w-4"
                    ></x-ui.icon>
                    <span>{{ $localeData['native_name'] ?? $localeCode }}</span>
                </button>
            </form>
        </div>
    @endforeach
</x-ui.dropdown>
