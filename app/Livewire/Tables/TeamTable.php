<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\DataTable\Datatable;
use App\Models\Team;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class TeamTable extends Datatable
{
    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_TEAMS);
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        return Team::query()
            ->select('teams.*')
            ->withCount(['users']);
    }

    /**
     * Get column definitions.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.teams.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.teams.description'), 'description')
                ->searchable()
                ->class('text-base-content/70 max-w-xs truncate'),

            Column::make(__('table.teams.members_count'), 'users_count')
                ->sortable()
                ->format(fn ($value) => DataTableUi::renderComponent(DataTableUi::UI_BADGE, (string) $value, ['variant' => 'ghost']))
                ->html()
                ->class('text-center'),
        ];
    }

    /**
     * Get row action definitions.
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        if (Route::has('teams.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (Team $team) => route('teams.show', $team->uuid))
                ->variant('ghost')
                ->can(Permissions::VIEW_TEAMS, false);
        }

        if (Route::has('teams.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (Team $team) => route('teams.edit', $team->uuid))
                ->variant('ghost')
                ->can(Permissions::EDIT_TEAMS, false);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (Team $team) {
                $team->delete();
                NotificationBuilder::make()
                    ->title('actions.deleted_successfully', ['name' => $team->label()])
                    ->success()
                    ->send();
            })
            ->can(Permissions::DELETE_TEAMS, false);

        return $actions;
    }

    /**
     * Get bulk action definitions.
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('delete', __('actions.delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(fn ($teams) => $teams->each->delete())
                ->can(Permissions::DELETE_TEAMS),
        ];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('teams.show')) {
            return Action::make()
                ->route('teams.show', $uuid)
                ->can(Permissions::VIEW_TEAMS, false);
        }

        return null;
    }
}
