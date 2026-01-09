<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Livewire\DataTable\Datatable;
use App\Models\Team;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Locked;

/**
 * Inline DataTable for displaying users in a specific team.
 * Used on the Team show page.
 */
class TeamUserTable extends Datatable
{
    /**
     * The team UUID to filter users by.
     */
    #[Locked]
    public string $teamUuid = '';

    /**
     * The team instance (resolved from UUID).
     */
    protected ?Team $team = null;

    /**
     * Mount the component with the team UUID.
     */
    public function mount(string $teamUuid = ''): void
    {
        $this->teamUuid = $teamUuid;
    }

    /**
     * Get the team instance.
     */
    protected function getTeam(): ?Team
    {
        if ($this->team === null && $this->teamUuid) {
            $this->team = Team::where('uuid', $this->teamUuid)->first();
        }

        return $this->team;
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        $team = $this->getTeam();

        if (! $team) {
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->whereHas('teams', fn (Builder $q) => $q->where('teams.id', $team->id))
            ->select('users.*');
    }

    /**
     * Get column definitions.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.users.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.users.email'), 'email')
                ->sortable()
                ->searchable()
                ->class('text-base-content/70'),
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

        if (Route::has('users.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (User $user) => route('users.show', $user->uuid))
                ->variant('ghost')
                ->can(Permissions::VIEW_USERS, false);
        }

        $actions[] = Action::make('detach', __('actions.detach'))
            ->icon('x-mark')
            ->variant('ghost')
            ->color('warning')
            ->confirm(__('actions.confirm_detach_user'))
            ->execute(function (User $user) {
                $team = $this->getTeam();
                if ($team) {
                    $user->teams()->detach($team->id);
                    NotificationBuilder::make()
                        ->title('actions.detached_successfully', ['user' => $user->name, 'team' => $team->label()])
                        ->success()
                        ->send();
                }
            })
            ->can(Permissions::EDIT_TEAMS, false);

        return $actions;
    }

    /**
     * Handle row click - navigate to user show page.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('users.show')) {
            return Action::make()
                ->route('users.show', $uuid)
                ->can(Permissions::VIEW_USERS, false);
        }

        return null;
    }
}
