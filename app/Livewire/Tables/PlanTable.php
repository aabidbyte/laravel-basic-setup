<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\DataTable\Datatable;
use App\Models\Plan;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

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
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Plan::class);
    }

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
                ->badge(fn ($plan) => $plan->is_active ? 'success' : 'ghost'),
        ];
    }

    /**
     * Define the top actions.
     */
    protected function topActions(): array
    {
        return [
            Action::make('view_trash', __('actions.view_trash'))
                ->icon('trash')
                ->variant('ghost')
                ->route(route('trash.index', ['entityType' => 'plans']))
                ->show(auth()->user()?->can(PolicyAbilities::RESTORE, Plan::class) ?? false),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('show', __('actions.view'))
                ->icon('eye')
                ->variant('ghost')
                ->color('info')
                ->route(fn (Plan $plan) => route('plans.show', $plan->uuid))
                ->can(PolicyAbilities::VIEW),

            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->variant('ghost')
                ->color('primary')
                ->route(fn (Plan $plan) => route('plans.edit', $plan->uuid))
                ->can(PolicyAbilities::UPDATE),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('plans.delete_confirm'))
                ->execute(function (Plan $plan) {
                    $plan->delete();
                    NotificationBuilder::make()
                        ->title('plans.deleted_successfully', ['name' => $plan->label()])
                        ->success()
                        ->send();
                })
                ->can(PolicyAbilities::DELETE),
        ];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('plans.show')) {
            return Action::make()
                ->route('plans.show', $uuid)
                ->can(PolicyAbilities::VIEW);
        }

        return null;
    }
}
