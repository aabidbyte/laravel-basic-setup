<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\EmailTemplate\EmailTemplate;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    #[Locked]
    public string $modelId = '';

    #[Locked]
    public bool $isLayout = false;

    public ?EmailTemplate $model = null;

    public function mount(string $id): void
    {
        $this->modelId = $id;
        $this->authorize(Permissions::VIEW_EMAIL_TEMPLATES);

        $this->model = EmailTemplate::with(['layout', 'translations'])->findOrFail($id);
        $this->isLayout = $this->model->is_layout;
        $this->pageTitle = $this->model->name;
        $this->pageSubtitle = $this->isLayout ? __('types.email_layout') : __('types.email_content');
    }
}; ?>

<x-layouts.app>
    <x-layouts.page
                    backHref="{{ $isLayout ? route('emailTemplates.layouts.index') : route('emailTemplates.contents.index') }}">
        <x-slot:topActions>
            <x-ui.button href="{{ route('emailTemplates.edit', ['id' => $modelId]) }}"
                         color="primary"
                         class="gap-2">
                <x-ui.icon name="pencil"
                           size="sm"></x-ui.icon>
                {{ __('actions.edit') }}
            </x-ui.button>
        </x-slot:topActions>

        <div class="space-y-6">
            {{-- Info Card --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <x-ui.title level="3"
                                class="mb-4">{{ __('common.details') }}</x-ui.title>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <span class="text-base-content/70 block text-sm font-medium">{{ __('fields.name') }}</span>
                            <span class="block text-lg">{{ $model->name }}</span>
                        </div>

                        <div>
                            <span class="text-base-content/70 block text-sm font-medium">{{ __('fields.type') }}</span>
                            <x-ui.badge :label="$model->type"
                                        color="neutral"
                                        size="sm" />
                        </div>

                        @if (!$isLayout)
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('email_templates.form.layout') }}</span>
                                <span class="block">{{ $model->layout->name ?? __('common.none') }}</span>
                            </div>
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('fields.status') }}</span>
                                <x-ui.badge :label="$model->status"
                                            color="neutral"
                                            size="sm" />
                            </div>
                        @else
                            <div>
                                <span
                                      class="text-base-content/70 block text-sm font-medium">{{ __('email_templates.form.is_default') }}</span>
                                <x-ui.badge :label="$model->is_default ? __('common.yes') : __('common.no')"
                                            :color="$model->is_default ? 'success' : 'neutral'"
                                            size="sm" />
                            </div>
                        @endif

                        <div class="col-span-1 md:col-span-2">
                            <span
                                  class="text-base-content/70 block text-sm font-medium">{{ __('fields.description') }}</span>
                            <p class="text-base-content/80">{{ $model->description ?? '—' }}</p>
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
                                @foreach ($model->translations as $translation)
                                    <tr>
                                        <td><x-ui.badge :label="__('locales.' . $translation->locale)"
                                                        size="sm" /></td>
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
        </div>
    </x-layouts.page>
</x-layouts.app>
