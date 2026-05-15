<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Livewire\DataTable\Datatable;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Features\FeatureValueNormalizer;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Plans\PlanFeatureSyncer;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class PlanFeatureAssignmentTable extends Datatable
{
    #[Locked]
    public string $planUuid = '';

    public ?string $title = 'plans.assigned_features';

    public ?string $queryStringAlias = 'assigned_features';

    protected string $datatableIdentifier = 'assigned-plan-features';

    public string $sortBy = 'features.key';

    public string $sortDirection = 'asc';

    public function mount(string $planUuid = ''): void
    {
        $this->planUuid = $planUuid;
        $this->authorize(PolicyAbilities::VIEW, $this->plan());
    }

    public function baseQuery(): Builder
    {
        return PlanFeature::query()
            ->with(['feature', 'plan'])
            ->where('plan_id', $this->plan()->id)
            ->select('plan_features.*');
    }

    /**
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('features.fields.name'), 'feature.name')
                ->format(fn ($value, PlanFeature $planFeature) => "<strong>{$planFeature->feature?->label()}</strong>")
                ->html(),

            Column::make(__('features.fields.key'), 'feature.key')
                ->sortable()
                ->searchable(),

            Column::make(__('features.fields.type'), 'feature.type')
                ->format(fn ($value, PlanFeature $planFeature) => $planFeature->feature?->type?->label() ?? '')
                ->badge(fn () => 'info'),

            Column::make(__('plans.feature_value'), 'value')
                ->format(fn ($value) => app(FeatureValueNormalizer::class)->display($value)),

            Column::make(__('plans.feature_enabled'), 'enabled')
                ->format(fn ($value) => $value ? __('common.yes') : __('common.no'))
                ->badge(fn (PlanFeature $planFeature) => $planFeature->enabled ? 'success' : 'ghost'),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        return [
            Action::make('edit_value', __('features.edit_plan_value'))
                ->icon('pencil')
                ->variant('ghost')
                ->color('primary')
                ->livewireModal('plan-features.value-modal', fn (PlanFeature $planFeature) => [
                    'planFeatureUuid' => $planFeature->uuid,
                ])
                ->can(Permissions::EDIT_PLANS(), false),

            Action::make('remove', __('features.remove_from_plan'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('features.remove_from_plan_confirm'))
                ->execute(fn (PlanFeature $planFeature) => $this->removeFeature($planFeature))
                ->can(Permissions::EDIT_PLANS(), false),
        ];
    }

    public function rowClick(string $uuid): ?Action
    {
        return Action::make()
            ->livewireModal('plan-features.value-modal', fn (PlanFeature $planFeature) => [
                'planFeatureUuid' => $planFeature->uuid,
            ])
            ->can(Permissions::EDIT_PLANS(), false);
    }

    #[On('plan-features-updated')]
    public function refreshAfterPlanFeatureChange(): void
    {
        $this->refreshTable();
    }

    protected function removeFeature(PlanFeature $planFeature): void
    {
        $this->authorize(PolicyAbilities::UPDATE, $this->plan());
        app(PlanFeatureSyncer::class)->remove($planFeature);

        $this->dispatch('plan-features-updated');

        NotificationBuilder::make()
            ->title('features.removed_from_plan_successfully')
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
