<?php

declare(strict_types=1);

use App\Enums\Subscription\SubscriptionStatus;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public Tenant $tenant;
    public ?Subscription $currentSubscription = null;

    public string|int|null $selectedPlanId = null;

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->currentSubscription = $tenant->currentSubscription;
        $this->pageTitle = 'subscriptions.title';
        $this->pageSubtitle = 'subscriptions.subtitle';
        $this->selectedPlanId = $this->currentSubscription?->plan_id;
    }

    public function subscribe(): void
    {
        $this->validate([
            'selectedPlanId' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::find($this->selectedPlanId);

        // Deactivate current active subscriptions
        Subscription::where('tenant_id', $this->tenant->tenant_id)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::CANCELED]);

        // Create new subscription
        Subscription::create([
            'tenant_id' => $this->tenant->tenant_id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => $plan->billing_cycle === 'monthly' ? now()->addMonth() : ($plan->billing_cycle === 'yearly' ? now()->addYear() : null),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('subscriptions.subscribed_successfully'),
        ]);

        $this->currentSubscription = $this->tenant->currentSubscription;
    }

    #[Computed]
    public function plans()
    {
        return Plan::where('is_active', true)->get();
    }

    public function getPageSubtitle(): ?string
    {
        return __('subscriptions.subtitle', ['name' => $this->tenant->name]);
    }
}; ?>

<x-layouts.page :title="__($pageTitle)"
                :subtitle="__($pageSubtitle, ['name' => $tenant->name])">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Current Subscription --}}
        <div class="space-y-8 lg:col-span-2">
            <x-ui.card title="{{ __('subscriptions.current_plan') }}">
                @if ($currentSubscription)
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold">{{ $currentSubscription->plan->name }}</h3>
                            <p class="text-base-content/60 text-sm">
                                {{ __('subscriptions.status') }}:
                                <span class="badge {{ $currentSubscription->status->color() }}">
                                    {{ $currentSubscription->status->label() }}
                                </span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ __('subscriptions.expires_at') }}</p>
                            <p class="text-base-content/60">
                                {{ $currentSubscription->ends_at?->format('Y-m-d') ?? __('subscriptions.no_expiry') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-ui.icon name="credit-card"
                                   size="lg"
                                   class="text-base-content/20 mx-auto mb-4" />
                        <p class="text-base-content/60">{{ __('subscriptions.no_active_subscription') }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="{{ __('subscriptions.history') }}">
                <livewire:tables.subscription-table :tenant="$tenant" />
            </x-ui.card>
        </div>

        {{-- Subscribe/Upgrade --}}
        <div>
            <x-ui.card title="{{ __('subscriptions.change_plan') }}">
                <x-ui.form wire:submit="subscribe">
                    <div class="space-y-4">
                        @foreach ($this->plans as $plan)
                            <label
                                   class="hover:bg-base-200 {{ $selectedPlanId === $plan->id ? 'border-primary bg-primary/5' : 'border-base-300' }} flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="radio"
                                           wire:model.live="selectedPlanId"
                                           value="{{ $plan->id }}"
                                           class="radio radio-primary" />
                                    <div>
                                        <p class="font-bold">{{ $plan->name }}</p>
                                        <p class="text-base-content/60 text-xs">{{ $plan->price }}
                                            {{ $plan->currency }} / {{ __("plans.cycles.{$plan->billing_cycle}") }}
                                        </p>
                                    </div>
                                </div>
                                <span class="badge {{ $plan->tier->color() }}">{{ $plan->tier->label() }}</span>
                            </label>
                        @endforeach
                    </div>

                    <x-slot:actions>
                        <x-ui.button type="submit"
                                     color="primary"
                                     class="w-full"
                                     icon="check-circle"
                                     wire:loading.attr="disabled">
                            {{ __('subscriptions.update_subscription') }}
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.page>
