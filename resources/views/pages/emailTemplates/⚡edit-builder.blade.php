<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateKind;
use App\Livewire\Bases\BasePageComponent;
use Illuminate\Support\Str;
use App\Models\EmailTemplate\EmailTemplate;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    // Layout Specific
    public bool $isLayout = false;

    public bool $showTextContent = false;

    public ?EmailTemplate $model = null;

    // Content Specific
    public array $entity_types = [];

    public array $context_variables = [];

    // Translations
    public array $translations = [];

    public string $activeLocale = 'en_US';

    public function mount(?EmailTemplate $template = null): void
    {
        $this->authorizeAccess($template);
        $this->initializeUnifiedModel($template, fn($t) => $this->loadExistingTemplate($t), fn() => $this->prepareNewTemplate());
        $this->setupTranslations();
        $this->updatePageHeader();
    }

    protected function authorizeAccess(?EmailTemplate $template): void
    {
        $permission = $template ? Permissions::EDIT_BUILDER_EMAIL_TEMPLATES() : Permissions::CREATE_EMAIL_TEMPLATES();

        $this->authorize($permission);
    }

    protected function loadExistingTemplate(EmailTemplate $template): void
    {
        $this->model = $template->load(['layout', 'translations']);
        $this->isLayout = $template->is_layout;
        $this->fillFromModel();
    }

    protected function prepareNewTemplate(): void
    {
        $this->isLayout = request()->query('type') === EmailTemplateKind::LAYOUT->value;
        $this->model = new EmailTemplate();
    }

    protected function updatePageHeader(): void
    {
        $typeLabel = $this->isLayout ? __('types.email_layout') : __('types.email_content');

        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => $typeLabel]);
            $this->pageSubtitle = __('email_templates.edit.content_description');
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => $typeLabel]);
            $this->pageSubtitle = __('email_templates.edit.content_description');
        }
    }

    protected function fillFromModel(): void
    {
        if (!$this->isLayout) {
            $this->entity_types = $this->model->entity_types ?? [];
            $this->context_variables = $this->model->context_variables ?? [];
        }

        foreach ($this->model->translations as $translation) {
            $this->translations[$translation->locale] = [
                'subject' => $translation->subject,
                'preheader' => $translation->preheader,
                'html_content' => $translation->html_content,
                'text_content' => $translation->text_content,
            ];
        }
    }

    protected function setupTranslations(): void
    {
        if (empty($this->translations)) {
            $this->addLocale(app()->getLocale());
        }

        $this->activeLocale = app()->getLocale();
        if (!isset($this->translations[$this->activeLocale])) {
            $this->activeLocale = array_key_first($this->translations) ?? 'en_US';
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

    public function updated(string $property, mixed $value): void
    {
        // Auto-generate text content when HTML content changes
        if (Str::startsWith($property, 'translations.') && Str::endsWith($property, '.html_content')) {
            $locale = explode('.', $property)[1];
            $this->translations[$locale]['text_content'] = $this->convertHtmlToText($value);
        }
    }

    protected function convertHtmlToText(string $html): string
    {
        return Str::of($html)
            // 1. Replace links with "text (url)" format
            ->replaceMatches('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/i', '$2 ($1)')
            // 2. Replace structural tags with newlines
            ->replaceMatches('/<br[^>]*>/i', "\n")
            ->replaceMatches('/<\/p>/i', "\n\n")
            ->replaceMatches('/<\/div>/i', "\n")
            ->replaceMatches('/<\/tr>/i', "\n")
            // 3. Strip all other tags
            ->stripTags()
            // 4. Decode HTML entities
            ->pipe(fn($s) => html_entity_decode((string) $s))
            // 5. Clean up excessive whitespace
            ->replaceMatches('/^[ \t]+|[ \t]+$/m', '') // Trim lines
            ->replaceMatches('/\n{3,}/', "\n\n") // Max 2 newlines
            ->trim()
            ->toString();
    }

    public function create(): void
    {
        $this->validate();

        $modelData = $this->prepareModelData();
        $template = EmailTemplate::create($modelData);

        $this->syncTranslations($template);

        $this->sendSuccessNotification($template, 'pages.common.create.success');
        $this->redirect(route('emailTemplates.show', $template), navigate: true);
    }

    public function save(): void
    {
        $this->validate();

        $modelData = $this->prepareModelData();
        $this->model->update($modelData);

        $this->syncTranslations($this->model);
        $this->cleanupTranslations($this->model);

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect(route('emailTemplates.show', $this->model), navigate: true);
    }

    protected function prepareModelData(): array
    {
        $data = [];

        if ($this->isLayout) {
            $data['is_layout'] = true;
        } else {
            $data['is_layout'] = false;
        }

        return $data;
    }

    protected function syncTranslations(EmailTemplate $template): void
    {
        foreach ($this->translations as $locale => $data) {
            $template->translations()->updateOrCreate(['locale' => $locale], $data);
        }
    }

    protected function cleanupTranslations(EmailTemplate $template): void
    {
        $template
            ->translations()
            ->whereNotIn('locale', array_keys($this->translations))
            ->delete();
    }

    public function getCancelUrlProperty(): string
    {
        return $this->isCreateMode ? route('emailTemplates.index') : route('emailTemplates.show', $this->model);
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="builder-form"
                     color="primary">
            <x-ui.loading wire:loading
                          wire:target="save"
                          size="sm"></x-ui.loading>
            {{ __('pages.common.edit.submit') }}
        </x-ui.button>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-6xl">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.form wire:submit="save"
                           id="builder-form"
                           class="space-y-6">

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
                             class="tabs tabs-lift">
                            @foreach (array_keys($translations) as $locale)
                                <a role="tab"
                                   class="tab group {{ $activeLocale === $locale ? 'tab-active' : '' }}"
                                   wire:click="$set('activeLocale', '{{ $locale }}')">
                                    {{ __("locales.{$locale}") }}
                                    <x-ui.icon name="trash"
                                               size="xs"
                                               wire:click="removeLocale('{{ $locale }}')"
                                               @class([
                                                "ml-2 cursor-pointer ",
                                                   $activeLocale === $locale ? 'text-error' : 'disabled',
                                               ])
                                               ></x-ui.icon>
                                </a>
                            @endforeach
                        </div>

                        {{-- Active Tab Content --}}
                        @if (isset($translations[$activeLocale]))
                            <div class="space-y-4 pt-4">
                                @if (!$isLayout)
                                    <div class="grid grid-cols-1 gap-4">
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
                                    <div class="flex flex-col gap-4">
                                        <label
                                               class="label-text font-medium">{{ __('email_templates.form.text_content') }}</label>
                                        <div class="alert alert-info mb-2 py-2 text-xs">
                                            <x-ui.icon name="information-circle"
                                                       size="xs"></x-ui.icon>
                                            <span>{{ __('email_templates.form.text_content_auto_generated') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <x-ui.toggle wire:model.live="showTextContent"
                                                         color="primary"
                                                         size="sm"
                                                         :label="$showTextContent
                                                             ? __('actions.hide_text_version')
                                                             : __('actions.customize_text_version')"></x-ui.toggle>
                                            @if (!$isLayout && $showTextContent)
                                                <x-ui.merge-tag-picker :entity-types="$entity_types"
                                                                       :context-variables="$context_variables"
                                                                       :target="'text_content_' . $activeLocale"></x-ui.merge-tag-picker>
                                            @endif
                                        </div>

                                    </div>

                                    @if ($showTextContent)
                                        <x-ui.input type="textarea"
                                                    wire:model="translations.{{ $activeLocale }}.text_content"
                                                    rows="10"
                                                    id="text_content_{{ $activeLocale }}"
                                                    class="font-mono text-sm"></x-ui.input>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="py-4 text-center">Select or add a locale to edit content.</div>
                        @endif
                    </div>
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>
