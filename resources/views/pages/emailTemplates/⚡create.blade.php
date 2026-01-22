<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    // Toggle
    public bool $isLayout = false;

    // Common Fields
    public string $name = '';

    public ?string $description = null;

    // Content Specific
    public EmailTemplateType $type = EmailTemplateType::TRANSACTIONAL;

    public EmailTemplateStatus $status = EmailTemplateStatus::DRAFT;

    public ?string $layout_id = null;

    /** @var array<string> */
    public array $entity_types = [];

    /** @var array<string> */
    public array $context_variables = [];

    // Layout Specific
    public bool $is_default = false;

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::CREATE_EMAIL_TEMPLATES);

        if (request()->query('type') === 'layout') {
            $this->isLayout = true;
        }

        $this->updatePageHeader();
    }

    public function updatedIsLayout(): void
    {
        $this->updatePageHeader();
    }

    protected function updatePageHeader(): void
    {
        $this->pageTitle = __('pages.common.create.title', ['type' => $this->isLayout ? __('types.email_layout') : __('types.email_content')]);
        $this->pageSubtitle = __('pages.common.create.description', ['type' => $this->isLayout ? __('types.email_layout') : __('types.email_content')]);
    }

    /**
     * Get available layouts for selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, EmailTemplate>
     */
    public function getLayoutsProperty()
    {
        return EmailTemplate::query()->layouts()->published()->orderBy('name')->get();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $baseRules = [
            'name' => ['required', 'string', 'max:255', Rule::unique(EmailTemplate::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->isLayout) {
            return array_merge($baseRules, [
                'is_default' => ['boolean'],
            ]);
        }

        return array_merge($baseRules, [
            'type' => ['required', new Enum(EmailTemplateType::class)],
            'status' => ['required', new Enum(EmailTemplateStatus::class)],
            'layout_id' => ['nullable', 'exists:email_templates,uuid'],
        ]);
    }

    /**
     * Create the entity.
     */
    public function create(): void
    {
        $this->validate();

        if ($this->isLayout) {
            $this->createLayout();
        } else {
            $this->createContent();
        }
    }

    protected function createLayout(): void
    {
        $template = EmailTemplate::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_layout' => true,
            'is_default' => $this->is_default,
            'status' => EmailTemplateStatus::PUBLISHED,
        ]);

        NotificationBuilder::make()
            ->title('pages.common.create.success', ['name' => $template->name])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('emailTemplates.edit', ['id' => $template->id]), navigate: true);
    }

    protected function createContent(): void
    {
        $layoutId = $this->resolveLayoutId();

        $template = EmailTemplate::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_layout' => false,
            'layout_id' => $layoutId,
            'type' => $this->type,
            'status' => $this->status,
            'entity_types' => $this->entity_types,
            'context_variables' => $this->context_variables,
        ]);

        NotificationBuilder::make()
            ->title('pages.common.create.success', ['name' => $template->name])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('emailTemplates.edit', ['id' => $template->id]), navigate: true);
    }

    /**
     * Resolve layout ID from UUID.
     */
    protected function resolveLayoutId(): ?int
    {
        if (empty($this->layout_id)) {
            return null;
        }

        return EmailTemplate::where('uuid', $this->layout_id)->value('id');
    }
}; ?>

<section class="mx-auto w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="mb-6 flex items-center justify-between">
                <x-ui.title level="2">{{ $this->getPageTitle() }}</x-ui.title>

                <div class="flex items-center gap-2">
                    <span
                          class="{{ !$isLayout ? 'text-primary' : 'text-base-content/70' }} text-sm font-medium">Content</span>
                    <input type="checkbox"
                           class="toggle toggle-primary"
                           wire:model.live="isLayout" />
                    <span
                          class="{{ $isLayout ? 'text-primary' : 'text-base-content/70' }} text-sm font-medium">Layout</span>
                </div>
            </div>

            <x-ui.form wire:submit="create"
                       class="space-y-6">
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('email_templates.form.basic_info') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-ui.input type="text"
                                    wire:model="name"
                                    name="name"
                                    :label="$isLayout
                                        ? __('email_templates.form.name')
                                        : __('email_templates.form.name')"
                                    required
                                    autofocus></x-ui.input>
                    </div>

                    <x-ui.input type="textarea"
                                wire:model="description"
                                name="description"
                                :label="__('email_templates.form.description')"
                                rows="3"></x-ui.input>
                </div>

                <div class="divider"></div>

                {{-- Settings --}}
                <div class="space-y-4">
                    <x-ui.title level="3"
                                class="text-base-content/70">{{ __('email_templates.form.settings') }}</x-ui.title>

                    @if ($isLayout)
                        <div class="flex gap-6">
                            <x-ui.checkbox wire:model="is_default"
                                           name="is_default"
                                           :label="__('email_templates.form.is_default')"></x-ui.checkbox>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <x-ui.select wire:model="type"
                                         name="type"
                                         :label="__('email_templates.form.type')">
                                <option value="{{ EmailTemplateType::TRANSACTIONAL->value }}">
                                    {{ __('email_templates.types.transactional') }}</option>
                                <option value="{{ EmailTemplateType::MARKETING->value }}">
                                    {{ __('email_templates.types.marketing') }}</option>
                                <option value="{{ EmailTemplateType::SYSTEM->value }}">
                                    {{ __('email_templates.types.system') }}</option>
                            </x-ui.select>

                            <x-ui.select wire:model="status"
                                         name="status"
                                         :label="__('email_templates.form.status')">
                                <option value="{{ EmailTemplateStatus::DRAFT->value }}">
                                    {{ __('email_templates.status.draft') }}</option>
                                <option value="{{ EmailTemplateStatus::PUBLISHED->value }}">
                                    {{ __('email_templates.status.published') }}</option>
                                <option value="{{ EmailTemplateStatus::ARCHIVED->value }}">
                                    {{ __('email_templates.status.archived') }}</option>
                            </x-ui.select>

                            <x-ui.select wire:model="layout_id"
                                         name="layout_id"
                                         :label="__('email_templates.form.layout')">
                                <option value="">{{ __('common.none') }}</option>
                                @foreach ($this->layouts as $layout)
                                    <option value="{{ $layout->uuid }}">{{ $layout->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
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
                                      wire:target="create"
                                      size="sm"></x-ui.loading>
                        {{ __('pages.common.create.submit', ['type' => $isLayout ? __('types.email_layout') : __('types.email_content')]) }}
                    </x-ui.button>
                </div>
            </x-ui.form>
        </div>
    </div>
</section>
