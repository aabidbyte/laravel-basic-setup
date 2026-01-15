<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\DataTable\Datatable;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class UserTable extends Datatable
{
    /**
     * Mount the component and authorize access
     */
    public function mount(): void
    {
        // Authorize access to the list
        $this->authorize(PolicyAbilities::VIEW_ANY, User::class);
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        return User::query()->with(['roles', 'teams'])->select('users.*');
    }

    /**
     * Get column definitions
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

            Column::make(__('table.users.status'), 'is_active')
                ->sortable()
                ->format(fn ($value) => $value
                    ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('users.active'), ['color' => 'success', 'size' => 'sm'])
                    : DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('users.inactive'), ['color' => 'error', 'size' => 'sm']))
                ->html(),

            Column::make(__('table.users.roles'), 'roles_for_datatable')
                ->content(function (User $user) {
                    $roles = $user->roles->pluck('display_name')->toArray();

                    return count($roles) > 3
                        ? [trans_choice('users.roles_count', count($roles))]
                        : $roles;
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'primary', 'size' => 'sm']),

            Column::make(__('table.users.teams'), 'teams_for_datatable')
                ->content(function (User $user) {
                    $teams = $user->teams->pluck('name')->toArray();

                    return count($teams) > 3
                        ? [trans_choice('users.teams_count', count($teams))]
                        : $teams;
                })
                ->type(DataTableUi::UI_BADGE, ['color' => 'secondary', 'size' => 'sm']),

            Column::make(__('table.users.last_login_at'), 'last_login_at')
                ->sortable()
                ->format(fn ($value) => $value ? $value->diffForHumans() : '—'),
        ];
    }

    /**
     * Get filter definitions
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('role', __('table.users.filters.role'))
                ->placeholder(__('table.users.filters.all_roles'))
                ->type('select')
                ->relationship('roles', 'name')
                ->options($this->getRoleOptions()), // Use memoized options

            Filter::make('is_active', __('table.users.filters.status'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type('select')
                ->options([
                    '1' => __('table.users.status_active'),
                    '0' => __('table.users.status_inactive'),
                ])
                ->valueMapping(['1' => true, '0' => false]),

            Filter::make('email_verified_at', __('table.users.filters.verified'))
                ->placeholder(__('table.users.filters.all_status'))
                ->type('select')
                ->options([
                    '1' => __('table.users.verified_yes'),
                    '0' => __('table.users.verified_no'),
                ])
                ->valueMapping(['1' => 'not_null', '0' => 'null']),
        ];
    }

    /**
     * Get role options (memoized).
     *
     * @return array<string, string>
     */
    protected function getRoleOptions(): array
    {
        return $this->memoize('filter:roles', fn () => Role::pluck('name', 'name')->toArray(),
        );
    }

    /**
     * Get row action definitions
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        // Only add view action if route exists
        if (Route::has('users.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (User $user) => route('users.show', $user->uuid))
                ->variant('ghost')
                ->can(PolicyAbilities::VIEW);
        }

        if (Route::has('users.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (User $user) => route('users.edit', $user->uuid))
                ->variant('ghost')
                ->can(PolicyAbilities::UPDATE);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (User $user) {
                $user->delete();
                NotificationBuilder::make()
                    ->title('actions.deleted_successfully', ['user' => $user->label()])
                    ->success()
                    ->send();
            })
            ->can(PolicyAbilities::DELETE);

        return $actions;
    }

    /**
     * Get bulk action definitions
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('activate', __('actions.activate_selected'))
                ->icon('check')
                ->variant('ghost')
                ->execute(fn ($users) => $users->each->update(['is_active' => true]))
                ->can(PolicyAbilities::UPDATE),

            BulkAction::make('deactivate', __('actions.deactivate_selected'))
                ->icon('x-mark')
                ->variant('ghost')
                ->execute(fn ($users) => $users->each->update(['is_active' => false]))
                ->can(PolicyAbilities::UPDATE),

            BulkAction::make('delete', __('actions.delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(fn ($users) => $users->each->delete())
                ->can(PolicyAbilities::DELETE),
        ];
    }

    /**
     * Handle row click
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('users.show')) {
            return Action::make()
                ->route('users.show', $uuid)
                ->can(PolicyAbilities::VIEW);
        }

        return null;
    }
}
