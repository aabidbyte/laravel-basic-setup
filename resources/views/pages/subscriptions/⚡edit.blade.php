<?php

declare(strict_types=1);

use App\Constants\Auth\PolicyAbilities;
use App\Enums\Subscription\SubscriptionStatus;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Features\FeatureResolver;
use App\Services\Features\FeatureValueNormalizer;
use App\Services\Subscriptions\SubscriptionPeriodCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public ?string $modelTypeLabel = 'subscriptions.subscription';

    public ?Illuminate\Database\Eloquent\Model $model = null;

    public ?string $tenant_id = null;
    public ?int $plan_id = null;
    public string $status = 'active';
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public ?string $trial_ends_at = null;
    public ?string $note = null;

    /**
     * Initialize the component.
     */
    public function mount(?Subscription $subscription = null): void
    {
        $subscription = $subscription?->exists ? $subscription : null;

        $this->authorizeAccess($subscription);
        $this->initializeUnifiedModel($subscription, fn ($model) => $this->loadExistingSubscription($model), fn () => $this->prepareNewSubscription());
        $this->updatePageHeader();
    }

    /**
     * Authorize access based on mode.
     */
    protected function authorizeAccess(?Subscription $subscription): void
    {
        $ability = $subscription ? PolicyAbilities::UPDATE : PolicyAbilities::CREATE;
        $this->authorize($ability, $subscription ?? Subscription::class);
    }

    /**
     * Load existing subscription data.
     */
    protected function loadExistingSubscription(Subscription $subscription): void
    {
        $this->model = $subscription;
        $this->tenant_id = $subscription->tenant_id;
        $this->plan_id = $subscription->plan_id;
        $this->status = $subscription->status?->value ?? SubscriptionStatus::ACTIVE->value;
        $this->starts_at = $this->formatDateTime($subscription->starts_at);
        $this->ends_at = $this->formatDateTime($subscription->ends_at);
        $this->trial_ends_at = $this->formatDateTime($subscription->trial_ends_at);
        $this->note = $subscription->extras['note'] ?? null;
    }

    /**
     * Prepare new subscription defaults.
     */
    protected function prepareNewSubscription(): void
    {
        $this->model = new Subscription();
        $this->starts_at = now()->format('Y-m-d\TH:i');
    }

    /**
     * Update page header.
     */
    protected function updatePageHeader(): void
    {
        $this->pageTitle = $this->isCreateMode ? 'subscriptions.create_title' : 'subscriptions.edit_title';
        $this->pageSubtitle = $this->isCreateMode ? 'subscriptions.create_subtitle' : 'subscriptions.edit_subtitle';
    }

    public function getPageSubtitle(): ?string
    {
        if ($this->isCreateMode) {
            return __('subscriptions.create_subtitle');
        }

        return __('subscriptions.edit_subtitle', ['name' => $this->model?->label()]);
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', Rule::exists('central.tenants', 'tenant_id')],
            'plan_id' => ['required', 'integer', Rule::exists('central.plans', 'id')],
            'status' => ['required', 'string', Rule::in(collect(SubscriptionStatus::cases())->pluck('value')->toArray())],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'trial_ends_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Create a new subscription.
     */
    public function create(): void
    {
        $this->validate();

        $this->model = Subscription::create($this->prepareData());

        $this->sendSuccessNotification($this->model, 'pages.common.create.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Save existing subscription.
     */
    public function save(): void
    {
        $this->validate();

        $this->model->update($this->prepareData());

        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect($this->cancelUrl, navigate: true);
    }

    /**
     * Prepare data for saving.
     */
    protected function prepareData(): array
    {
        $plan = Plan::query()->find($this->plan_id);
        $period = $plan ? app(SubscriptionPeriodCalculator::class)->forPlan($plan, $this->starts_at ? Carbon::parse($this->starts_at) : null) : null;

        return [
            'tenant_id' => $this->tenant_id,
            'plan_id' => $this->plan_id,
            'status' => $this->status,
            'starts_at' => $this->starts_at ?: ($period['starts_at'] ?? null),
            'ends_at' => $this->ends_at ?: ($period['ends_at'] ?? null),
            'trial_ends_at' => $this->trial_ends_at,
            'extras' => $this->note ? ['note' => $this->note] : [],
        ];
    }

    protected function formatDateTime(mixed $value): ?string
    {
        return $value?->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function tenantOptions(): array
    {
        return Tenant::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Tenant $tenant) => [$tenant->tenant_id => $tenant->name ?? $tenant->tenant_id])
            ->toArray();
    }

    #[Computed]
    public function planOptions(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (Plan $plan) => [$plan->id => $plan->name])
            ->toArray();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(SubscriptionStatus::cases())
            ->mapWithKeys(fn (SubscriptionStatus $status) => [$status->value => $status->label()])
            ->toArray();
    }

    #[Computed]
    public function effectiveFeatures(): Collection
    {
        if (! $this->tenant_id) {
            return collect();
        }

        $tenant = Tenant::query()
            ->where('tenant_id', $this->tenant_id)
            ->first();

        if (! $tenant) {
            return collect();
        }

        return app(FeatureResolver::class)->effectiveFeatures($tenant);
    }

    #[Computed]
    public function cancelUrl(): string
    {
        return route('subscriptions.index');
    }
}; ?>

