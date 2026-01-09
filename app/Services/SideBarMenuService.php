<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Auth\Permissions;
use App\Services\Navigation\NavigationBuilder;
use App\Services\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;

class SideBarMenuService
{
    /**
     * Get top navigation menus (Platform section).
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getTopMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('navigation.platform'))
            ->items(
                NavigationItem::make()
                    ->title(__('navigation.dashboard'))
                    ->route('dashboard')
                    ->icon('home'),

                NavigationItem::make()
                    ->title(__('navigation.starter_kit'))
                    ->url('#')
                    ->icon('sparkles'),
            )
            ->toArray();
    }

    /**
     * Get bottom navigation menus (Administration section).
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getBottomMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('navigation.administration'))
            ->items(
                // Administration group (collapsible)
                NavigationItem::make()
                    ->title(__('navigation.management'))
                    ->icon('cog')
                    ->items(
                        NavigationItem::make()
                            ->title(__('navigation.users'))
                            ->route('users.index')
                            ->activeRoutes('users.*')
                            ->show(Auth::user()?->can(Permissions::VIEW_USERS) ?? false),

                        NavigationItem::make()
                            ->title(__('navigation.roles'))
                            ->route('roles.index')
                            ->activeRoutes('roles.*')
                            ->show(Auth::user()?->can(Permissions::VIEW_ROLES) ?? false),

                        NavigationItem::make()
                            ->title(__('navigation.teams'))
                            ->route('teams.index')
                            ->activeRoutes('teams.*')
                            ->show(Auth::user()?->can(Permissions::VIEW_TEAMS) ?? false),
                    ),

                // Developer Tools group (collapsible, dev only)
                NavigationItem::make()
                    ->title(__('navigation.developer_tools'))
                    ->icon('code-bracket')
                    ->show(app()->environment('local', 'development'))
                    ->items(
                        NavigationItem::make()
                            ->title(__('navigation.telescope'))
                            ->url(config('app.url') . '/admin/system/debug/monitoring')
                            ->icon('magnifying-glass')
                            ->external(),

                        NavigationItem::make()
                            ->title(__('navigation.horizon'))
                            ->url(config('app.url') . '/admin/system/queue-monitor')
                            ->icon('queue-list')
                            ->external(),

                        NavigationItem::make()
                            ->title(__('navigation.log_viewer'))
                            ->url(config('app.url') . '/admin/system/log-viewer')
                            ->icon('document-text')
                            ->external(),

                        NavigationItem::make()
                            ->title(__('navigation.error_handler'))
                            ->route('admin.errors.index')
                            ->activeRoutes('admin.errors.*')
                            ->icon('exclamation-triangle')
                            ->show(Auth::user()?->can(Permissions::VIEW_ERROR_LOGS) ?? false),
                    ),

                // Trashed items group (collapsible)
                NavigationItem::make()
                    ->title(__('navigation.trashed'))
                    ->icon('trash')
                    ->show($this->hasAnyTrashPermission())
                    ->items(
                        NavigationItem::make()
                            ->title(__('types.users'))
                            ->route('trash.index', ['entityType' => 'users'])
                            ->active(fn () => request()->route('entityType') === 'users' && request()->routeIs('trash.*'))
                            ->show(Auth::user()?->can(Permissions::VIEW_USERS) ?? false),

                        NavigationItem::make()
                            ->title(__('types.roles'))
                            ->route('trash.index', ['entityType' => 'roles'])
                            ->active(fn () => request()->route('entityType') === 'roles' && request()->routeIs('trash.*'))
                            ->show(Auth::user()?->can(Permissions::VIEW_ROLES) ?? false),

                        NavigationItem::make()
                            ->title(__('types.teams'))
                            ->route('trash.index', ['entityType' => 'teams'])
                            ->active(fn () => request()->route('entityType') === 'teams' && request()->routeIs('trash.*'))
                            ->show(Auth::user()?->can(Permissions::VIEW_TEAMS) ?? false),

                        NavigationItem::make()
                            ->title(__('types.error_logs'))
                            ->route('trash.index', ['entityType' => 'error-logs'])
                            ->active(fn () => request()->route('entityType') === 'error-logs' && request()->routeIs('trash.*'))
                            ->show(Auth::user()?->can(Permissions::VIEW_ERROR_LOGS) ?? false),
                    ),
            )
            ->toArray();
    }

    /**
     * Get user dropdown menu items.
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getUserMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('navigation.user'))
            ->items(
                NavigationItem::make()
                    ->title(__('navigation.settings'))
                    ->route('settings.account'),
            )
            ->toArray();
    }

    /**
     * Check if the current user has any trash-related permissions.
     */
    protected function hasAnyTrashPermission(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->can(Permissions::VIEW_USERS)
            || $user->can(Permissions::VIEW_ROLES)
            || $user->can(Permissions::VIEW_TEAMS)
            || $user->can(Permissions::VIEW_ERROR_LOGS);
    }
}
