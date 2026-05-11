<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantService;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    /**
     * Optional label for the model type used in common translations.
     */
    public ?string $modelTypeLabel = 'tenancy.tenant';

    /**
     * Form fields
     */
    public string $name = '';
    public string $tenant_id = '';
    public string $plan = 'free';
    public bool $should_seed = true;
    public array $selectedUsers = [];

    /**
     * Initialize the component.
     */
    public function mount(?Tenant $tenant = null): void
    {
        $this->authorizeAccess($tenant);
        $this->initializeUnifiedModel($tenant, fn ($t) => $this->loadExistingTenant($t), fn () => $this->prepareNewTenant());
        $this->updatePageHeader();
    }

    /**
     * Authorize access to the component.
     */
    protected function authorizeAccess(?Tenant $tenant): void
    {
        $permission = $tenant ? Permissions::EDIT_TENANTS() : Permissions::CREATE_TENANTS();
        $this->authorize($permission);
    }

    /**
     * Load existing tenant data into form fields.
     */
    protected function loadExistingTenant(Tenant $tenant): void
    {
        $this->model = $tenant;
        $this->name = $tenant->name;
        $this->tenant_id = $tenant->id;
        $this->plan = $tenant->plan ?? 'free';
        $this->selectedUsers = $tenant->users->pluck('uuid')->toArray();
    }

    /**
     * Prepare a new tenant model with defaults.
     */
    protected function prepareNewTenant(): void
    {
        $this->model = new Tenant();
        // If not admin, automatically assign current user
        if (!Auth::user()->can(Permissions::VIEW_TENANTS())) {
            $this->selectedUsers = [Auth::user()->uuid];
        }
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = 'tenancy.create_tenant';
            $this->pageSubtitle = 'tenancy.create_tenant_description';
        } else {
            $this->pageTitle = 'tenancy.edit_tenant';
            $this->pageSubtitle = 'tenancy.edit_tenant_description';
        }
    }

    /**
     * Get variables for translation strings.
     */
    protected function getTranslationParams(): array
    {
        return [
            'name' => $this->name,
            'type' => __($this->modelTypeLabel),
        ];
    }

    /**
     * Override getPageSubtitle to provide dynamic parameters.
     */
    public function getPageSubtitle(): ?string
    {
        $subtitle = parent::getPageSubtitle();
        return $subtitle ? __($subtitle, $this->getTranslationParams()) : null;
    }

    /**
     * Handle form submission.
     */
    public function save(TenantService $tenantService): void
    {
        $this->validate();

        $data = [
            'id' => $this->tenant_id,
            'name' => $this->name,
            'plan' => $this->plan,
            'should_seed' => $this->should_seed,
        ];

        // Map UUIDs back to IDs for the service layer if necessary, 
        // but it's better if service handles UUIDs.
        // For now, we'll convert UUIDs to IDs to maintain service compatibility.
        $userIds = User::whereIn('uuid', $this->selectedUsers)->pluck('id')->toArray();

        if ($this->isCreateMode) {
            $this->model = $tenantService->createTenant($data, $userIds);
            $messageKey = 'pages.common.create.success';
        } else {
            $tenantService->updateTenant($this->model, $data, $userIds);
            $messageKey = 'pages.common.edit.success';
        }

        $this->sendSuccessNotification($this->model, $messageKey);
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', 'in:free,pro,enterprise'],
            'selectedUsers' => ['array'],
            'selectedUsers.*' => ['exists:users,uuid'],
        ];

        if ($this->isCreateMode) {
            $rules['tenant_id'] = ['required', 'string', 'alpha_dash', 'max:255', 'unique:tenants,id'];
            $rules['should_seed'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Get available users for selection.
     */
    #[Computed]
    public function availableUsers()
    {
        return User::all();
    }

    /**
     * Get plan options for select component.
     */
    #[Computed]
    public function planOptions(): array
    {
        return collect(\App\Enums\Tenancy\TenantPlan::cases())
            ->mapWithKeys(fn ($plan) => [$plan->value => $plan->label()])
            ->toArray();
    }

    /**
     * Get the URL to redirect back to.
     */
    #[Computed]
    public function cancelUrl(): string
    {
        return route('tenants.index');
    }
}; ?>

<x-layouts.page backHref="{{ $this->cancelUrl }}">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3"
         x-data="tenantEdit({
             name: $wire.$entangle('name'),
             tenantId: $wire.$entangle('tenant_id'),
             isCreateMode: @js($isCreateMode)
         })">

        {{-- Left: Main Form --}}
        <div class="lg:col-span-2">
            <x-ui.card>
                <x-ui.form wire:submit="{{ $this->submitAction }}">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {{-- Tenant Name --}}
                        <div class="md:col-span-2">
                            <x-ui.input label="{{ __('tenancy.tenant_name') }}"
                                        wire:model="name"
                                        x-model="name"
                                        required
                                        placeholder="My Awesome Company" />
                        </div>

                        {{-- Tenant ID (Slug) --}}
                        <div>
                            <x-ui.input label="{{ __('tenancy.tenant_id') }}"
                                        wire:model="tenant_id"
                                        x-model="tenantId"
                                        @input="handleInput()"
                                        required
                                        :disabled="!$isCreateMode"
                                        placeholder="my-company"
                                        hint="{{ __('tenancy.tenant_id_hint') }}">
                                <x-slot:prepend>
                                    <x-ui.icon name="at-symbol"
                                               size="sm"
                                               class="text-base-content/40" />
                                </x-slot:prepend>
                            </x-ui.input>
                        </div>

                        {{-- Plan --}}
                        <div>
                            <x-ui.select label="{{ __('tenancy.plan') }}"
                                         wire:model="plan"
                                         :options="$this->planOptions"
                                         required />
                        </div>

                        @if ($isCreateMode)
                            {{-- Seeding Toggle --}}
                            <div class="md:col-span-2">
                                <x-ui.card class="bg-base-200/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ __('tenancy.seed_default_data') }}</span>
                                            <span class="text-base-content/60 text-xs">{{ __('tenancy.seed_default_data_description') }}</span>
                                        </div>
                                        <x-ui.toggle wire:model="should_seed"
                                                     color="primary" />
                                    </div>
                                </x-ui.card>
                            </div>
                        @endif
                    </div>

                    <x-slot:actions>
                        <x-ui.button variant="ghost"
                                     href="{{ $this->cancelUrl }}"
                                     wire:navigate>
                            {{ __('actions.cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit"
                                     variant="primary"
                                     wire:loading.attr="disabled"
                                     class="data-loading:opacity-50 data-loading:pointer-events-none">
                            <x-ui.loading wire:loading
                                          size="sm"
                                          :centered="false" />
                            {{ $this->submitButtonText }}
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.card>
        </div>

        {{-- Right: User Assignments --}}
        <div class="space-y-6">
            <x-ui.card title="{{ __('tenancy.assigned_users') }}"
                       description="{{ __('tenancy.assigned_users_description') }}">
                <div class="space-y-4">
                    <div class="max-h-[400px] space-y-2 overflow-y-auto pr-2">
                        @foreach ($this->availableUsers as $user)
                            <label wire:key="user-{{ $user->uuid }}"
                                   class="hover:bg-base-200 has-[:checked]:border-primary/20 has-[:checked]:bg-primary/5 flex cursor-pointer items-center gap-3 rounded-lg border border-transparent p-2 transition-colors">
                                <x-ui.checkbox wire:model="selectedUsers"
                                               value="{{ $user->uuid }}"
                                               color="primary"
                                               size="sm" />
                                <div class="flex items-center gap-2">
                                    <x-ui.avatar :user="$user"
                                                 size="xs" />
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $user->name }}</span>
                                        <span class="text-base-content/50 text-[10px]">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.page>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('tenantEdit', (config) => ({
                    name: config.name || '',
                    tenantId: config.tenantId || '',
                    autoSlug: config.isCreateMode || false,

                    init() {
                        this.$watch('name', value => {
                            if (this.autoSlug) {
                                this.tenantId = this.slugify(value);
                            }
                        });
                    },

                    slugify(text) {
                        return text.toString().toLowerCase()
                            .replace(/\s+/g, '-')
                            .replace(/[^\w\-]+/g, '')
                            .replace(/\-\-+/g, '-')
                            .replace(/^-+/, '')
                            .replace(/-+$/, '');
                    },

                    handleInput() {
                        this.autoSlug = false;
                    }
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
