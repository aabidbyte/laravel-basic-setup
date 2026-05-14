<?php

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Tenant;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantService;

new class extends BasePageComponent {
    public ?Tenant $tenant = null;

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    /**
     * Mount the component and authorize access.
     */
    public function mount(Tenant $tenant): void
    {
        $this->authorize(PolicyAbilities::VIEW, $tenant);

        $this->tenant = $tenant->load(['planModel', 'currentSubscription.plan', 'users']);
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
        @can(PolicyAbilities::UPDATE, $tenant)
            <x-ui.button href="{{ route('tenants.settings.edit', $tenant->id) }}"
                         variant="primary"
                         size="sm"
                         icon="pencil"
                         wire:navigate>
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
                         variant="error"
                         size="sm"
                         icon="trash">
                {{ __('actions.delete') }}
            </x-ui.button>
        @endcan
    </x-slot:topActions>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3"
         @confirm-delete-tenant.window="$wire.deleteTenant()">

        {{-- Left: Tenant Details --}}
        <div class="space-y-6 lg:col-span-1">
            <x-ui.card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ __('tenancy.overview') }}</h3>
                        <x-ui.badge variant="{{ $tenant->planModel?->tier?->color() ?? 'badge-ghost' }}"
                                        size="md">
                                {{ $tenant->planModel?->name ?? __('tenancy.no_plan') }}
                            </x-ui.badge>
                    </div>

                    <div class="divider my-0"></div>

                    <div class="space-y-3">
                        <div>
                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.tenant_id') }}</span>
                            <p class="font-mono text-sm">{{ $tenant->id }}</p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.tenant_name') }}</span>
                            <p class="font-medium">{{ $tenant->name }}</p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('common.created_at') }}</span>
                            <p class="font-medium">{{ formatDateTime($tenant->created_at) }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">{{ __('tenancy.technical_details') }}</h3>
                    <div class="divider my-0"></div>

                    <div class="space-y-3">
                        <div>
                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.database_name') }}</span>
                            <p class="font-mono text-sm">{{ $tenant->tenancy_db_name ?? '—' }}</p>
                        </div>

                        @if($tenant->domain)
                        <div>
                            <span class="text-base-content/60 text-xs uppercase tracking-wider font-bold">{{ __('tenancy.domain') }}</span>
                            <p class="font-medium text-primary">{{ $tenant->domain }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Right: Assigned Users DataTable --}}
        <div class="lg:col-span-2">
            <x-ui.card class="h-full">
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

                    <livewire:tables.tenant-user-table :tenantId="$tenant->id" />
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.page>