<x-layouts.page backHref="{{ $this->cancelUrl }}">
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
                         form="subscription-form"
                         color="primary"
                         size="sm">
                <x-ui.icon name="check"
                           size="sm" />
                {{ $this->submitButtonText }}
            </x-ui.button>
        </div>
    </x-slot:bottomActions>

    <div class="mx-auto max-w-4xl">
        <x-ui.card>
            <x-ui.form wire:submit="{{ $this->submitAction }}"
                       id="subscription-form">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <x-ui.select label="{{ __('subscriptions.tenant') }}"
                                     wire:model="tenant_id"
                                     :options="$this->tenantOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('subscriptions.plan') }}"
                                     wire:model="plan_id"
                                     :options="$this->planOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.select label="{{ __('subscriptions.status') }}"
                                     wire:model="status"
                                     :options="$this->statusOptions"
                                     required />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('subscriptions.starts_at') }}"
                                    wire:model="starts_at"
                                    type="datetime-local" />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('subscriptions.ends_at') }}"
                                    wire:model="ends_at"
                                    type="datetime-local" />
                    </div>

                    <div>
                        <x-ui.input label="{{ __('subscriptions.trial_ends_at') }}"
                                    wire:model="trial_ends_at"
                                    type="datetime-local" />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.input label="{{ __('subscriptions.note') }}"
                                    wire:model="note"
                                    type="textarea"
                                    rows="4" />
                    </div>
                </div>
            </x-ui.form>
        </x-ui.card>

        @if ($this->effectiveFeatures->isNotEmpty())
            <x-ui.card class="mt-6"
                       title="{{ __('subscriptions.effective_features') }}"
                       description="{{ __('subscriptions.effective_features_description') }}">
                <div class="divide-base-200 divide-y">
                    @foreach ($this->effectiveFeatures as $resolvedFeature)
                        <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $resolvedFeature['feature']->label() }}</p>
                                <p class="text-base-content/60 text-xs">{{ $resolvedFeature['feature']->key }}</p>
                            </div>
                            <x-ui.badge :color="$resolvedFeature['enabled'] ? 'success' : 'ghost'"
                                        size="sm">
                                {{ app(FeatureValueNormalizer::class)->display($resolvedFeature['value']) }}
                            </x-ui.badge>
                            <x-ui.badge variant="outline"
                                        size="sm">
                                {{ __("features.sources.{$resolvedFeature['source']}") }}
                            </x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.page>
