<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Plan;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;

class PlanTable extends Datatable
{
    /**
     * The table title.
     */
    public ?string $title = 'plans.list_title';

    /**
     * Whether to show the search bar.
     */
    public bool $showSearch = true;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        return Plan::query()
            ->select(['plans.*']);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('plans.name'), 'name')
                ->searchable()
                ->sortable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('plans.tier'), 'tier')
                ->format(fn ($value) => $value->label())
                ->badge(fn ($plan) => $plan->tier->color()),

            Column::make(__('plans.price'), 'price')
                ->sortable()
                ->format(fn ($value, $row) => "{$value} {$row->currency}"),

            Column::make(__('plans.billing_cycle'), 'billing_cycle')
                ->format(fn ($value) => __("plans.cycles.{$value}")),

            Column::make(__('plans.status'), 'is_active')
                ->format(fn ($value) => $value ? __('plans.active') : __('plans.inactive'))
                ->badge(fn ($plan) => $plan->is_active ? 'badge-success' : 'badge-ghost'),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->color('ghost')
                ->execute(fn ($plan) => $this->redirect(route('plans.edit', $plan->id), navigate: true)),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->color('error')
                ->confirm(__('plans.delete_confirm'))
                ->execute(fn ($plan) => $this->deletePlan($plan->id)),
        ];
    }

    /**
     * Delete a plan.
     */
    public function deletePlan(string $id): void
    {
        $plan = Plan::find($id);
        
        if ($plan) {
            $plan->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('plans.deleted_successfully'),
            ]);
        }
    }
}
