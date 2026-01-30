<form method="POST"
      action="{{ route('preferences.theme') }}"
      x-data="themeSwitcher('{{ $currentTheme }}')">
    @csrf
    <input type="hidden"
           name="theme"
           x-ref="themeInput"
           :value="currentTheme === 'dark' ? 'dark' : 'light'">
    <x-ui.label variant="plain"
                class="swap swap-rotate">
        <input type="checkbox"
               @checked($currentTheme === 'dark')
               @change="toggle($event)" />

        {{-- Sun icon (light theme) - shown when checkbox is unchecked (swap-off) --}}
        <div class="swap-off">
            <x-ui.icon name="sun"
                       class="h-5 w-5 fill-current"></x-ui.icon>
        </div>

        {{-- Moon icon (dark theme) - shown when checkbox is checked (swap-on) --}}
        <div class="swap-on">
            <x-ui.icon name="moon"
                       class="h-5 w-5 fill-current"></x-ui.icon>
        </div>
    </x-ui.label>
</form>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('themeSwitcher', (initialTheme = 'light') => ({
                    currentTheme: initialTheme,

                    toggle(event) {
                        const isDark = event.target.checked;
                        this.currentTheme = isDark ? 'dark' : 'light';
                        this.$refs.themeInput.value = this.currentTheme;
                        this.$refs.themeInput.form.submit();
                    },
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
