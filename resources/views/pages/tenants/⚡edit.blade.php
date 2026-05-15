<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Enums\Ui\ThemeColorTypes;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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
    public string $slug = '';
    public ?string $plan = null;
    public string $color = 'neutral';
    public string $primary_domain = '';
    public string $newDomain = '';
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
        $this->slug = (string) ($tenant->slug ?? '');
        $this->plan = $tenant->getAttribute('plan');
        $this->color = (string) ($tenant->color ?? 'neutral');
        $this->primary_domain = (string) ($tenant->domains()->oldest()->first()?->domain ?? '');
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
        if ($this->isCreateMode || ! ($this->model?->tenant_id)) {
            return route('tenants.index');
        }

        return route('tenants.show', $this->model->tenant_id);
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
     * Handle model update.
     */
    public function save(TenantService $tenantService): void
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'name' => $this->name,
            'plan' => $this->plan,
            'color' => $this->color,
            'should_seed' => $this->should_seed,
        ];

        if ($this->primary_domain !== '') {
            $data['domain'] = $this->primary_domain;
        }

        $this->model = $tenantService->updateTenant($this->model, $data);
        $messageKey = 'tenancy.tenant_updated';
        $redirectUrl = route('tenants.show', $this->model->tenant_id);

        $this->sendSuccessNotification($this->model, $messageKey);
        $this->redirect($redirectUrl);
    }

    /**
     * Handle model creation.
     */
    public function create(TenantService $tenantService): void
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'name' => $this->name,
            'plan' => $this->plan,
            'color' => $this->color,
            'should_seed' => $this->should_seed,
        ];

        if ($this->primary_domain !== '') {
            $data['domain'] = $this->primary_domain;
        }

        $userIds = User::whereIn('uuid', $this->selectedUsers)->pluck('id')->toArray();

        $this->model = $tenantService->createTenant($data, $userIds);
        $messageKey = 'tenancy.tenant_created';
        $redirectUrl = route('tenants.show', $this->model->tenant_id);

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
            'color' => ['required', new Enum(ThemeColorTypes::class)],
            'primary_domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('central.domains', 'domain')->ignore($this->primaryDomainId()),
            ],
            'selectedUsers' => ['array'],
            'selectedUsers.*' => ['exists:users,uuid'],
        ];

        if ($this->isCreateMode) {
            $rules['slug'] = ['required', 'string', 'alpha_dash', 'max:255', 'unique:tenants,slug'];
            $rules['should_seed'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Add a domain to an existing tenant.
     */
    public function addDomain(): void
    {
        if ($this->isCreateMode || ! $this->model instanceof Tenant) {
            return;
        }

        $this->authorize(PolicyAbilities::UPDATE, $this->model);

        $this->validateOnly('newDomain', [
            'newDomain' => ['required', 'string', 'max:255', Rule::unique('central.domains', 'domain')],
        ]);

        $this->model->domains()->create(['domain' => $this->newDomain]);
        $this->newDomain = '';

        NotificationBuilder::make()
            ->title('tenancy.domain_created')
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    /**
     * Get the current primary domain ID for unique validation.
     */
    protected function primaryDomainId(): ?int
    {
        if (! $this->model instanceof Tenant || ! $this->model->exists) {
            return null;
        }

        return $this->model->domains()->oldest()->first()?->id;
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

    /**
     * Get theme color options for color picker component.
     */
    #[Computed]
    public function colorOptions(): array
    {
        return ThemeColorTypes::options();
    }
}; ?>

<x-layouts.page :backHref="$this->cancelUrl"
                backLabel="{{ __('actions.cancel') }}">
    <x-slot:bottomActions>
        <div class="flex items-center justify-end gap-3">
            <x-ui.button :href="$this->cancelUrl"
                         wire:navigate
                         variant="ghost"
                         size="sm">
                <x-ui.icon name="x-mark"
                           size="sm" />
                {{ __('actions.cancel') }}
            </x-ui.button>

            <x-ui.button type="submit"
                         form="tenant-form"
                         color="primary"
                         size="sm">
                <x-ui.icon name="check"
                           size="sm" />
                {{ $this->submitButtonText }}
            </x-ui.button>
        </div>
    </x-slot:bottomActions>

    <section class="mx-auto w-full max-w-4xl"
             x-data="tenantEdit({
                 name: $wire.$entangle('name'),
                 slug: $wire.$entangle('slug'),
                 primaryDomain: $wire.$entangle('primary_domain'),
                 centralDomain: @js(config('tenancy.central_domains.0')),
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

                            <x-ui.input label="{{ __('tenancy.organization_slug') }}"
                                        wire:model="slug"
                                        x-model="slug"
                                        @input="handleInput()"
                                        required
                                        :disabled="!$isCreateMode"
                                        placeholder="my-company"
                                        hint="{{ __('tenancy.organization_slug_hint') }}">
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

                            <div class="md:col-span-2">
                                <x-ui.color-picker label="{{ __('fields.color') }}"
                                                   wire:model="color"
                                                   :options="$this->colorOptions"
                                                   required />
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>
                    <div class="space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70">{{ __('tenancy.domain_configuration') }}</x-ui.title>

                        <x-ui.input label="{{ __('tenancy.primary_domain') }}"
                                    wire:model="primary_domain"
                                    x-model="primaryDomain"
                                    placeholder="my-company.example.com"
                                    hint="{{ __('tenancy.primary_domain_hint') }}">
                            <x-slot:prepend>
                                <x-ui.icon name="globe-alt"
                                           size="sm"
                                           class="text-base-content/40" />
                            </x-slot:prepend>
                        </x-ui.input>

                        @if (! $isCreateMode)
                            <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
                                <x-ui.input label="{{ __('tenancy.new_domain') }}"
                                            wire:model="newDomain"
                                            placeholder="app.example.com"
                                            hint="{{ __('tenancy.new_domain_hint') }}" />

                                <x-ui.button type="button"
                                             wire:click="addDomain"
                                             color="primary"
                                             size="sm"
                                             class="md:mb-6">
                                    <x-ui.icon name="plus"
                                               size="sm" />
                                    {{ __('tenancy.add_domain') }}
                                </x-ui.button>
                            </div>

                            <livewire:tables.domain-table :tenantId="$model->tenant_id" />
                        @endif
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

                        @if ($isCreateMode)
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
                        @else
                            <livewire:tables.tenant-user-assignment-table :tenantId="$model->tenant_id" />
                        @endif
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
                    slug: config.slug || '',
                    primaryDomain: config.primaryDomain || '',
                    centralDomain: config.centralDomain || '',
                    autoSlug: config.isCreateMode || false,

                    init() {
                        this.$watch('name', value => {
                            if (this.autoSlug) {
                                this.slug = this.slugify(value);
                                this.syncPrimaryDomain();
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
                        this.syncPrimaryDomain();
                    },

                    syncPrimaryDomain() {
                        if (!this.primaryDomain && this.slug && this.centralDomain) {
                            this.primaryDomain = `${this.slug}.${this.centralDomain}`;
                        }
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
