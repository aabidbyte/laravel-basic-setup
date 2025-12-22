<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Permissions;
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
            ->title(__('ui.navigation.platform'))
            ->items(
                NavigationItem::make()
                    ->title(__('ui.navigation.dashboard'))
                    ->route('dashboard')
                    ->icon('home'),

                NavigationItem::make()
                    ->title(__('ui.navigation.users'))
                    ->route('users.index')
                    ->icon('users')
                    ->show(Auth::user()?->can(Permissions::VIEW_USERS) ?? false)
            )
            ->toArray();
    }

    /**
     * Get bottom navigation menus (Resources section).
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getBottomMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('ui.navigation.resources'))
            ->items(
                NavigationItem::make()
                    ->title(__('ui.navigation.repository'))
                    ->url('https://github.com/laravel/livewire-starter-kit')
                    ->external()
                    ->icon('folder'),

                NavigationItem::make()
                    ->title(__('ui.navigation.documentation'))
                    ->url('https://laravel.com/docs/starter-kits#livewire')
                    ->external()
                    ->icon('book-open')
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
            ->title(__('ui.navigation.user'))
            ->items(
                NavigationItem::make()
                    ->title(__('ui.navigation.settings'))
                    ->route('profile.edit')
            )
            ->toArray();
    }
}
