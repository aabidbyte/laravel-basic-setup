<?php

use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;

new class extends BasePageComponent {
    public ?string $pageTitle = 'settings.tabs.preferences';

    public ?string $pageSubtitle = 'settings.preferences.description';

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 3;

    public string $theme = 'light';

    public string $locale = 'en_US';

    public string $timezone = 'UTC';

    /**
     * Mount the component.
     */
    public function mount(FrontendPreferencesService $preferencesService): void
    {
        $this->theme = $preferencesService->getTheme();
        $this->locale = $preferencesService->getLocale();

        $user = Auth::user();
        $this->timezone = $user?->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Get available themes.
     */
    public function getThemesProperty(): array
    {
        return [
            'light' => __('settings.preferences.theme_light'),
            'dark' => __('settings.preferences.theme_dark'),
        ];
    }

    /**
     * Get available locales.
     */
    public function getLocalesProperty(I18nService $i18nService): array
    {
        return collect($i18nService->getSupportedLocales())->mapWithKeys(fn($data, $code) => [$code => __("locales.{$code}")])->toArray();
    }

    /**
     * Get available timezones.
     */
    public function getTimezonesProperty(): array
    {
        $timezones = timezone_identifiers_list();

        return array_combine($timezones, $timezones);
    }

    /**
     * Save all preferences.
     */
    public function savePreferences(FrontendPreferencesService $preferencesService): void
    {
        $this->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
            'locale' => ['required', 'string'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        // Update theme
        $preferencesService->setTheme($this->theme);

        // Update locale
        $preferencesService->setLocale($this->locale);

        // Update timezone
        $user = Auth::user();
        if ($user) {
            $user->setAttribute('timezone', $this->timezone);
            $user->save();
        }

        $this->redirect(route('settings.preferences'), navigate: false);

        NotificationBuilder::make()->title('settings.preferences.save_success')->success()->send();
    }
}; ?>

<x-layouts.page>
    <section class="w-full">
        <x-settings.layout>
            <x-ui.form wire:submit="savePreferences"
                       class="w-full space-y-6">
                {{-- Theme Selection --}}
                <div class="form-control w-full">
                    <x-ui.label :text="__('settings.preferences.theme_label')"></x-ui.label>
                    <div class="flex gap-4">
                        @foreach ($this->themes as $value => $label)
                            <x-ui.label class="flex cursor-pointer items-center gap-2"
                                        variant="plain">
                                <input type="radio"
                                       wire:model="theme"
                                       name="theme"
                                       value="{{ $value }}"
                                       class="radio radio-primary" />
                                <span class="flex items-center gap-2">
                                    @if ($value === 'light')
                                        <x-ui.icon name="sun"
                                                   class="h-5 w-5"></x-ui.icon>
                                    @else
                                        <x-ui.icon name="moon"
                                                   class="h-5 w-5"></x-ui.icon>
                                    @endif
                                    {{ $label }}
                                </span>
                            </x-ui.label>
                        @endforeach
                    </div>
                    @error('theme')
                        <x-ui.label variant="plain">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </x-ui.label>
                    @enderror
                </div>

                {{-- Language Selection --}}
                <x-ui.select label="{{ __('settings.preferences.locale_label') }}"
                             name="locale"
                             wire:model="locale"
                             :options="$this->locales"
                             :prepend-empty="false" />

                {{-- Timezone Selection --}}
                <div class="w-full">
                    <x-ui.select label="{{ __('settings.preferences.timezone_label') }}"
                                 name="timezone"
                                 wire:model="timezone"
                                 :options="$this->timezones"
                                 :prepend-empty="false" />
                    <x-ui.label variant="plain" class="mt-1">
                        <span class="label-text-alt text-base-content/70">
                            {{ __('settings.preferences.timezone_help') }}
                        </span>
                    </x-ui.label>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <x-ui.button type="submit"
                                 color="primary"
                                 data-test="save-preferences-button">
                        {{ __('actions.save') }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </x-settings.layout>
    </section>
</x-layouts.page>
