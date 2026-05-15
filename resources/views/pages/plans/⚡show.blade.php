<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\Plan;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    #[Locked]
    public string $planUuid = '';

    public ?Plan $plan = null;

    public string $activeTab = 'overview';

    /**
     * Mount the component and authorize access.
     */
    public function mount(Plan $plan): void
    {
        $this->authorize(Permissions::VIEW_PLANS());

        $this->planUuid = $plan->uuid;
        $this->plan = $plan->loadCount('subscriptions');

        $this->pageSubtitle = __('pages.common.show.description', ['type' => __('types.plan')]);
    }

    public function getPageTitle(): string
    {
        return $this->plan?->name ?? __('types.plan');
    }

    /**
     * Get tabs for the plan detail page.
     */
    public function tabs(): array
    {
        return [
            [
                'key' => 'overview',
                'label' => __('plans.overview'),
                'icon' => 'information-circle',
            ],
            [
                'key' => 'features',
                'label' => __('plans.features'),
                'icon' => 'puzzle-piece',
            ],
            [
                'key' => 'subscriptions',
                'label' => __('plans.subscriptions'),
                'icon' => 'credit-card',
            ],
        ];
    }

    /**
     * Delete the plan.
     */
    public function deletePlan(): void
    {
        $this->authorize(Permissions::DELETE_PLANS());

        $name = $this->plan->name;
        $this->plan->delete();

        $this->sendSuccessNotification(null, 'plans.deleted_successfully', ['name' => $name]);

        $this->redirect(route('plans.index'), navigate: true);
    }
}; ?>

