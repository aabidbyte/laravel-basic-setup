<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\Datatable;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class UserTable extends Datatable
{
    /**
     * Mount the component and authorize access
     */
    public function mount(): void
    {
        parent::mount();
        $this->authorize(Permissions::VIEW_USERS);
    }

    /**
     * Get the base query
     */
    protected function baseQuery(): Builder
    {
        return User::query()->with(['roles', 'teams'])->select('users.*');
    }

    /**
     * Get column definitions
     *
     * @return array<int, Column>
     */
    protected function columns(): array
    {
        return [
            Column::make(__('ui.table.users.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('ui.table.users.email'), 'email')
                ->sortable()
                ->searchable()
                ->class('text-base-content/70'),

            Column::make(__('ui.table.users.roles'), 'roles_for_datatable')
                ->content(fn (User $user) => $user->roles->pluck('name')->toArray())
                ->type(DataTableUi::BADGE, ['variant' => 'primary', 'size' => 'sm']),

            Column::make(__('ui.table.users.teams'), 'teams_for_datatable')
                ->content(fn (User $user) => $user->teams->pluck('name')->toArray())
                ->type(DataTableUi::BADGE, ['variant' => 'secondary', 'size' => 'sm']),
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
            Filter::make('role', __('ui.table.users.filters.role'))
                ->placeholder(__('ui.table.users.filters.all_roles'))
                ->type('select')
                ->relationship('roles', 'name')
                ->optionsCallback(fn () => Role::pluck('name', 'name')->toArray()),

            Filter::make('is_active', __('ui.table.users.filters.status'))
                ->placeholder(__('ui.table.users.filters.all_status'))
                ->type('select')
                ->options([
                    '1' => __('ui.table.users.status_active'),
                    '0' => __('ui.table.users.status_inactive'),
                ])
                ->valueMapping(['1' => true, '0' => false]),

            Filter::make('email_verified_at', __('ui.table.users.filters.verified'))
                ->placeholder(__('ui.table.users.filters.all_status'))
                ->type('select')
                ->options([
                    '1' => __('ui.table.users.verified_yes'),
                    '0' => __('ui.table.users.verified_no'),
                ])
                ->valueMapping(['1' => 'not_null', '0' => 'null']),
        ];
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
            $actions[] = Action::make('view', __('ui.actions.view'))
                ->icon('eye')
                ->route(fn (User $user) => route('users.show', $user))
                ->variant('ghost');
        }

        $actions[] = Action::make('view_modal', __('ui.actions.view_details'))
            ->icon('eye')
            ->bladeModal('components.users.view-modal', fn (User $user) => ['user' => $user])
            ->variant('ghost');

        // Only add edit action if route exists
        if (Route::has('users.edit')) {
            $actions[] = Action::make('edit', __('ui.actions.edit'))
                ->icon('pencil')
                ->route(fn (User $user) => route('users.edit', $user))
                ->variant('ghost')
                ->show(fn (User $user) => Auth::user()?->can('update', $user) ?? false);
        }

        $actions[] = Action::make('delete', __('ui.actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('ui.actions.confirm_delete'))
            ->execute(function (User $user) {
                $user->delete();
                NotificationBuilder::make()->title(__('ui.actions.deleted_successfully',["user" => $user->name]))->success()->send();
            })
            ->show(fn (User $user) => Auth::user()?->can('delete', $user) ?? false);

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
            BulkAction::make('activate', __('ui.actions.activate_selected'))
                ->icon('check')
                ->variant('ghost')
                ->execute(fn ($users) => $users->each->update(['is_active' => true])),
            BulkAction::make('deactivate', __('ui.actions.deactivate_selected'))
                ->icon('x-mark')
                ->variant('ghost')
                ->execute(fn ($users) => $users->each->update(['is_active' => false])),
            BulkAction::make('delete', __('ui.actions.delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('ui.actions.confirm_bulk_delete'))
                ->execute(fn ($users) => $users->each->delete()),
        ];
    }

    /**
     * Handle row click
     */
    public function rowClicked(string $uuid): void
    {
        // Only redirect if route exists
        if (! Route::has('users.show')) {
            return;
        }

        $user = User::where('uuid', $uuid)->first();
        if ($user !== null) {
            $this->redirect(route('users.show', $user));
        }
    }
}
