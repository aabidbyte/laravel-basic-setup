<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\Notifications\NotificationBuilder;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    #[Locked]
    public string $modelId = '';

    #[Locked]
    public bool $isLayout = false;

    public ?EmailTemplate $model = null;

    // Common
    public string $name = '';

    // Content Specific
    public array $entity_types = [];

    public array $context_variables = [];

    // Layout Specific
    public bool $is_default = false;

    // Translations
    public array $translations = [];

    public string $activeLocale = 'en_US';

    /**
     * Mount the component.
     */
    public function mount(string $id): void
    {
        $this->modelId = $id;
        $this->authorize(Permissions::EDIT_EMAIL_TEMPLATES);

        $this->model = EmailTemplate::with(['layout', 'translations'])->findOrFail($id);
        $this->isLayout = $this->model->is_layout;

        $typeLabel = $this->isLayout ? __('types.email_layout') : __('types.email_content');
        $this->pageTitle = __('pages.common.edit.title', ['type' => $typeLabel]);

        $this->fillFromModel();

        $this->activeLocale = app()->getLocale();
        if (!isset($this->translations[$this->activeLocale])) {
            $this->activeLocale = array_key_first($this->translations) ?? 'en_US';
        }
    }

    protected function fillFromModel(): void
    {
        $this->name = $this->model->name;

        if (!$this->isLayout) {
            $this->entity_types = $this->model->entity_types ?? [];
            $this->context_variables = $this->model->context_variables ?? [];
        } else {
            $this->is_default = $this->model->is_default;
        }

        // Translations (Common)
        foreach ($this->model->translations as $translation) {
            $this->translations[$translation->locale] = [
                'subject' => $translation->subject,
                'preheader' => $translation->preheader,
                'html_content' => $translation->html_content,
                'text_content' => $translation->text_content,
            ];
        }

        if (empty($this->translations)) {
            $this->addLocale(app()->getLocale());
        }
    }

    protected function rules(): array
    {
        $rules = [
            'translations' => ['array'],
            'translations.*.html_content' => ['required', 'string'],
        ];

        if (!$this->isLayout) {
            $rules['translations.*.subject'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function addLocale(string $locale): void
    {
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [
                'subject' => '',
                'preheader' => '',
                'html_content' => '',
                'text_content' => '',
            ];
        }
        $this->activeLocale = $locale;
    }

    public function removeLocale(string $locale): void
    {
        unset($this->translations[$locale]);
        if ($this->activeLocale === $locale) {
            $this->activeLocale = array_key_first($this->translations) ?? app()->getLocale();
        }
    }

    public function save(): void
    {
        $this->validate();

        // Update Metadata
        if ($this->isLayout) {
            $this->model->update([
                'name' => $this->name,
                'is_default' => $this->is_default,
            ]);
        } else {
            $this->model->update(['name' => $this->name]);
        }

        // Sync translations
        foreach ($this->translations as $locale => $data) {
            $this->model->translations()->updateOrCreate(['locale' => $locale], $data);
        }

        // Remove deleted locales
        $this->model
            ->translations()
            ->whereNotIn('locale', array_keys($this->translations))
            ->delete();

        NotificationBuilder::make()
            ->title('pages.common.edit.success', ['name' => $this->model->label()])
            ->success()
            ->persist()
            ->send();
    }
}; ?>

<section class="mx-auto w-full max-w-6xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-ui.title level="2"
                        class="mb-6">{{ $this->getPageTitle() }}</x-ui.title>

            <x-ui.form wire:submit="save"
                       class="space-y-6">

                {{-- Metadata --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input type="text"
                                wire:model="name"
                                name="name"
                                :label="__('email_templates.form.name')"
                                required></x-ui.input>

                    @if ($isLayout)
                        <div class="col-span-1 flex gap-6 md:col-span-2">
                            <x-ui.checkbox wire:model="is_default"
                                           name="is_default"
                                           :label="__('email_templates.form.is_default')"></x-ui.checkbox>
                        </div>
                    @endif
                </div>

                <div class="divider"></div>

                {{-- Translations --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('email_templates.show.translations') }}</x-ui.title>

                        {{-- Locale Adder --}}
                        @if (count($translations) < count(config('i18n.supported_locales')))
                            <div class="dropdown dropdown-end">
                                <label tabindex="0"
                                       class="btn btn-sm btn-ghost gap-2">
                                    <x-ui.icon name="plus"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.add_locale') }}
                                </label>
                                <ul tabindex="0"
                                    class="dropdown-content menu bg-base-100 rounded-box w-52 p-2 shadow">
                                    @foreach (config('i18n.supported_locales') as $locale => $data)
                                        @if (!isset($translations[$locale]))
                                            <li><a
                                                   wire:click="addLocale('{{ $locale }}')">{{ __("locales.{$locale}") }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Tabs --}}
                    <div role="tablist"
                         class="tabs tabs-bordered">
                        @foreach (array_keys($translations) as $locale)
                            <a role="tab"
                               class="tab {{ $activeLocale === $locale ? 'tab-active' : '' }}"
                               wire:click="$set('activeLocale', '{{ $locale }}')">
                                {{ __("locales.{$locale}") }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Active Tab Content --}}
                    @if (isset($translations[$activeLocale]))
                        <div class="space-y-4 pt-4">
                            <div class="flex justify-end">
                                <x-ui.button type="button"
                                             wire:click="removeLocale('{{ $activeLocale }}')"
                                             variant="ghost"
                                             color="error"
                                             size="sm"
                                             class="gap-2">
                                    <x-ui.icon name="trash"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.remove_locale', ['locale' => __("locales.{$activeLocale}")]) }}
                                </x-ui.button>
                            </div>

                            @if (!$isLayout)
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <x-ui.input type="text"
                                                wire:model="translations.{{ $activeLocale }}.subject"
                                                :label="__('email_templates.form.subject')"
                                                required></x-ui.input>
                                    <x-ui.input type="text"
                                                wire:model="translations.{{ $activeLocale }}.preheader"
                                                :label="__('email_templates.form.preheader')"></x-ui.input>
                                </div>
                            @endif

                            <div class="relative">
                                <div class="mb-2 flex items-center justify-between">
                                    <label
                                           class="label-text font-medium">{{ __('email_templates.form.html_content') }}</label>
                                    @if (!$isLayout)
                                        <x-ui.merge-tag-picker :entity-types="$entity_types"
                                                               :context-variables="$context_variables"
                                                               :target="'html_content_' . $activeLocale"></x-ui.merge-tag-picker>
                                    @endif
                                </div>
                                <x-ui.input type="textarea"
                                            wire:model="translations.{{ $activeLocale }}.html_content"
                                            rows="15"
                                            id="html_content_{{ $activeLocale }}"
                                            class="font-mono text-sm"></x-ui.input>
                            </div>

                            <div class="relative">
                                <div class="mb-2 flex items-center justify-between">
                                    <label
                                           class="label-text font-medium">{{ __('email_templates.form.text_content') }}</label>
                                    @if (!$isLayout)
                                        <x-ui.merge-tag-picker :entity-types="$entity_types"
                                                               :context-variables="$context_variables"
                                                               :target="'text_content_' . $activeLocale"></x-ui.merge-tag-picker>
                                    @endif
                                </div>
                                <x-ui.input type="textarea"
                                            wire:model="translations.{{ $activeLocale }}.text_content"
                                            rows="10"
                                            id="text_content_{{ $activeLocale }}"
                                            class="font-mono text-sm"></x-ui.input>
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center">Select or add a locale to edit content.</div>
                    @endif
                </div>

                {{-- Submit --}}
                <div class="divider"></div>
                <div class="flex justify-end gap-4">
                    <x-ui.button href="{{ route('emailTemplates.contents.index') }}"
                                 variant="ghost"
                                 wire:navigate>{{ __('actions.cancel') }}</x-ui.button>
                    <x-ui.button type="submit"
                                 color="primary">
                        <x-ui.loading wire:loading
                                      wire:target="save"
                                      size="sm"></x-ui.loading>
                        {{ __('pages.common.edit.submit') }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
