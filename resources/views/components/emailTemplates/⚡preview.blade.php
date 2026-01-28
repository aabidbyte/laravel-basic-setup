<?php

declare(strict_types=1);

use App\Models\EmailTemplate\EmailTemplate;
use App\Services\EmailTemplate\EmailRenderer;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Services\Notifications\NotificationBuilder;

new #[Lazy] class extends Component {
    #[Locked]
    public string $templateUuid = '';

    public ?int $temporaryLayoutId = null;

    public ?EmailTemplate $template = null;

    public string $selectedLocale = '';

    public string $previewHtml = '';

    public string $previewSubject = '';

    public bool $preferDraft = true;

    public function mount(string $templateUuid): void
    {
        $this->templateUuid = $templateUuid;
        $this->loadTemplate();
        $this->selectedLocale = app()->getLocale();
        $this->temporaryLayoutId = $this->template->layout_id;
        // Check if there is actual draft content to show, otherwise default to false?
        // Actually, prioritizing draft means if draft exists, show it.
        // We can check if any translation has draft content.
        $this->generatePreview();
    }

    protected function loadTemplate(): void
    {
        $this->template = EmailTemplate::query()
            ->where('uuid', $this->templateUuid)
            ->with(['translations', 'layout'])
            ->firstOrFail();
    }

    public function getLocalesProperty(): array
    {
        $locales = config('i18n.supported_locales');

        if (empty($locales) || !is_array($locales)) {
            return [app()->getLocale() => app()->getLocale()];
        }

        $options = [];
        foreach ($locales as $code => $meta) {
            $options[$code] = $meta['native_name'] ?? $code;
        }

        return $options;
    }

    public function getLayoutsProperty(): array
    {
        return EmailTemplate::query()->layouts()->published()->pluck('name', 'id')->toArray();
    }

    public function updatedTemporaryLayoutId($value): void
    {
        if ($value) {
            $this->template->layout_id = (int) $value;
            $this->template->load('layout');
        } else {
            $this->template->layout_id = null;
            $this->template->unsetRelation('layout');
        }
        $this->generatePreview();
    }

    public function saveLayout(): void
    {
        $this->template->fill(['layout_id' => $this->temporaryLayoutId])->save();

        NotificationBuilder::make()->title('actions.success')->subtitle('email_templates.messages.published')->success()->send();
    }

    public function generatePreview(): void
    {
        $renderer = app(EmailRenderer::class);

        $rendered = $renderer->preview($this->template, $this->selectedLocale, $this->preferDraft);

        $this->previewHtml = $rendered->html;
        $this->previewSubject = $rendered->subject;
    }

    public function updatedSelectedLocale(): void
    {
        $this->generatePreview();
    }

    public function updatedPreferDraft(): void
    {
        $this->generatePreview();
    }
}; ?>

<div class="space-y-4"
     x-data="{ device: 'desktop' }">
    {{-- Template Info & Controls --}}
    <div class="space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold">{{ $template->name }}</h3>
                <x-ui.badge :text="$template->type->label()"
                            color="neutral"
                            size="sm" />
                @if (!$template->is_layout)
                    <x-ui.badge :text="$template->status->label()"
                                :color="$template->status->color()"
                                size="sm" />
                @endif
            </div>

            <div class="flex items-center gap-4">
                {{-- Draft Toggle --}}
                @if ($template->status === \App\Enums\EmailTemplate\EmailTemplateStatus::DRAFT && $template->hasDraftContent())
                    <x-ui.toggle wire:model.live="preferDraft"
                                 size="sm"
                                 color="primary"
                                 :label="__('email_templates.preview.view_draft')"
                                 labelPosition="left" />
                @endif

                {{-- Device Selector --}}
                @if ($previewHtml)
                    <div class="join">
                        <x-ui.button type="button"
                                     size="sm"
                                     class="join-item"
                                     ::class="{ 'btn-active': device === 'desktop' }"
                                     @click="device = 'desktop'"
                                     variant="ghost">
                            <x-ui.icon name="computer-desktop"
                                       size="sm" />
                        </x-ui.button>
                        <x-ui.button type="button"
                                     size="sm"
                                     class="join-item"
                                     ::class="{ 'btn-active': device === 'tablet' }"
                                     @click="device = 'tablet'"
                                     variant="ghost">
                            <x-ui.icon name="device-tablet"
                                       size="sm" />
                        </x-ui.button>
                        <x-ui.button type="button"
                                     size="sm"
                                     class="join-item"
                                     ::class="{ 'btn-active': device === 'mobile' }"
                                     @click="device = 'mobile'"
                                     variant="ghost">
                            <x-ui.icon name="device-phone-mobile"
                                       size="sm" />
                        </x-ui.button>
                    </div>
                @endif

                {{-- Locale Selector --}}
                @if (count($this->locales) > 1)
                    <x-ui.select wire:model.live="selectedLocale"
                                 :options="$this->locales"
                                 :prepend-empty="false"
                                 size="sm"
                                 class="w-auto" />
                @endif
            </div>
        </div>

        @if ($previewSubject)
            <p class="text-base-content/60 text-sm">
                <span class="font-medium">{{ __('email_templates.preview.subject') }}:</span> {{ $previewSubject }}
            </p>
        @endif
    </div>
    {{-- Layout Selector --}}
    @if (!$template->is_layout)
        <div class="flex items-center gap-2">
            <x-ui.label for="temporaryLayoutId">
                {{ __('email_templates.preview.change_layout') }}
            </x-ui.label>
            <x-ui.select wire:model.live="temporaryLayoutId"
                         :options="$this->layouts"
                         placeholder="{{ __('email_templates.preview.select_layout') }}"
                         size="sm"
                         class="w-48" />

            @if ($temporaryLayoutId !== $template->getRawOriginal('layout_id'))
                <x-ui.button type="button"
                             wire:click="saveLayout"
                             size="sm"
                             color="success">
                    <x-ui.icon name="check"
                               size="sm" />
                    {{ __('email_templates.preview.apply_layout') }}
                </x-ui.button>
            @endif
        </div>
    @endif

    {{-- HTML Preview --}}
    @if ($previewHtml)
        <div class="bg-base-200 flex justify-center overflow-auto rounded-lg border p-4">
            <div :class="{
                'w-full': device === 'desktop',
                'w-[768px]': device === 'tablet',
                'w-[375px]': device === 'mobile'
            }"
                 class="border bg-white shadow-sm transition-all duration-300">
                {{-- Secure iframe: no allow-same-origin = complete isolation --}}
                <iframe srcdoc="{!! e($previewHtml) !!}"
                        class="min-h-[60vh] w-full rounded border-0"></iframe>
            </div>
        </div>
    @else
        <div class="bg-base-200 flex h-64 items-center justify-center rounded-lg border">
            <div class="text-center">
                <x-ui.loading wire:loading
                              wire:target="generatePreview"
                              size="lg"></x-ui.loading>
                <p class="text-base-content/60"
                   wire:loading.remove
                   wire:target="generatePreview">
                    {{ __('email_templates.preview.no_preview') }}
                </p>
            </div>
        </div>
    @endif
</div>
