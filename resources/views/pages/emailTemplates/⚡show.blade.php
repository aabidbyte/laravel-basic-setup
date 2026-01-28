<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;

new class extends BasePageComponent {
    public ?string $pageSubtitle = null;

    protected string $placeholderType = 'card';

    protected int $placeholderRows = 2;

    public ?EmailTemplate $template = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(EmailTemplate $template): void
    {
        $this->authorize(Permissions::VIEW_EMAIL_TEMPLATES());

        $this->template = $template->load(['layout', 'translations']);
        $this->pageSubtitle = $template->is_layout ? __('types.email_layout') : __('types.email_content');
    }

    public function getPageTitle(): string
    {
        return $this->template?->name ?? __('types.email_template');
    }

    public function archive(): void
    {
        $this->authorize(Permissions::EDIT_EMAIL_TEMPLATES());

        if ($this->template->is_system || $this->template->is_default) {
            return;
        }

        $this->template->update(['status' => EmailTemplateStatus::ARCHIVED]);
        $this->sendSuccessNotification($this->template, 'email_templates.actions.archived_success');
    }

    public function publish(): void
    {
        $this->authorize(Permissions::EDIT_EMAIL_TEMPLATES());

        if ($this->template->is_system || $this->template->is_default) {
            return;
        }

        $this->template->update(['status' => EmailTemplateStatus::PUBLISHED]);
        $this->sendSuccessNotification($this->template, 'email_templates.actions.published_success');
    }
}; ?>

<x-layouts.page
                backHref="{{ $template?->is_layout ? route('emailTemplates.layouts.index') : route('emailTemplates.contents.index') }}">
    <x-slot:topActions>
        <div class="flex gap-2"
             x-data="{ showPreview: false }">
            {{-- Preview Button --}}
            <x-ui.button type="button"
                         @click="showPreview = true"
                         variant="ghost"
                         class="gap-2">
                <x-ui.icon name="eye"
                           size="sm"></x-ui.icon>
                {{ __('email_templates.preview.button') }}
            </x-ui.button>

            {{-- Preview Modal Component --}}
            <x-ui.base-modal open-state="showPreview"
                             :use-parent-state="true"
                             max-width="7xl">
                <x-slot:title>{{ __('email_templates.preview.title') }}</x-slot:title>

                <livewire:emailTemplates.preview :template-uuid="$template->uuid"
                                                 lazy />

                <x-slot:actions>
                    <x-ui.button type="button"
                                 @click="showPreview = false"
                                 variant="ghost">
                        {{ __('actions.close') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.base-modal>

            @can(Permissions::EDIT_BUILDER_EMAIL_TEMPLATES())
                <x-ui.button href="{{ route('emailTemplates.builder.edit', $template) }}"
                             variant="outline"
                             wire:navigate
                             class="gap-2">
                    <x-ui.icon name="document-text"
                               size="sm"></x-ui.icon>
                    {{ __('email_templates.edit.edit_builder') }}
                </x-ui.button>
            @endcan

            @can(Permissions::EDIT_EMAIL_TEMPLATES())
                <x-ui.button href="{{ route('emailTemplates.settings.edit', $template) }}"
                             color="primary"
                             wire:navigate
                             class="gap-2">
                    <x-ui.icon name="cog-6-tooth"
                               size="sm"></x-ui.icon>
                    {{ __('email_templates.edit.edit_settings') }}
                </x-ui.button>
            @endcan

            @if (!$template->is_layout && !$template->is_system)
                @if ($template->status === EmailTemplateStatus::DRAFT)
                    @can(Permissions::EDIT_EMAIL_TEMPLATES())
                        <x-ui.button type="button"
                                     wire:click="publish"
                                     wire:confirm="{{ __('actions.confirm_publish') }}"
                                     color="success"
                                     class="gap-2">
                            <x-ui.icon name="check-circle"
                                       size="sm"></x-ui.icon>
                            {{ __('email_templates.actions.publish') }}
                        </x-ui.button>
                    @endcan
                @endif

                @if ($template->status !== EmailTemplateStatus::ARCHIVED)
                    @can(Permissions::EDIT_EMAIL_TEMPLATES())
                        <x-ui.button type="button"
                                     wire:click="archive"
                                     wire:confirm="{{ __('actions.confirm_archive') }}"
                                     variant="ghost"
                                     class="text-error gap-2">
                            <x-ui.icon name="archive-box"
                                       size="sm"></x-ui.icon>
                            {{ __('email_templates.actions.archive') }}
                        </x-ui.button>
                    @endcan
                @endif
            @endif
        </div>
    </x-slot:topActions>

    <section class="mx-auto w-full max-w-6xl space-y-6">
        @if ($template)
            {{-- Info Card --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <x-ui.title level="3"
                                class="mb-4">{{ __('common.details') }}</x-ui.title>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <span class="text-base-content/70 block text-sm font-medium">{{ __('fields.name') }}</span>
                            <span class="block text-lg">{{ $template->name }}</span>
                        </div>

                        <div>
                            <span class="text-base-content/70 block text-sm font-medium">{{ __('fields.type') }}</span>
                            <x-ui.badge :text="$template->type->label()"
                                        color="neutral"
                                        size="sm"></x-ui.badge>
                        </div>

                        @if (!$template->is_layout)
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('email_templates.form.layout') }}</span>
                                <span class="block">{{ $template->layout->name ?? __('common.none') }}</span>
                            </div>
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('fields.status') }}</span>
                                <x-ui.badge :text="$template->status->label()"
                                            :color="$template->status->color()"
                                            size="sm"></x-ui.badge>
                            </div>
                        @else
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('email_templates.form.is_default') }}</span>
                                <x-ui.badge :text="$template->is_default ? __('common.yes') : __('common.no')"
                                            :color="$template->is_default ? 'success' : 'neutral'"
                                            size="sm"></x-ui.badge>
                            </div>
                        @endif

                        <div class="col-span-1 md:col-span-2">
                            <span
                                  class="text-base-content/70 block text-sm font-medium">{{ __('fields.description') }}</span>
                            <p class="text-base-content/80">{{ $template->description ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Translations Review --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <x-ui.title level="3"
                                class="mb-4">{{ __('email_templates.show.translations') }}</x-ui.title>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('fields.locale') }}</th>
                                    <th>{{ __('email_templates.form.subject') }}</th>
                                    <th>{{ __('email_templates.show.html_length') }}</th>
                                    <th>{{ __('fields.updated_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($template->translations as $translation)
                                    <tr>
                                        <td><x-ui.badge :text="__('locales.' . $translation->locale)"
                                                        size="sm"></x-ui.badge></td>
                                        <td>{{ $translation->subject ?? '—' }}</td>
                                        <td>{{ strlen($translation->html_content) }} {{ __('common.chars') }}</td>
                                        <td>{{ $translation->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-error">
                <x-ui.icon name="exclamation-triangle"
                           size="sm"></x-ui.icon>
                <span>{{ __('email_templates.not_found') }}</span>
            </div>
        @endif
    </section>
</x-layouts.page>