<x-layouts.page backHref="{{ route('plans.index') }}">
    <x-slot:topActions>
        @can(Permissions::EDIT_PLANS())
            <x-ui.button href="{{ route('plans.edit', $planUuid) }}"
                         wire:navigate
                         color="primary"
                         size="sm"
                         icon="pencil">
                {{ __('actions.edit') }}
            </x-ui.button>
        @endcan

        @can(Permissions::DELETE_PLANS())
            <x-ui.button x-on:click="confirmModal({
                         title: @js(__('actions.delete')),
                         message: @js(__('plans.delete_confirm')),
                         callback: 'confirm-delete-plan'
                     })"
                         color="error"
                         size="sm"
                         icon="trash">
                {{ __('actions.delete') }}
            </x-ui.button>
        @endcan
    </x-slot:topActions>

    <section class="mx-auto w-full max-w-6xl space-y-6"
             x-on:confirm-delete-plan.window="$wire.deletePlan()">
        <x-ui.tabs :tabs="$this->tabs()"
                   :active="$activeTab"
                   class="mb-6" />

        @if($activeTab === 'overview')
            {{-- Plan Details Card --}}
            <div class="card bg-base-100 border-base-200 overflow-hidden border shadow-xl">
                <div class="card-body p-0">
                    <div class="bg-base-200/50 border-base-200 border-b p-6">
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <x-ui.title level="3">{{ $plan->name }}</x-ui.title>
                                    <x-ui.badge :color="$plan->tier->color()"
                                                size="sm">
                                        {{ $plan->tier->label() }}
                                    </x-ui.badge>
                                </div>
                                <p class="text-base-content/60 text-sm">
                                    {{ __('plans.created_at') }}: {{ formatDateTime($plan->created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-primary text-2xl font-bold">
                                    {{ $plan->price }} <span
                                          class="text-base-content/60 text-sm font-normal">{{ $plan->currency }}</span>
                                </div>
                                <div class="text-base-content/60 text-sm">
                                    {{ $plan->billing_cycle->label() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 p-6 md:grid-cols-2">
                        {{-- Features List --}}
                        <div class="space-y-4">
                            <x-ui.title level="4"
                                        class="text-base-content/70">
                                {{ __('plans.features') }}
                            </x-ui.title>

                            <ul class="space-y-3">
                                @foreach ($plan->features ?? [] as $key => $feature)
                                    <li class="flex items-center gap-3">
                                        <div class="bg-success/10 text-success rounded-full p-1">
                                            <x-ui.icon name="check"
                                                       size="xs" />
                                        </div>
                                        <span class="text-sm">
                                            @if(is_array($feature) && isset($feature['key']))
                                                <span class="text-base-content/80 font-medium">{{ $feature['key'] }}:</span>
                                                <span class="text-base-content/60">{{ $feature['value'] }}</span>
                                            @else
                                                <span class="text-base-content/80 font-medium">{{ is_numeric($key) ? '' : $key . ':' }}</span>
                                                <span class="text-base-content/60">{{ $feature }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach

                                @if (empty($plan->features))
                                    <li class="text-base-content/40 text-sm italic italic">
                                        {{ __('plans.no_features_defined') }}
                                    </li>
                                @endif
                            </ul>
                        </div>

                        {{-- Stats / Status --}}
                        <div class="space-y-4">
                            <x-ui.title level="4"
                                        class="text-base-content/70">
                                {{ __('plans.status_and_stats') }}
                            </x-ui.title>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="bg-base-200/30 border-base-200 rounded-xl border p-4">
                                    <div class="text-base-content/50 mb-1 text-xs font-semibold uppercase tracking-wider">
                                        {{ __('plans.status') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div @class([
                                            'w-2 h-2 rounded-full',
                                            'bg-success' => $plan->is_active,
                                            'bg-base-content/30' => !$plan->is_active,
                                        ])></div>
                                        <span class="font-medium">
                                            {{ $plan->is_active ? __('plans.active') : __('plans.inactive') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-base-200/30 border-base-200 rounded-xl border p-4">
                                    <div class="text-base-content/50 mb-1 text-xs font-semibold uppercase tracking-wider">
                                        {{ __('plans.active_subscriptions') }}
                                    </div>
                                    <div class="text-xl font-bold">
                                        {{ $plan->subscriptions_count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'features')
            {{-- Feature Assignment --}}
            <div class="card bg-base-100 border-base-200 border shadow-xl">
                <div class="card-body space-y-8">
                    <div class="space-y-2">
                        <x-ui.title level="3">{{ __('plans.assigned_features') }}</x-ui.title>
                        <p class="text-base-content/60 text-sm">
                            {{ __('plans.assigned_features_description') }}
                        </p>
                    </div>

                    <livewire:tables.plan-feature-assignment-table :planUuid="$planUuid"
                                                                   :key="'plan-'.$planUuid.'-assigned-features'"
                                                                   lazy />

                    <div class="space-y-2">
                        <x-ui.title level="3">{{ __('plans.available_features') }}</x-ui.title>
                        <p class="text-base-content/60 text-sm">
                            {{ __('plans.available_features_description') }}
                        </p>
                    </div>

                    <livewire:tables.plan-assignable-feature-table :planUuid="$planUuid"
                                                                   :key="'plan-'.$planUuid.'-available-features'"
                                                                   lazy />
                </div>
            </div>
        @elseif($activeTab === 'subscriptions')
            {{-- Subscriptions with this Plan --}}
            <div class="card bg-base-100 border-base-200 border shadow-xl">
                <div class="card-body">
                    <div class="mb-4 flex items-center justify-between">
                        <x-ui.title level="3">{{ __('plans.subscriptions_with_plan') }}</x-ui.title>
                        @can(Permissions::CREATE_SUBSCRIPTIONS())
                            <x-ui.button variant="primary"
                                         color="primary"
                                         size="sm">
                                <x-ui.icon name="plus"
                                           size="xs" />
                                {{ __('plans.add_subscription') }}
                            </x-ui.button>
                        @endcan
                    </div>

                    <livewire:tables.subscription-table :plan="$plan"
                                                        lazy></livewire:tables.subscription-table>
                </div>
            </div>
        @endif
    </section>
</x-layouts.page>
