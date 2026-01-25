@php
    use App\Constants\Auth\Permissions;

    use App\Enums\EmailTemplate\EmailTemplateKind;

    $kind = request()->routeIs('emailTemplates.layouts.index') ? EmailTemplateKind::LAYOUT : EmailTemplateKind::CONTENT;

    $title = $kind === EmailTemplateKind::LAYOUT ? __('types.email_layouts') : __('types.email_contents');

    setPageTitle(
        __('pages.email_templates.index.title', ['default' => $title]),
        __('pages.email_templates.index.subtitle', ['default' => 'Manage your email templates and layouts.']),
    );
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        <x-slot:topActions>
            <div class="flex gap-2">
                @can(Permissions::CREATE_EMAIL_TEMPLATES())
                    <x-ui.button href="{{ route('emailTemplates.settings.edit', ['type' => $kind->value]) }}"
                                 wire:navigate
                                 color="primary"
                                 class="gap-2">
                        <x-ui.icon name="plus"
                                   size="sm"></x-ui.icon>
                        {{ __('actions.create_new') }}
                    </x-ui.button>
                @endcan
            </div>
        </x-slot:topActions>

        <div class="space-y-6">
            <section>
                <livewire:tables.email-template.email-template-table :kind-mode="$kind"
                                                                     lazy></livewire:tables.email-template.email-template-table>
            </section>
        </div>
    </x-layouts.page>
</x-layouts.app>
