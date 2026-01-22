@php
    use App\Constants\Auth\Permissions;

    $type = request()->routeIs('emailTemplates.layouts.index') ? 'layout' : 'content';
    $title = $type === 'layout' ? __('types.email_layouts') : __('types.email_contents');

    setPageTitle(
        __('pages.email_templates.index.title', ['default' => $title]),
        __('pages.email_templates.index.subtitle', ['default' => 'Manage your email templates and layouts.']),
    );
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('dashboard') }}">
        <x-slot:topActions>
            <div class="flex gap-2">
                @can(Permissions::CREATE_EMAIL_TEMPLATES)
                    <x-ui.button href="{{ route('emailTemplates.create', ['type' => $type]) }}"
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
            {{-- Tabs --}}
            <div role="tablist"
                 class="tabs tabs-bordered">
                <a role="tab"
                   class="tab {{ $type === 'content' ? 'tab-active' : '' }}"
                   href="{{ route('emailTemplates.contents.index') }}"
                   wire:navigate>
                    {{ __('types.email_contents') }}
                </a>
                <a role="tab"
                   class="tab {{ $type === 'layout' ? 'tab-active' : '' }}"
                   href="{{ route('emailTemplates.layouts.index') }}"
                   wire:navigate>
                    {{ __('types.email_layouts') }}
                </a>
            </div>

            <section>
                <livewire:tables.email-template.email-template-table :is-layout="$type === 'layout'"
                                                                     lazy></livewire:tables.email-template.email-template-table>
            </section>
        </div>
    </x-layouts.page>
</x-layouts.app>
