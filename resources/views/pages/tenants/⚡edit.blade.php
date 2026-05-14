<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

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
    public ?string $plan = null;
    public bool $should_seed = true;
    public array $selectedUsers = [];

    /**
     * Initialize the component.
     */
    public function mount(?Tenant $tenant = null): void
    {
        $this->authorizeAccess($tenant);
        $this->initializeUnifiedModel($tenant, fn ($t) => $this->loadExistingTenant($t), fn () => $this->prepareNewTenant());

        $this->modelTypeLabel = __('tenancy.tenant');
        $this->updatePageHeader();
    }

    /**
     * Authorize access to the component.
     */
    protected function authorizeAccess(?Tenant $tenant): void
    {
        if ($tenant instanceof Tenant && $tenant->exists) {
            $this->authorize(PolicyAbilities::UPDATE, $tenant);

            return;
        }

        $this->authorize(PolicyAbilities::CREATE, Tenant::class);
    }

    /**
     * Load existing tenant data into form fields.
     */
    protected function loadExistingTenant(Tenant $tenant): void
    {
        $this->model = $tenant;
        $this->name = (string) ($tenant->name ?? '');
        $this->tenant_id = (string) ($tenant->id ?? '');
        $this->plan = $tenant->getAttribute('plan');
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
     * Get the URL to redirect back to.
     */
    #[Computed]
    public function cancelUrl(): string
    {
        if ($this->isCreateMode || ! ($this->model?->id)) {
            return route('tenants.index');
        }

        return route('tenants.show', $this->model->id);
    }

    /**
     * Update the page title and subtitle.
     */
    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => __('tenancy.tenant')]);
            $this->pageSubtitle = __('pages.common.create.description', ['type' => __('tenancy.tenant')]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => __('tenancy.tenant'), 'name' => $this->name]);
            $this->pageSubtitle = __('pages.common.edit.description', ['type' => __('tenancy.tenant')]);
        }
    }

    public function getPageSubtitle(): ?string
    {
        if ($this->isCreateMode) {
            return __('tenancy.create_tenant_description');
        }

        return __('tenancy.edit_tenant_description', ['name' => $this->model?->name]);
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

        $userIds = User::whereIn('uuid', $this->selectedUsers)->pluck('id')->toArray();

        if ($this->isCreateMode) {
            $this->model = $tenantService->createTenant($data, $userIds);
            $messageKey = 'tenancy.tenant_created';
            $redirectUrl = route('tenants.show', $this->model->id);
        } else {
            $this->model = $tenantService->updateTenant($this->model, $data, $userIds);
            $messageKey = 'tenancy.tenant_updated';
            $redirectUrl = route('tenants.show', $this->model->id);
        }

        $this->sendSuccessNotification($this->model, $messageKey);
        $this->redirect($redirectUrl);
    }

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', 'exists:central.plans,uuid'],
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
        return App\Models\Plan::where('is_active', true)->get()->pluck('name', 'uuid')->toArray();
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <x-ui.button type="submit"
                     form="tenant-form"
                     color="primary">
            <x-ui.loading wire:loading
                          wire:target="{{ $this->submitAction }}"
                          size="sm"></x-ui.loading>
            {{ $this->submitButtonText }}
        </x-ui.button>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-4xl"
             x-data="tenantEdit({
                 name: $wire.$entangle('name'),
                 tenantId: $wire.$entangle('tenant_id'),
                 isCreateMode: @js($isCreateMode)
             })">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <x-ui.form wire:submit="{{ $this->submitAction }}"
                           id="tenant-form"
                           class="space-y-8">
                    {{-- Basic Information --}}
                    <div class="space-y-6">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('tenancy.basic_info') }}</x-ui.title>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <x-ui.input label="{{ __('tenancy.tenant_name') }}"
                                            wire:model="name"
                                            x-model="name"
                                            required
                                            autofocus
                                            placeholder="My Awesome Company" />
                            </div>

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

                            <x-ui.select label="{{ __('tenancy.plan') }}"
                                         wire:model="plan"
                                         :options="$this->planOptions"
                                         required />
                        </div>
                    </div>

                    @if ($isCreateMode)
                        <div class="divider"></div>
                        <div class="space-y-4">
                            <x-ui.title level="3"
                                        class="text-base-content/70">{{ __('tenancy.initial_configuration') }}</x-ui.title>

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

                    {{-- Assigned Users --}}
                    <div class="divider"></div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <x-ui.title level="3"
                                        class="text-base-content/70">{{ __('tenancy.assigned_users') }}</x-ui.title>
                        </div>

                        <p class="text-base-content/60 text-sm">{{ __('tenancy.assigned_users_description') }}</p>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @forelse ($this->availableUsers as $user)
                                <label wire:key="user-{{ $user->uuid }}"
                                       class="hover:bg-base-200 has-[:checked]:border-primary/30 has-[:checked]:bg-primary/5 group flex cursor-pointer items-center gap-4 rounded-xl border border-transparent p-3 transition-all duration-200">
                                    <div class="relative flex items-center justify-center">
                                        <x-ui.checkbox wire:model="selectedUsers"
                                                       value="{{ $user->uuid }}"
                                                       color="primary"
                                                       class="checkbox-sm" />
                                    </div>

                                    <div class="flex flex-1 items-center gap-3 overflow-hidden">
                                        <x-ui.avatar :user="$user"
                                                     size="sm"
                                                     class="ring-base-content/10 ring-1 ring-offset-2 ring-offset-base-100" />
                                        <div class="flex min-w-0 flex-col">
                                            <span class="truncate text-sm font-bold transition-colors group-hover:text-primary">{{ $user->name }}</span>
                                            <span class="text-base-content/50 truncate text-[11px]">{{ $user->email }}</span>
                                        </div>
                                    </div>

                                    @if (in_array($user->uuid, $selectedUsers))
                                        <x-ui.icon name="check-circle"
                                                   size="sm"
                                                   class="text-primary"
                                                   variant="solid" />
                                    @endif
                                </label>
                            @empty
                                <div class="col-span-full py-8 text-center">
                                    <x-ui.icon name="users"
                                               size="lg"
                                               class="text-base-content/20 mx-auto mb-2" />
                                    <p class="text-base-content/50 text-sm italic">{{ __('common.no_records_found') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </x-ui.form>
            </div>
        </div>
    </section>
</x-layouts.page>

@assets
    <script @cspNonce>
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
