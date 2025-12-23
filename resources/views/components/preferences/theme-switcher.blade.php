<form method="POST" action="{{ route('preferences.theme') }}" x-data="{ currentTheme: '{{ $currentTheme }}' }">
    @csrf
    <input type="hidden" name="theme" x-ref="themeInput" :value="currentTheme === 'dark' ? 'dark' : 'light'">
    <label class="swap swap-rotate">
        <input type="checkbox" :checked="currentTheme === 'dark'"
            @change="
                $refs.themeInput.value = $el.checked ? 'dark' : 'light';
                $el.closest('form').submit();
            " />

        {{-- Sun icon (light theme) - shown when checkbox is unchecked (swap-off) --}}
        <div class="swap-off">
            <x-ui.icon name="sun" class="h-5 w-5 fill-current"></x-ui.icon>
        </div>

        {{-- Moon icon (dark theme) - shown when checkbox is checked (swap-on) --}}
        <div class="swap-on">
            <x-ui.icon name="moon" class="h-5 w-5 fill-current"></x-ui.icon>
        </div>
    </label>
</form>
