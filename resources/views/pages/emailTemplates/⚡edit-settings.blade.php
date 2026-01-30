<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateKind;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 2;

    #[Locked]
    public bool $isLayout = false;

    public ?EmailTemplate $model = null;

    // Common fields
    public string $name = '';

    public ?string $description = null;

    // Content specific
    public EmailTemplateType $type = EmailTemplateType::TRANSACTIONAL;

    public ?int $layout_id = null;

    public array $entity_types = [];

    public array $context_variables = [];

    // Layout specific
    public bool $is_default = false;

    public function mount(?EmailTemplate $template = null): void
    {
        $this->authorizeAccess($template);
        $this->initializeUnifiedModel($template, fn($t) => $this->loadExistingTemplate($t), fn() => $this->prepareNewTemplate());

        $this->modelTypeLabel = $this->isLayout ? __('types.email_layout') : __('types.email_content');

        $this->updatePageHeader();
    }

    protected function authorizeAccess(?EmailTemplate $template): void
    {
        $permission = $template ? Permissions::EDIT_EMAIL_TEMPLATES() : Permissions::CREATE_EMAIL_TEMPLATES();

        $this->authorize($permission);
    }

    protected function loadExistingTemplate(EmailTemplate $template): void
    {
        $this->model = $template->load(['layout']);
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
            $this->pageSubtitle = __('pages.common.create.description', ['type' => $typeLabel]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => $typeLabel]) . ' - ' . __('email_templates.edit.settings');
            $this->pageSubtitle = __('email_templates.edit.settings_description');
        }
    }

    protected function fillFromModel(): void
    {
        $this->name = $this->model->name;
        $this->description = $this->model->description;

        if (!$this->isLayout) {
            $this->type = $this->model->type;
            $this->layout_id = $this->model->layout_id;
            $this->entity_types = $this->model->entity_types ?? [];
            $this->context_variables = $this->model->context_variables ?? [];
        } else {
            $this->is_default = $this->model->is_default;
        }
    }

    protected function rules(): array
    {
        $uniqueRule = $this->isCreateMode ? Rule::unique(EmailTemplate::class) : Rule::unique(EmailTemplate::class)->ignore($this->model->id);

        $rules = [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:500'],
        ];

        if (!$this->isLayout) {
            $rules['type'] = ['required', new Enum(EmailTemplateType::class)];
            $rules['layout_id'] = ['nullable', 'exists:email_templates,id'];
            $rules['entity_types'] = ['array'];
            $rules['context_variables'] = ['array'];
        } else {
            $rules['is_default'] = ['boolean'];
        }

        return $rules;
    }

    public function create(): void
    {
        $this->validate();

        $messageKey = $this->persistEmailTemplate();

        $this->handleSuccess($this->model, $messageKey);
    }

    public function save(): void
    {
        $this->validate();

        $messageKey = $this->persistEmailTemplate();

        $this->handleSuccess($this->model, $messageKey);
    }

    protected function persistEmailTemplate(): string
    {
        $data = $this->prepareData();

        if ($this->isCreateMode) {
            $this->model = EmailTemplate::create($data);
            return 'pages.common.create.success';
        }

        $this->model->update($data);
        return 'pages.common.edit.success';
    }

    protected function handleSuccess(EmailTemplate $template, string $messageKey): void
    {
        $this->sendSuccessNotification($template, $messageKey);

        if ($this->isCreateMode) {
            $this->redirect(route('emailTemplates.builder.edit', $template), navigate: true);
            return;
        }

        $this->redirect(route('emailTemplates.show', $template), navigate: true);
    }

    protected function prepareData(): array
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->isLayout) {
            $data['is_layout'] = true;
            $data['is_default'] = $this->is_default;
            $data['status'] = EmailTemplateStatus::PUBLISHED;
        } else {
            $data['is_layout'] = false;
            $data['type'] = $this->type;
            $data['layout_id'] = $this->layout_id;
            $data['entity_types'] = $this->entity_types;
            $data['context_variables'] = $this->context_variables;
        }

        return $data;
    }

    public function getCancelUrlProperty(): string
    {
        if (!$this->isCreateMode) {
            return route('emailTemplates.show', $this->model);
        }

        return $this->isLayout ? route('emailTemplates.layouts.index') : route('emailTemplates.contents.index');
    }

    public function getAvailableLayoutsProperty(): array
    {
        $query = EmailTemplate::query()->where('is_layout', true)->orderBy('name');

        if (!$this->isCreateMode && $this->layout_id) {
            $query->where(function ($q) {
                $q->where('is_default', false)->orWhere('id', $this->layout_id);
            });
        }

        return ['' => __('common.select')] + $query->get()->mapWithKeys(fn($l) => [$l->id => $l->name])->toArray();
    }

    public function getTypeOptionsProperty(): array
    {
        return [
            EmailTemplateType::TRANSACTIONAL->value => __('email_templates.types.transactional'),
            EmailTemplateType::MARKETING->value => __('email_templates.types.marketing'),
            EmailTemplateType::SYSTEM->value => __('email_templates.types.system'),
        ];
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        @if (!$isCreateMode && !$isLayout)
            @can(Permissions::EDIT_BUILDER_EMAIL_TEMPLATES())
                <x-ui.button href="{{ route('emailTemplates.builder.edit', $model) }}"
                             variant="outline"
                             wire:navigate>
                    {{ __('email_templates.edit.edit_builder') }}
                </x-ui.button>
            @endcan
        @endif

        <x-ui.button type="submit"
                     form="settings-form"
                     color="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm"></x-ui.loading>
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-4xl">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.form wire:submit="{{ $this->submitAction }}"
                           id="settings-form"
                           class="space-y-6">
                    {{-- Basic Settings --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">
                            {{ __('email_templates.form.basic_info') }}
                        </x-ui.title>

                        <div class="grid grid-cols-1 gap-4">
                            <x-ui.input type="text"
                                        wire:model="name"
                                        name="name"
                                        :label="__('email_templates.form.name')"
                                        required
                                        autofocus></x-ui.input>

                            <x-ui.input type="textarea"
                                        wire:model="description"
                                        name="description"
                                        :label="__('fields.description')"
                                        rows="3"></x-ui.input>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Settings --}}
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">
                            {{ __('email_templates.form.settings') }}
                        </x-ui.title>

                        @if ($isLayout)
                            <div class="flex gap-6">
                                <x-ui.checkbox wire:model="is_default"
                                               name="is_default"
                                               :label="__('email_templates.form.is_default')"></x-ui.checkbox>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <x-ui.select wire:model="type"
                                             name="type"
                                             :label="__('email_templates.form.type')"
                                             :options="$this->typeOptions"
                                             :prepend-empty="false"></x-ui.select>

                                <x-ui.select wire:model="layout_id"
                                             name="layout_id"
                                             :label="__('email_templates.form.layout')"
                                             :options="$this->availableLayouts"
                                             :prepend-empty="false"></x-ui.select>
                            </div>
                        @endif
                    </div>
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>
