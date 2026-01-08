<?php

use App\Livewire\Bases\BasePageComponent;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use App\Services\I18nService;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;

new class extends BasePageComponent
{
    public ?string $pageTitle = 'settings.tabs.preferences';

    public ?string $pageSubtitle = 'settings.preferences.description';

    protected string $placeholderType = 'form';

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
        return collect($i18nService->getSupportedLocales())
            ->mapWithKeys(fn ($data, $code) => [$code => $data['native_name'] ?? $code])
            ->toArray();
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
            $user->timezone = $this->timezone;
            $user->save();
        }

        $this->redirect(route('settings.preferences'), navigate: false);

        NotificationBuilder::make()->title('settings.preferences.save_success')->success()->send();
    }
}; ?>

<section class="w-full">
    <x-settings.layout>
        <x-ui.form
            wire:submit="savePreferences"
            class="w-full space-y-6"
        >
            {{-- Theme Selection --}}
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-medium">{{ __('settings.preferences.theme_label') }}</span>
                </label>
                <div class="flex gap-4">
                    @foreach ($this->themes as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                wire:model="theme"
                                name="theme"
                                value="{{ $value }}"
                                class="radio radio-primary"
                            />
                            <span class="flex items-center gap-2">
                                @if ($value === 'light')
                                    <x-ui.icon
                                        name="sun"
                                        class="h-5 w-5"
                                    ></x-ui.icon>
                                @else
                                    <x-ui.icon
                                        name="moon"
                                        class="h-5 w-5"
                                    ></x-ui.icon>
                                @endif
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('theme')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Language Selection --}}
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-medium">{{ __('settings.preferences.locale_label') }}</span>
                </label>
                <select
                    wire:model="locale"
                    class="select select-bordered w-full"
                >
                    @foreach ($this->locales as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('locale')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            {{-- Timezone Selection --}}
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-medium">{{ __('settings.preferences.timezone_label') }}</span>
                </label>
                <select
                    wire:model="timezone"
                    class="select select-bordered w-full"
                >
                    @foreach ($this->timezones as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </select>
                <label class="label">
                    <span class="label-text-alt text-base-content/70">
                        {{ __('settings.preferences.timezone_help') }}
                    </span>
                </label>
                @error('timezone')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-4">
                <x-ui.button
                    type="submit"
                    variant="primary"
                    data-test="save-preferences-button"
                >
                    {{ __('actions.save') }}
                </x-ui.button>
            </div>
        </x-ui.form>
    </x-settings.layout>
</section>
