<?php

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Tenant;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantService;

new class extends BasePageComponent {
    public ?Tenant $tenant = null;

    public string $activeTab = 'overview';

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Tenant $tenant): void
    {
        $this->authorize(PolicyAbilities::VIEW, $tenant);

        $this->tenant = $tenant
            ->load(['planModel', 'currentSubscription.plan', 'users', 'domains'])
            ->loadCount(['users', 'domains']);
    }

    /**
     * Get the page title.
     */
    public function getPageTitle(): string
    {
        return $this->tenant->name;
    }

    /**
     * Get the page subtitle.
     */
    public function getPageSubtitle(): ?string
    {
        return __('tenancy.tenants_management');
    }

    /**
     * Get tabs for the tenant detail page.
     */
    public function tabs(): array
    {
        return [
            [
                'key' => 'overview',
                'label' => __('tenancy.overview'),
                'icon' => 'information-circle',
            ],
            [
                'key' => 'users',
                'label' => __('tenancy.manage_users'),
                'icon' => 'users',
            ],
            [
                'key' => 'domains',
                'label' => __('tenancy.domains'),
                'icon' => 'globe-alt',
            ],
        ];
    }

    /**
     * Delete the tenant.
     */
    public function deleteTenant(): void
    {
        $this->authorize(PolicyAbilities::DELETE, $this->tenant);

        $name = $this->tenant->name;
        app(TenantService::class)->deleteTenant($this->tenant);

        NotificationBuilder::make()
            ->title('tenancy.tenant_deleted')
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('tenants.index'), navigate: true);
    }
}; ?>

