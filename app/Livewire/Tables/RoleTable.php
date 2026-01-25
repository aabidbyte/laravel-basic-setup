<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\DataTable\Datatable;
use App\Models\Role;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class RoleTable extends Datatable
{
    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_ROLES());
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        $query = Role::query()
            ->select('roles.*')
            ->withCount(['permissions', 'users']);

        // Hide super_admin role from non-super_admin users
        if (! auth()->user()?->hasRole(Roles::SUPER_ADMIN)) {
            $query->where('name', '!=', Roles::SUPER_ADMIN);
        }

        return $query;
    }

    /**
     * Get column definitions.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.roles.name'), 'display_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.roles.users_count'), 'users_count')
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

        if (Route::has('roles.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (Role $role) => route('roles.show', $role->uuid))
                ->variant('ghost')
                ->can(Permissions::VIEW_ROLES(), false);
        }

        if (Route::has('roles.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (Role $role) => route('roles.edit', $role->uuid))
                ->variant('ghost')
                ->can(Permissions::EDIT_ROLES(), false);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (Role $role) {
                if (in_array($role->name, [Roles::SUPER_ADMIN, Roles::ADMIN], true)) {
                    NotificationBuilder::make()
                        ->title(__('actions.error'))
                        ->content('Cannot delete protected role.')
                        ->error()
                        ->send();

                    return;
                }

                $role->delete();
                NotificationBuilder::make()
                    ->title('actions.deleted_successfully', ['name' => $role->label()])
                    ->success()
                    ->send();
            })
            ->can(Permissions::DELETE_ROLES(), false)
            ->show(fn (Role $role) => ! in_array($role->name, [Roles::SUPER_ADMIN, Roles::ADMIN], true));

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
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(function ($roles) {
                    $roles->reject(fn ($role) => in_array($role->name, [Roles::SUPER_ADMIN, Roles::ADMIN], true))
                        ->each->delete();
                })
                ->can(Permissions::DELETE_ROLES()),
        ];
    }

    /**
     * Handle row click.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('roles.show')) {
            return Action::make()
                ->route('roles.show', $uuid)
                ->can(Permissions::VIEW_ROLES(), false);
        }

        return null;
    }
}
