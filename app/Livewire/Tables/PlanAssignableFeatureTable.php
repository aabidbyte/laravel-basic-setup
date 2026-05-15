<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;
use App\Models\Plan;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Plans\PlanFeatureSyncer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class PlanAssignableFeatureTable extends FeatureTable
{
    #[Locked]
    public string $planUuid = '';

    public ?string $title = 'plans.available_features';

    public ?string $queryStringAlias = 'available_features';

    protected string $datatableIdentifier = 'available-plan-features';

    public function mount(string $planUuid = ''): void
    {
        $this->planUuid = $planUuid;
        $this->authorize(PolicyAbilities::VIEW, $this->plan());
    }

    public function baseQuery(): Builder
    {
        return Feature::query()
            ->where('is_active', true)
            ->whereDoesntHave('planFeatures', fn (Builder $query) => $query->where('plan_id', $this->plan()->id))
            ->select('features.*');
    }

    /**
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        return [
            Action::make('assign', __('features.assign_to_plan'))
                ->icon('plus')
                ->variant('ghost')
                ->color('success')
                ->execute(fn (Feature $feature) => $this->assignBooleanFeature($feature))
                ->show(fn (Feature $feature) => $feature->type === FeatureValueType::BOOLEAN)
                ->can(Permissions::EDIT_PLANS(), false),

            Action::make('configure', __('features.configure_for_plan'))
                ->icon('adjustments-horizontal')
                ->variant('ghost')
                ->color('primary')
                ->livewireModal('plan-features.value-modal', fn (Feature $feature) => [
                    'planUuid' => $this->planUuid,
                    'featureUuid' => $feature->uuid,
                ])
                ->show(fn (Feature $feature) => $feature->type !== FeatureValueType::BOOLEAN)
                ->can(Permissions::EDIT_PLANS(), false),
        ];
    }

    /**
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('assign', __('features.assign_selected_to_plan'))
                ->icon('plus')
                ->variant('ghost')
                ->color('success')
                ->execute(fn (Collection $features) => $this->assignFeatures($features))
                ->can(Permissions::EDIT_PLANS()),
        ];
    }

    public function rowClick(string $uuid): ?Action
    {
        return null;
    }

    #[On('plan-features-updated')]
    public function refreshAfterPlanFeatureChange(): void
    {
        $this->refreshTable();
    }

    protected function assignBooleanFeature(Feature $feature): void
    {
        $this->authorize(PolicyAbilities::UPDATE, $this->plan());
        app(PlanFeatureSyncer::class)->assign([
            'plan' => $this->plan(),
            'feature' => $feature,
            'value' => true,
            'enabled' => true,
        ]);

        $this->dispatch('plan-features-updated');

        NotificationBuilder::make()
            ->title('features.assigned_to_plan_successfully')
            ->success()
            ->send();
    }

    protected function assignFeatures(Collection $features): void
    {
        $this->authorize(PolicyAbilities::UPDATE, $this->plan());

        $features->each(fn (Feature $feature) => app(PlanFeatureSyncer::class)->assign([
            'plan' => $this->plan(),
            'feature' => $feature,
            'value' => $feature->type === FeatureValueType::BOOLEAN ? true : $feature->default_value,
            'enabled' => true,
        ]));

        $this->dispatch('plan-features-updated');

        NotificationBuilder::make()
            ->title('features.assigned_to_plan_successfully')
            ->success()
            ->send();
    }

    protected function plan(): Plan
    {
        return Plan::query()
            ->where('uuid', $this->planUuid)
            ->firstOrFail();
    }
}
