<?php

declare(strict_types=1);

use App\Models\EmailTemplate\EmailTemplate;
use App\Models\EmailTemplate\EmailTranslation;
use App\Services\EmailTemplate\EmailRenderer;
use App\Services\Notifications\NotificationBuilder;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public string $templateUuid = '';

    public ?EmailTemplate $template = null;

    public string $selectedLocale = '';

    public string $previewHtml = '';

    public string $previewSubject = '';

    public bool $showModal = false;

    /**
     * Mount the component.
     */
    public function mount(string $templateUuid): void
    {
        $this->templateUuid = $templateUuid;
        $this->template = EmailTemplate::where('uuid', $templateUuid)
            ->with(['translations', 'layout'])
            ->firstOrFail();

        // Default to first available locale
        $firstTranslation = $this->template->translations->first();
        $this->selectedLocale = $firstTranslation?->locale ?? app()->getLocale();
    }

    /**
     * Get available locales for this template.
     *
     * @return array<string, string>
     */
    public function getLocalesProperty(): array
    {
        $locales = [];
        foreach ($this->template->translations as $translation) {
            $locales[$translation->locale] = __("locales.{$translation->locale}");
        }

        return $locales;
    }

    /**
     * Generate preview for selected locale.
     */
    public function generatePreview(): void
    {
        try {
            $renderer = app(EmailRenderer::class);

            // Find specific translation
            $translation = $this->template->translations->where('locale', $this->selectedLocale)->first();

            // Prevent lazy loading violation
            $translation?->setRelation('template', $this->template);

            if (!$translation) {
                // Fallback or error
                throw new \Exception("Translation for locale '{$this->selectedLocale}' not found.");
            }

            // Split sample data into entities and context
            $sampleData = $this->getSampleData();
            $entities = [];
            $context = [];

            foreach ($sampleData as $key => $value) {
                if (str_starts_with($key, 'context.')) {
                    $context[str_replace('context.', '', $key)] = $value;
                } else {
                    $entities[$key] = $value;
                }
            }

            $rendered = $renderer->previewTranslation($translation, $entities, $context);

            $this->previewHtml = $rendered->html;
            $this->previewSubject = $rendered->subject;
            $this->showModal = true;
        } catch (\Throwable $e) {
            NotificationBuilder::make()->title(__('email_templates.preview.error'))->content($e->getMessage())->error()->send();
        }
    }

    /**
     * Get sample data for preview.
     *
     * @return array<string, mixed>
     */
    protected function getSampleData(): array
    {
        // Create sample data based on entity types
        $data = [];

        if (in_array('user', $this->template->entity_types ?? [], true)) {
            $data['user'] = auth()->user() ?? $this->createSampleUser();
        }

        // Add sample context variables
        foreach ($this->template->context_variables ?? [] as $variable) {
            $data["context.{$variable}"] = $this->getSampleContextValue($variable);
        }

        return $data;
    }

    /**
     * Create sample user data.
     *
     * @return array<string, mixed>
     */
    protected function createSampleUser(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'username' => 'johndoe',
        ];
    }

    /**
     * Get sample value for a context variable.
     */
    protected function getSampleContextValue(string $variable): string
    {
        return match (true) {
            str_contains($variable, 'url') => 'https://example.com/action',
            str_contains($variable, 'link') => 'https://example.com/link',
            str_contains($variable, 'token') => 'sample-token-12345',
            str_contains($variable, 'code') => '123456',
            default => 'Sample Value',
        };
    }

    /**
     * Close the preview modal.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->previewHtml = '';
        $this->previewSubject = '';
    }

    public function updatedSelectedLocale(): void
    {
        if ($this->showModal) {
            $this->generatePreview();
        }
    }
}; ?>

<div>
    {{-- Preview Button --}}
    <x-ui.button type="button"
                 wire:click="generatePreview"
                 wire:loading.attr="disabled"
                 variant="ghost"
                 class="gap-2">
        <x-ui.loading wire:loading
                      wire:target="generatePreview"
                      size="sm"></x-ui.loading>
        <x-ui.icon wire:loading.remove
                   wire:target="generatePreview"
                   name="eye"
                   size="sm"></x-ui.icon>
        {{ __('email_templates.preview.button') }}
    </x-ui.button>

    {{-- Preview Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @keydown.escape.window="$wire.closeModal()">
            <div class="bg-base-100 relative max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-xl shadow-2xl">
                {{-- Modal Header --}}
                <div class="border-base-300 flex items-center justify-between border-b px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ __('email_templates.preview.title') }}</h3>
                        <p class="text-base-content/60 text-sm">
                            {{ __('email_templates.preview.subject') }}: {{ $previewSubject }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (count($this->locales) > 1)
                            <x-ui.select wire:model.live="selectedLocale"
                                         size="sm"
                                         class="w-auto">
                                @foreach ($this->locales as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                        <x-ui.button type="button"
                                     wire:click="closeModal"
                                     variant="ghost"
                                     size="sm">
                            <x-ui.icon name="x-mark"
                                       size="sm"></x-ui.icon>
                        </x-ui.button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="max-h-[calc(90vh-100px)] overflow-y-auto p-6">
                        <div class="bg-base-200 rounded-lg border p-4">
                            <iframe srcdoc="{!! e($previewHtml) !!}"
                                    class="h-[500px] w-full rounded border-0 bg-white"
                                    sandbox="allow-same-origin"></iframe>
                        </div>
                    </div>
                </div>
            </div>
    @endif
</div>