<x-layouts.page backHref="{{ route('tenants.index') }}">
    <x-slot:topActions>
        <x-ui.button href="{{ route('tenants.switch', $tenant->tenant_id) }}"
                     color="success"
                     size="sm">
            <x-ui.icon name="arrow-path"
                       size="sm" />
            {{ __('tenancy.switch') }}
        </x-ui.button>

        @can(PolicyAbilities::UPDATE, $tenant)
            <x-ui.button href="{{ route('tenants.settings.edit', $tenant->tenant_id) }}"
                         color="primary"
                         size="sm"
                         wire:navigate>
                <x-ui.icon name="pencil"
                           size="sm" />
                {{ __('actions.edit') }}
            </x-ui.button>
        @endcan

        @can(PolicyAbilities::DELETE, $tenant)
            <x-ui.button @click="confirmModal({
                             title: @js(__('actions.delete')),
                             message: @js(__('tenancy.confirm_delete_tenant')),
                             confirmColor: 'error',
                             confirmEvent: 'confirm-delete-tenant'
                         })"
                         color="error"
                         size="sm">
                <x-ui.icon name="trash"
                           size="sm" />
                {{ __('actions.delete') }}
            </x-ui.button>
        @endcan
    </x-slot:topActions>

    <div @confirm-delete-tenant.window="$wire.deleteTenant()">
        <x-ui.tabs :tabs="$this->tabs()"
                   :active="$activeTab"
                   class="mb-6" />

        {{-- Tab Content --}}
        <div class="mt-6">
            @if($activeTab === 'overview')
                <div class="space-y-6">
                    <section class="border-base-300 bg-base-100 rounded-box border p-6 shadow-sm">
                        <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-start">
                            <div class="min-w-0 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <x-ui.badge :color="$tenant->color"
                                                size="md">
                                        {{ __("fields.colors.{$tenant->color}") }}
                                    </x-ui.badge>

                                    <x-ui.badge :variant="$tenant->planModel ? 'solid' : 'ghost'"
                                                :color="$tenant->planModel?->tier?->color()"
                                                size="md">
                                        {{ $tenant->planModel?->name ?? __('tenancy.no_plan') }}
                                    </x-ui.badge>
                                </div>

                                <div>
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.organization_slug') }}</p>
                                    <p class="mt-1 break-all font-mono text-sm">{{ $tenant->slug }}</p>
                                </div>

                                <div>
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.tenant_uuid') }}</p>
                                    <p class="mt-1 break-all font-mono text-sm">{{ $tenant->tenant_id }}</p>
                                </div>

                                <div>
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.primary_domain') }}</p>
                                    <p class="mt-1 break-all font-medium">{{ $tenant->domains->first()?->domain ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:min-w-96">
                                <div class="bg-base-200/60 rounded-box p-4">
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.users_count') }}</p>
                                    <p class="mt-2 text-2xl font-semibold">{{ $tenant->users_count }}</p>
                                </div>

                                <div class="bg-base-200/60 rounded-box p-4">
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.domains') }}</p>
                                    <p class="mt-2 text-2xl font-semibold">{{ $tenant->domains_count }}</p>
                                </div>

                                <div class="bg-base-200/60 rounded-box col-span-2 p-4">
                                    <p class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('common.created_at') }}</p>
                                    <p class="mt-2 text-sm font-semibold">{{ formatDateTime($tenant->created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                        <x-ui.card class="lg:col-span-2">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">{{ __('tenancy.technical_details') }}</h3>

                                <div>
                                    <span class="text-base-content/60 text-xs font-bold uppercase tracking-wider">{{ __('tenancy.database_name') }}</span>
                                    <p class="mt-1 break-all font-mono text-sm">{{ $tenant->tenancy_db_name ?? '—' }}</p>
                                </div>
                            </div>
                        </x-ui.card>

                        <x-ui.card class="lg:col-span-3">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">{{ __('tenancy.subscription_details') }}</h3>

                                @if($tenant->currentSubscription)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.current_plan') }}</span>
                                            <p class="font-medium text-lg text-primary">{{ $tenant->currentSubscription->plan->name }}</p>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.status') }}</span>
                                            <div class="mt-1">
                                                <x-ui.badge :color="$tenant->currentSubscription->status->color()" size="md">
                                                    {{ $tenant->currentSubscription->status->label() }}
                                                </x-ui.badge>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.starts_at') }}</span>
                                            <p class="font-medium">{{ formatDateTime($tenant->currentSubscription->starts_at) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.ends_at') }}</span>
                                            <p class="font-medium">{{ $tenant->currentSubscription->ends_at ? formatDateTime($tenant->currentSubscription->ends_at) : __('tenancy.no_expiry') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-base-content/60 italic">{{ __('tenancy.no_active_subscription') }}</p>
                                @endif
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            @elseif($activeTab === 'users')
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">{{ __('tenancy.assigned_users') }}</h3>
                                <p class="text-base-content/60 text-sm">{{ __('tenancy.assigned_users_description') }}</p>
                            </div>
                            <div class="badge badge-primary badge-lg font-bold">
                                {{ $tenant->users_count ?? $tenant->users->count() }}
                            </div>
                        </div>

                        <div class="divider my-0"></div>

                        <div class="space-y-8">
                            <div class="space-y-3">
                                <x-ui.title level="4"
                                            class="text-base-content/70">{{ __('tenancy.assigned_users') }}</x-ui.title>
                                <livewire:tables.tenant-user-assignment-table :tenantId="$tenant->tenant_id"
                                                                              :key="'tenant-'.$tenant->tenant_id.'-assigned-users'"
                                                                              lazy />
                            </div>

                            <div class="space-y-3">
                                <x-ui.title level="4"
                                            class="text-base-content/70">{{ __('tenancy.available_users') }}</x-ui.title>
                                <p class="text-base-content/60 text-sm">{{ __('tenancy.available_users_description') }}</p>
                                <livewire:tables.tenant-assignable-user-table :tenantId="$tenant->tenant_id"
                                                                              :key="'tenant-'.$tenant->tenant_id.'-available-users'"
                                                                              lazy />
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @elseif($activeTab === 'domains')
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">{{ __('tenancy.domains') }}</h3>
                                <p class="text-base-content/60 text-sm">{{ __('tenancy.manage_domains_description') }}</p>
                            </div>
                        </div>

                        <div class="divider my-0"></div>

                        <livewire:tables.domain-table :tenantId="$tenant->tenant_id"
                                                      lazy />
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.page>
