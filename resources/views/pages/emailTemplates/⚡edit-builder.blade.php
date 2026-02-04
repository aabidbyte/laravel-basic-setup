<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateKind;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\Notifications\NotificationBuilder;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

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

        $this->modelTypeLabel = $this->isLayout ? __('types.email_layout') : __('types.email_content');

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

        // Use helper to get content, prioritizing draft for the builder
        foreach ($this->model->getAvailableLocales() as $locale) {
            $content = $this->model->getContent($locale, preferDraft: true);
            $this->translations[$locale] = $content;
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

    // REMOVED: updated() and convertHtmlToText() - handled by EmailTemplateSaved event and listener

    public function saveAsDraft(): void
    {
        $this->authorize(Permissions::EDIT_BUILDER_EMAIL_TEMPLATES());
        $this->validate();

        // Ensure model exists before saving draft translations
        if (!$this->model->exists) {
            $this->persistEmailTemplate(EmailTemplateStatus::DRAFT);
        }

        $service = resolve(\App\Services\EmailTemplate\EmailTemplateService::class);
        $service->saveDraft($this->model, $this->translations);

        $this->handleSuccess($this->model, 'email_templates.messages.draft_saved');
    }

    public function publish(): void
    {
        $this->authorize(Permissions::PUBLISH_EMAIL_TEMPLATES());
        $this->validate();

        // Ensure model exists
        if (!$this->model->exists) {
            $this->persistEmailTemplate(EmailTemplateStatus::DRAFT);
        }

        $service = resolve(\App\Services\EmailTemplate\EmailTemplateService::class);

        // Save draft first to ensure latest content is used
        $service->saveDraft($this->model, $this->translations);
        $service->publish($this->model);

        $this->handleSuccess($this->model, 'email_templates.messages.published');
    }

    public function restoreToDraft(): void
    {
        $this->authorize(Permissions::EDIT_BUILDER_EMAIL_TEMPLATES());

        $service = resolve(\App\Services\EmailTemplate\EmailTemplateService::class);
        $service->restoreToDraft($this->model);

        // Reload model and form
        $this->loadExistingTemplate($this->model->refresh());

        NotificationBuilder::make()->title('actions.success')->subtitle('email_templates.messages.restored_from_published')->success()->send();
    }

    public function save(): void
    {
        // Default action for generic save button (if used)
        $this->saveAsDraft();
    }

    protected function saveWithStatus(EmailTemplateStatus $status): void
    {
        // Legacy method kept for Layouts or simple saves
        $this->validate();

        $messageKey = $this->persistEmailTemplate($status);
        $this->persistTranslations($this->model);

        if (!$this->isCreateMode) {
            $this->cleanupOrphanedTranslations($this->model);
        }

        $this->handleSuccess($this->model, $messageKey);
    }

    protected function persistEmailTemplate(EmailTemplateStatus $status): string
    {
        $modelData = $this->prepareModelData();
        $modelData['status'] = $status;

        if ($this->isCreateMode) {
            $this->model = EmailTemplate::create($modelData);

            return 'pages.common.create.success';
        }

        $this->model->fill($modelData)->save();

        return 'pages.common.edit.success';
    }

    protected function persistTranslations(EmailTemplate $template): void
    {
        foreach ($this->translations as $locale => $data) {
            $template->translations()->updateOrCreate(['locale' => $locale], $data);
        }
    }

    protected function cleanupOrphanedTranslations(EmailTemplate $template): void
    {
        $template
            ->translations()
            ->whereNotIn('locale', \array_keys($this->translations))
            ->delete();
    }

    protected function handleSuccess(EmailTemplate $template, string $messageKey): void
    {
        $this->sendSuccessNotification($template, $messageKey);
        // Stay on page for builder to allow continuous editing
        // $this->redirect(route('emailTemplates.show', $template), navigate: true);
    }

    protected function prepareModelData(): array
    {
        return [
            'is_layout' => $this->isLayout,
        ];
    }

    public function getShowSaveActionsProperty(): bool
    {
        return true;
    }

    public function getCanRestoreProperty(): bool
    {
        // Can restore (discard draft) if:
        // 1. Not in create mode
        // 2. Status is DRAFT
        // 3. Has draft content to discard
        // 4. Has published content to restore FROM (safety check)
        return !$this->isCreateMode && $this->model->status === EmailTemplateStatus::DRAFT && $this->model->hasDraftContent() && $this->model->hasPublishedContent();
    }

    public function getSupportedLocalesProperty(): array
    {
        return resolve(\App\Services\I18nService::class)->getSupportedLocales();
    }

    public function getCancelUrlProperty(): string
    {
        if (!$this->isCreateMode) {
            return route('emailTemplates.show', $this->model);
        }

        return $this->isLayout ? route('emailTemplates.layouts.index') : route('emailTemplates.contents.index');
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <div class="flex items-center gap-2">
            @if ($this->showSaveActions)
                <!-- Restore Action -->
                @if ($this->canRestore)
                    <x-ui.button type="button"
                                 wire:click="restoreToDraft"
                                 wire:confirm="{{ __('email_templates.actions.confirm_restore') }}"
                                 variant="ghost"
                                 class="text-error">
                        {{ __('email_templates.actions.restore_from_published') }}
                    </x-ui.button>
                @endif

                <x-ui.button type="button"
                             wire:click="saveAsDraft"
                             wire:loading.attr="disabled"
                             variant="secondary">
                    <x-ui.loading wire:loading
                                  wire:target="saveAsDraft"
                                  size="sm"></x-ui.loading>
                    {{ __('email_templates.actions.save_draft') }}
                </x-ui.button>

                <x-ui.button type="button"
                             wire:click="publish"
                             wire:loading.attr="disabled"
                             color="primary">
                    <x-ui.loading wire:loading
                                  wire:target="publish"
                                  size="sm"></x-ui.loading>
                    {{ __('email_templates.actions.publish') }}
                </x-ui.button>
            @else
                <x-ui.button type="submit"
                             form="builder-form"
                             color="primary">
                    <x-ui.loading wire:loading
                                  wire:target="save"
                                  size="sm"></x-ui.loading>
                    {{ $this->submitButtonText }}
                </x-ui.button>
            @endif
        </div>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-6xl p-2">
        <x-ui.form wire:submit="save"
                   id="builder-form"
                   class="space-y-6">

            {{-- Translations --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">

                    {{-- Tabs --}}
                    <div role="tablist"
                         class="tabs tabs-lift">
                        @foreach (array_keys($translations) as $locale)
                            <a role="tab"
                               class="tab {{ $activeLocale === $locale ? 'tab-active' : '' }} group"
                               wire:click="$set('activeLocale', '{{ $locale }}')">
                                {{ __("locales.{$locale}") }}
                                <x-ui.icon name="trash"
                                           size="xs"
                                           wire:click="removeLocale('{{ $locale }}')"
                                           @class([
                                               'ml-2 cursor-pointer ',
                                               $activeLocale === $locale ? 'text-error' : 'disabled',
                                           ])></x-ui.icon>
                            </a>
                        @endforeach
                    </div>
                    @if (count($translations) < count($this->supportedLocales))
                        <x-ui.dropdown placement="end"
                                       menu="true"
                                       contentClass="w-52">
                            <x-slot:trigger>
                                <x-ui.button type="button"
                                             variant="ghost"
                                             size="sm"
                                             class="gap-2"
                                             tabindex="0">
                                    <x-ui.icon name="plus"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.add_locale') }}
                                </x-ui.button>
                            </x-slot:trigger>

                            @foreach ($this->supportedLocales as $locale => $data)
                                @if (!isset($translations[$locale]))
                                    <li>
                                        <a wire:click="addLocale('{{ $locale }}')">
                                            {{ __("locales.{$locale}") }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </x-ui.dropdown>
                    @endif
                </div>
                <div class="card shadow">
                    <div class="card-body">
                        {{-- Active Tab Content --}}
                        @if (isset($translations[(string) $activeLocale]))
                            <div class="space-y-4">
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
                                        <x-ui.label for="html_content_{{ $activeLocale }}"
                                                    :text="__('email_templates.form.html_content')"
                                                    required></x-ui.label>
                                        @if (!$isLayout)
                                            <x-ui.merge-tag-picker :entity-types="$entity_types"
                                                                   :context-variables="$context_variables"
                                                                   target="{{ 'html_content_' . $activeLocale }}"></x-ui.merge-tag-picker>
                                        @endif
                                    </div>
                                    <x-ui.grape-editor wire:model="translations.{{ $activeLocale }}.html_content"
                                                       id="html_content_{{ $activeLocale }}"
                                                       lang="{{ str_replace('_', '-', $activeLocale) }}"
                                                       dir="{{ $this->supportedLocales[$activeLocale]['direction'] ?? 'ltr' }}"></x-ui.grape-editor>
                                </div>

                                @if (!$isLayout)
                                    <div class="relative">
                                        <div class="flex flex-col gap-4">
                                            <x-ui.label for="text_content_{{ $activeLocale }}"
                                                        :text="__('email_templates.form.text_content')"></x-ui.label>
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
                                @endif

                            </div>
                        @else
                            <div class="py-4 text-center">Select or add a locale to edit content.</div>
                        @endif
                    </div>
                </div>

            </div>
        </x-ui.form>
    </section>
</x-layouts.page>
